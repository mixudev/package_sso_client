# Integration Examples

Contoh-contoh praktis integrasi Mixu SSO Auth Package dalam aplikasi Laravel.

## Example 1: Basic Integration

### Setup Routes

```php
// routes/web.php
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn() => view('welcome'))->name('home');

// SSO Auth Routes (auto-registered by package)
// GET  /login                  → AuthController@redirect
// GET  /auth/callback          → AuthController@callback
// POST /logout                 → AuthController@logout
// POST /auth/sso/logout-webhook → SsoLogoutCallbackController@handle

// Protected
Route::middleware(['sso.auth', 'sso.alive', 'validate.session.ip'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
```

### Create Dashboard Controller

```php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sso_user');
        
        return view('dashboard', [
            'user' => $user,
            'roles' => $user['roles'] ?? [],
            'areas' => $user['access_areas'] ?? [],
        ]);
    }
}
```

### Create Dashboard View

```blade
<!-- resources/views/dashboard.blade.php -->
<h1>Welcome, {{ $user['name'] }}!</h1>

<div>
    <p>Email: {{ $user['email'] }}</p>
    <p>Roles: {{ implode(', ', $roles) }}</p>
    <p>Areas: {{ implode(', ', $areas) }}</p>
</div>

<form action="{{ route('auth.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
```

## Example 2: Role-Based Routes

### Admin Only Area

```php
// routes/web.php
Route::middleware(['role:admin,super_admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'listUsers'])->name('admin.users');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
});
```

### Supervisor Area

```php
Route::middleware(['role:supervisor'])->group(function () {
    Route::get('/supervisor', [SupervisorController::class, 'dashboard'])->name('supervisor.dashboard');
    Route::get('/supervisor/reports', [SupervisorController::class, 'reports'])->name('supervisor.reports');
});
```

### Multiple Roles

```php
// User harus punya minimal satu role: manager OR supervisor
Route::middleware(['role:manager,supervisor'])->group(function () {
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
});
```

## Example 3: Area-Based Access Control

```php
// Portal Area
Route::middleware(['access_area:portal'])->group(function () {
    Route::get('/portal/dashboard', [PortalController::class, 'dashboard']);
    Route::get('/portal/documents', [PortalController::class, 'documents']);
});

// Supplier Area
Route::middleware(['access_area:supplier'])->group(function () {
    Route::get('/supplier/orders', [SupplierController::class, 'orders']);
    Route::get('/supplier/invoices', [SupplierController::class, 'invoices']);
});

// Multi-area (user dengan access ke 2+ area)
Route::middleware(['access_area:portal,supplier'])->group(function () {
    Route::get('/shared', [SharedController::class, 'index']);
});
```

## Example 4: Security Monitoring

### Monitor Security in Controller

```php
// app/Http/Controllers/SecurityController.php
namespace App\Http\Controllers;

use Mixu\SSOAuth\Services\SecurityMonitoringService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function __construct(
        private SecurityMonitoringService $security
    ) {}

    public function dashboard(Request $request)
    {
        // Get stats untuk 30 hari terakhir
        $stats = $this->security->getSecurityStats(30);
        
        // Detect anomalies untuk user tertentu
        $userId = $request->session()->get('sso_user.id');
        $anomalies = $this->security->detectAnomalies($userId);

        return view('security.dashboard', [
            'stats' => $stats,
            'anomalies' => $anomalies,
        ]);
    }

    public function checkBruteForce(Request $request)
    {
        $ip = $request->ip();
        $isBruteForce = $this->security->checkBruteForceAttempts(
            ip: $ip,
            minutes: 15,
            threshold: 3
        );

        if ($isBruteForce) {
            // Block user / show captcha / etc
        }

        return response()->json(['is_brute_force' => $isBruteForce]);
    }
}
```

### Security Dashboard View

```blade
<!-- resources/views/security/dashboard.blade.php -->
<div class="security-stats">
    <h2>Security Overview (Last 30 Days)</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Logins</h3>
            <p class="stat-value">{{ $stats['total_logins'] }}</p>
        </div>
        
        <div class="stat-card">
            <h3>Failed Logins</h3>
            <p class="stat-value warning">{{ $stats['failed_logins'] }}</p>
        </div>
        
        <div class="stat-card">
            <h3>IP Mismatches</h3>
            <p class="stat-value danger">{{ $stats['ip_mismatches'] }}</p>
        </div>
        
        <div class="stat-card">
            <h3>Critical Events</h3>
            <p class="stat-value danger">{{ $stats['critical_events'] }}</p>
        </div>
        
        <div class="stat-card">
            <h3>Unique Users</h3>
            <p class="stat-value">{{ $stats['unique_users'] }}</p>
        </div>
        
        <div class="stat-card">
            <h3>Unique IPs</h3>
            <p class="stat-value">{{ $stats['unique_ips'] }}</p>
        </div>
    </div>
</div>

@if($anomalies)
    <div class="alerts">
        <h3>⚠️ Anomalies Detected</h3>
        @foreach($anomalies as $anomaly)
            <div class="alert alert-{{ $anomaly['severity'] }}">
                <strong>{{ $anomaly['type'] }}:</strong> {{ $anomaly['message'] }}
            </div>
        @endforeach
    </div>
@endif
```

