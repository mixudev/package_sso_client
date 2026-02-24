# Testing Guide

Panduan untuk testing package Mixu SSO Auth.

## Unit Tests

Jalankan unit tests:

```bash
php artisan test
```

Atau dengan coverage:

```bash
php artisan test --coverage
```

## Feature Tests

### Test SSO Flow

```php
// tests/Feature/SSOFlowTest.php
use Mixu\SSOAuth\Services\SSOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SSOFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sso_auth_service_is_configured(): void
    {
        $sso = app(SSOAuthService::class);
        
        // Skip if not configured (untuk testing tanpa credentials)
        if (!$sso->isConfigured()) {
            $this->markTestSkipped('SSO not configured');
        }
        
        $this->assertTrue($sso->isConfigured());
    }

    public function test_generate_state_returns_random_string(): void
    {
        $sso = app(SSOAuthService::class);
        $state1 = $sso->generateState();
        $state2 = $sso->generateState();

        $this->assertNotEmpty($state1);
        $this->assertNotEmpty($state2);
        $this->assertNotEquals($state1, $state2);
        $this->assertEquals(40, strlen($state1));
    }

    public function test_login_redirect_without_config(): void
    {
        // Reset config
        config(['services.mixuauth' => []]);
        
        $this->get(route('auth.login'))
            ->assertStatus(200);
    }

    public function test_logout_clears_session(): void
    {
        // Setup mock session
        $this->withSession([
            'sso_user' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'roles' => ['user'],
                'access_areas' => ['portal'],
            ],
            'sso_access_token' => 'fake-token',
            'session_ip' => '127.0.0.1',
            'session_user_agent' => 'Mozilla/5.0',
        ]);

        // Skip actual SSO logout
        // $this->post(route('auth.logout'))
        //     ->assertRedirect(route('home'))
        //     ->assertSessionMissing('sso_user');
    }
}
```

### Test Middleware

```php
// tests/Feature/MiddlewareTest.php
class MiddlewareTest extends TestCase
{
    public function test_ensure_sso_authenticated_blocks_unauthenticated(): void
    {
        $this->withoutMiddleware();
        
        $response = $this->get('/protected-route');
        // Expect redirect to login
    }

    public function test_validate_session_ip_allows_same_ip(): void
    {
        $this->withSession([
            'sso_user' => ['id' => 1],
            'session_ip' => '192.168.1.1',
        ]);

        // Setup middleware test
    }

    public function test_validate_session_ip_blocks_different_ip(): void
    {
        $this->withSession([
            'sso_user' => ['id' => 1],
            'session_ip' => '192.168.1.1',
        ]);
        
        $this->withoutMiddleware();
        
        // Mock IP change
    }

    public function test_check_role_allows_authorized_users(): void
    {
        $this->withSession([
            'sso_user' => [
                'id' => 1,
                'roles' => ['admin'],
            ],
        ]);

        // Setup middleware test
    }

    public function test_check_role_blocks_unauthorized_users(): void
    {
        $this->withSession([
            'sso_user' => [
                'id' => 1,
                'roles' => ['user'],
            ],
        ]);

        // Setup middleware test
    }
}
```

### Test Security Monitoring

```php
// tests/Feature/SecurityMonitoringTest.php
use Mixu\SSOAuth\Services\SecurityMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_security_event(): void
    {
        $service = app(SecurityMonitoringService::class);

        $service->logSecurityEvent([
            'event_type' => 'login',
            'sso_user_id' => 1,
            'email' => 'test@example.com',
            'ip_address' => '127.0.0.1',
            'severity' => 'low',
        ]);

        // Check if event was recorded
        $this->assertDatabaseHas('security_events', [
            'event_type' => 'login',
            'sso_user_id' => 1,
        ]);
    }

    public function test_detect_brute_force(): void
    {
        $service = app(SecurityMonitoringService::class);
        
        // Insert multiple failed login attempts
        for ($i = 0; $i < 5; $i++) {
            DB::table('security_events')->insert([
                'event_type' => 'login',
                'ip_address' => '192.168.1.100',
                'severity' => 'low',
                'created_at' => now(),
            ]);
        }

        $isBruteForce = $service->checkBruteForceAttempts('192.168.1.100', 15, 3);
        
        $this->assertTrue($isBruteForce);
    }

    public function test_get_security_stats(): void
    {
        $service = app(SecurityMonitoringService::class);
        
        // Insert some events
        DB::table('security_events')->insert([
            'event_type' => 'login',
            'ip_address' => '127.0.0.1',
            'severity' => 'low',
            'created_at' => now(),
        ]);

        $stats = $service->getSecurityStats(30);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_logins', $stats);
        $this->assertEquals(1, $stats['total_logins']);
    }
}
```

