# Installation & Integration Guide

Panduan lengkap untuk mengintegrasikan Mixu SSO Auth Package ke aplikasi Laravel Anda.

## Prerequisites

- Laravel 12.0+
- PHP 8.2+
- Composer
- Database (MySQL, PostgreSQL, SQLite, dll)
- Credentials dari Mixu Auth Server (AUTH_CLIENT_ID, AUTH_CLIENT_SECRET)

## Step-by-Step Installation

### 1. Install Package via Composer

```bash
composer require mixu/sso-auth
```

### 2. Publish Package Assets

#### Publish Configuration File

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-config
```

File config akan berada di `config/mixuauth.php`

#### Publish Migrations

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-migrations
```

Migrations akan berada di `database/migrations/`

#### Publish Routes (Optional)

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-routes
```

### 3. Configure Environment Variables

Di file `.env`, tambahkan:

```env
# SSO Configuration
AUTH_BASE_URL=https://auth.yourcompany.com
AUTH_CLIENT_ID=your-client-id-from-sso
AUTH_CLIENT_SECRET=your-client-secret-from-sso
AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
AUTH_SCOPES=openid profile email
SSO_WEBHOOK_SECRET=your-webhook-secret-for-global-logout
```

**Important:** Gunakan HTTPS untuk AUTH_BASE_URL di production!

### 4. Run Database Migrations

```bash
php artisan migrate
```

Ini akan membuat tabel:
- `session_activities` - Untuk audit trail
- `security_events` - Untuk security monitoring

### 5. Register Middleware (jika belum auto-discover)

Di `bootstrap/app.php` atau `app/Http/Kernel.php`, daftarkan middleware:

```php
use Mixu\SSOAuth\Http\Middleware\EnsureSSOAuthenticated;
use Mixu\SSOAuth\Http\Middleware\EnsureSSOSessionAlive;
use Mixu\SSOAuth\Http\Middleware\ValidateSessionIP;
use Mixu\SSOAuth\Http\Middleware\ValidateSessionUserAgent;
use Mixu\SSOAuth\Http\Middleware\TrackSessionActivity;
use Mixu\SSOAuth\Http\Middleware\CheckRole;
use Mixu\SSOAuth\Http\Middleware\CheckAccessArea;

// Bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'sso.auth' => EnsureSSOAuthenticated::class,
        'sso.alive' => EnsureSSOSessionAlive::class,
        'validate.session.ip' => ValidateSessionIP::class,
        'validate.session.ua' => ValidateSessionUserAgent::class,
        'track.activity' => TrackSessionActivity::class,
        'role' => CheckRole::class,
        'access_area' => CheckAccessArea::class,
    ]);
})
```

### 6. Setup Routes

Routes sudah otomatis di-load. Jika perlu custom, publish routes:

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-routes
```

Kemudian edit file `routes/sso-auth.php` sesuai kebutuhan.

### 7. Create Login/Logout Links

Di layout file Anda (e.g., `resources/views/layouts/app.blade.php`):

```blade
@if (session('sso_user'))
    <span>{{ session('sso_user.name') }}</span>
    <form action="{{ route('auth.logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-link">Logout</button>
    </form>
@else
    <a href="{{ route('auth.login') }}" class="btn btn-primary">Login</a>
@endif
```

## Advanced Configuration

### Customize Middleware Order

Edit di route definitions:

```php
Route::middleware([
    'sso.auth',           // Check user logged in
    'sso.alive',          // Check token still valid
    'validate.session.ip', // Check IP didn't change
    'track.activity',     // Log request
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

### Disable IP Binding (if behind proxy/VPN)

Jika aplikasi di belakang proxy, user IP mungkin berubah. Skip middleware:

```php
Route::middleware(['sso.auth', 'sso.alive'])
    //->middleware('validate.session.ip') // Disable
    ->middleware('track.activity')
    ->group(function () {
        // Routes here
    });
```

### Customize Access Control

```php
// Role-based
Route::middleware(['role:admin,moderator'])->group(function () {
    Route::get('/admin', [...]);
});

// Area-based  
Route::middleware(['access_area:portal,supervisor'])->group(function () {
    Route::get('/portal', [...]);
});

// Combined
Route::middleware(['sso.auth', 'role:admin', 'access_area:portal'])->group(function () {
    Route::get('/admin-portal', [...]);
});
```

## Integration with Existing Authentication

Jika sudah punya sistem auth lain, integration bisa dilakukan:

```php
// app/Services/UserService.php
use Mixu\SSOAuth\Services\SSOAuthService;

class UserService
{
    public function __construct(private SSOAuthService $sso) {}

    public function syncUserFromSSO($accessToken)
    {
        $ssoUser = $this->sso->getUser($accessToken);
        
        // Sync dengan local database
        $user = User::updateOrCreate(
            ['sso_id' => $ssoUser['id']],
            [
                'name' => $ssoUser['name'],
                'email' => $ssoUser['email'],
                'roles' => json_encode($ssoUser['roles']),
            ]
        );

        return $user;
    }
}
```

## Testing

```php
// tests/Feature/SSOAuthTest.php
use Mixu\SSOAuth\Services\SSOAuthService;

class SSOAuthTest extends TestCase
{
    public function test_sso_configured(): void
    {
        $sso = app(SSOAuthService::class);
        $this->assertTrue($sso->isConfigured());
    }

    public function test_generate_state(): void
    {
        $sso = app(SSOAuthService::class);
        $state = $sso->generateState();
        $this->assertNotEmpty($state);
        $this->assertEquals(40, strlen($state));
    }
}
```

## Troubleshooting

### Error: "SSO not configured"

**Penyebab:** Environment variables belum diatur

**Solution:**
```bash
# Set di .env
AUTH_BASE_URL=https://auth.example.com
AUTH_CLIENT_ID=your-id
AUTH_CLIENT_SECRET=your-secret

# Atau set di production
php artisan config:cache
```

### Error: "Token exchange failed"

**Penyebab:** Client ID/Secret salah atau SSO Server unreachable

**Solution:**
1. Verify credentials di SSO Server
2. Check network connectivity: `curl https://auth.example.com`
3. Check logs: `tail -f storage/logs/laravel.log`

### Error: "Session IP mismatch"

**Penyebab:** User IP berubah (proxy, VPN, mobile data)

**Solution:**
1. Nonaktifkan IP validation jika di behind proxy
2. Atau allow IP range change dengan custom middleware

## Production Checklist

- ✅ Set `AUTH_BASE_URL` ke HTTPS URL
- ✅ Generate secure `SSO_WEBHOOK_SECRET`
- ✅ Update `AUTH_REDIRECT_URI` ke production URL
- ✅ Run migrations: `php artisan migrate --force`
- ✅ Set secure session cookie: `SESSION_SECURE_COOKIES=true`
- ✅ Enable HTTPS only: `FORCE_HTTPS=true`
- ✅ Setup log monitoring untuk security_events
- ✅ Configure backup untuk databases
- ✅ Test logout flow dengan global logout webhook

## Next Steps

1. Customize views di `resources/views/vendor/mixu-sso-auth/`
2. Monitor security events di dashboard
3. Setup alerts untuk critical events
4. Integrate dengan notification system (Email, Slack)

Untuk lebih lanjut, lihat README.md dan dokumentasi API.