## Example 5: Custom Middleware

### Custom IP Validation

```php
// app/Http/Middleware/FlexibleIPValidation.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FlexibleIPValidation
{
    // Allow IP changes dari same ISP/provider
    public function handle(Request $request, Closure $next)
    {
        $sessionIP = $request->session()->get('session_ip');
        $currentIP = $request->ip();

        if (!$sessionIP) {
            $request->session()->put('session_ip', $currentIP);
            return $next($request);
        }

        // Check if IPs are from same /24 subnet
        if ($this->sameSubnet($sessionIP, $currentIP, 24)) {
            return $next($request);
        }

        // For different subnets, allow but log
        Log::warning('IP changed', [
            'from' => $sessionIP,
            'to' => $currentIP,
        ]);

        return $next($request);
    }

    private function sameSubnet($ip1, $ip2, $mask)
    {
        return (ip2long($ip1) & ip2long($mask)) === (ip2long($ip2) & ip2long($mask));
    }
}
```

## Example 6: Activity Tracking

### Custom Activity Logger

```php
// app/Http/Middleware/LogDetailedActivity.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogDetailedActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = $request->session()->get('sso_user');
        if ($user) {
            DB::table('activity_logs')->insert([
                'user_id' => $user['id'],
                'action' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_headers' => json_encode($request->headers->all()),
                'response_status' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
```

## Example 7: Protected API Routes

```php
// routes/api.php
Route::middleware(['sso.auth', 'sso.alive'])->group(function () {
    // Get current user info
    Route::get('/user', function (Request $request) {
        return response()->json($request->session()->get('sso_user'));
    });

    // Activity history
    Route::get('/activities', function (Request $request) {
        $userId = $request->session()->get('sso_user.id');
        $activities = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($activities);
    });

    // Security events
    Route::get('/security-events', function (Request $request) {
        $userId = $request->session()->get('sso_user.id');
        $events = DB::table('security_events')
            ->where('sso_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($events);
    });
});
```

## Example 8: Admin Dashboard

```php
// controllers/AdminDashboardController.php
class AdminDashboardController extends Controller
{
    public function __construct(
        private SecurityMonitoringService $security
    ) {}

    public function index()
    {
        // Security stats
        $stats = $this->security->getSecurityStats(30);
        
        // Recent security events
        $recentEvents = DB::table('security_events')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Most active users
        $activeUsers = DB::table('session_activities')
            ->selectRaw('sso_user_id, COUNT(*) as activity_count')
            ->where('created_at', '>', now()->subDay())
            ->groupBy('sso_user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'activeUsers' => $activeUsers,
        ]);
    }
}
```

## Example 9: Email Notifications

```php
// app/Notifications/SecurityAlertNotification.php
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    public function __construct(private array $event) {}

    public function via($notifiable)
    {
        return ['mail', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject('Security Alert: ' . $this->event['event_type'])
            ->line('Severity: ' . strtoupper($this->event['severity']))
            ->line('Details: ' . json_encode($this->event['details']))
            ->action('View Details', url('/admin/security-events'));
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->content('Security Alert!')
            ->attachment(function ($attachment) {
                $attachment->field('Event Type', $this->event['event_type'])
                    ->field('Severity', $this->event['severity'])
                    ->field('IP', $this->event['ip_address']);
            });
    }
}

// Usage dalam observer atau middleware
DB::table('security_events')->create([
    'event_type' => 'critical_event',
    'severity' => 'critical',
    // ...
]);

// Send notification
SecurityAlertNotification::dispatch($event)->toAdmins();
```

## Example 10: Testing

```php
// tests/Feature/SSOFlowTest.php
class SSOFlowTest extends TestCase
{
    public function test_user_can_login_via_sso()
    {
        // Simulate SSO callback
        $this->withSession([
            'oauth_state' => 'test-state',
            'oauth_intended_url' => '/dashboard',
        ]);

        // This would normally redirect to SSO, but we can test the callback
        // $response = $this->get('/login');
        // $this->assertTrue($sso->isConfigured());
    }

    public function test_protected_route_requires_login()
    {
        $response = $this->get('/dashboard');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('location'));
    }

    public function test_role_middleware_blocks_unauthorized()
    {
        $this->withSession([
            'sso_user' => ['id' => 1, 'roles' => ['user']],
        ]);

        $response = $this->get('/admin');
        $this->assertEquals(403, $response->getStatusCode());
    }
}
```

---

Untuk lebih banyak contoh dan kasus penggunaan, lihat dokumentasi lengkap di README.md.