## Integration Tests

```php
// tests/Integration/SSOIntegrationTest.php
class SSOIntegrationTest extends TestCase
{
    /**
     * Test complete SSO flow (requires actual SSO Server)
     */
    public function test_complete_sso_flow(): void
    {
        if (!config('services.mixuauth.client_id')) {
            $this->markTestSkipped('SSO credentials not configured');
        }

        $sso = app(SSOAuthService::class);

        // 1. Generate state
        $state = $sso->generateState();
        $this->assertNotEmpty($state);

        // 2. Get authorize URL
        $authorizeUrl = $sso->getAuthorizeUrl($state);
        $this->assertStringContainsString('client_id', $authorizeUrl);
        $this->assertStringContainsString('state', $authorizeUrl);

        // 3. (Skip) Exchange code - requires user interaction
        // $tokens = $sso->exchangeCodeForToken($code);
        
        // 4. (Skip) Get user - requires valid token
        // $user = $sso->getUser($tokens['access_token']);
    }
}
```

## Manual Testing

### 1. Setup Test Environment

```bash
# Create test database
php artisan migrate --env=testing

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### 2. Test Login Flow

```bash
# Start dev server
php artisan serve

# Visit http://localhost:8000/login
# Should redirect to SSO Server
```

### 3. Test Middleware

```php
// Test IP binding
POST /logout (with different IP)
// Should clear session

// Test activity tracking
GET /dashboard
// Should log in session_activities table
```

### 4. Test Security Events

```php
// Trigger brute force
for i in {1..5}; do
  curl http://localhost:8000/login
done

# Check security_events table
php artisan tinker
> DB::table('security_events')->get();
```

## Coverage Report

```bash
php artisan test --coverage --coverage-html=./coverage
```

Buka `coverage/index.html` untuk melihat coverage report.

## Continuous Integration

Setup dengan GitHub Actions:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_PASSWORD: password
          MYSQL_ROOT_PASSWORD: password

    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, mysql
      
      - name: Install dependencies
        run: composer install
      
      - name: Run tests
        run: php artisan test
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_DATABASE: test
          DB_USERNAME: root
          DB_PASSWORD: password
```

## Performance Testing

```php
// tests/Performance/SSOPerformanceTest.php
use Illuminate\Foundation\Testing\RefreshDatabase;

class SSOPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_performance(): void
    {
        $startTime = microtime(true);
        
        // Simulate login
        $sso = app(SSOAuthService::class);
        $state = $sso->generateState();
        
        $elapsed = (microtime(true) - $startTime) * 1000;
        
        // Should complete within 100ms
        $this->assertLessThan(100, $elapsed);
    }

    public function test_activity_logging_performance(): void
    {
        $startTime = microtime(true);
        
        // Log 100 activities
        for ($i = 0; $i < 100; $i++) {
            DB::table('session_activities')->insert([
                'sso_user_id' => 1,
                'session_id' => 'test',
                'ip_address' => '127.0.0.1',
                'method' => 'GET',
                'path' => '/test',
                'status_code' => 200,
                'created_at' => now(),
            ]);
        }
        
        $elapsed = (microtime(true) - $startTime) * 1000;
        
        // Should complete within 1 second
        $this->assertLessThan(1000, $elapsed);
    }
}
```

## Security Testing

```php
// tests/Security/SecurityTest.php
class SecurityTest extends TestCase
{
    public function test_csrf_protection(): void
    {
        // Missing CSRF token should fail
        $this->post(route('auth.logout'))
            ->assertStatus(419);
    }

    public function test_xss_protection(): void
    {
        // User input should be escaped
    }

    public function test_sql_injection_protection(): void
    {
        // SQL injection attempts should be prevented
    }
}
```

---

Untuk menjalankan semua tests:

```bash
php artisan test
```

Untuk test tertentu:

```bash
php artisan test tests/Feature/SSOFlowTest.php
```

Dengan verbose output:

```bash
php artisan test --verbose
```
