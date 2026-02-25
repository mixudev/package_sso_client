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

#### Quick Publish All Assets (Recommended)

```bash
php artisan vendor:publish --tag=mixu-sso-auth
```

Ini akan publish semua assets sekaligus:
-  Configuration file → `config/mixuauth.php`
-  Database migrations → `database/migrations/`
-  Routes → `routes/sso-auth.php`
-  Views → `resources/views/vendor/mixu-sso-auth/`

#### Publish Individual Assets (Optional)

Jika hanya ingin publish asset tertentu:

```bash
# Config only
php artisan vendor:publish --tag=mixu-sso-auth-config

# Migrations only
php artisan vendor:publish --tag=mixu-sso-auth-migrations

# Routes only
php artisan vendor:publish --tag=mixu-sso-auth-routes

# Views only
php artisan vendor:publish --tag=mixu-sso-auth-views
```

### 3. Configure Environment Variables

Di file `.env`, tambahkan:

```env
# SSO Configuration
AUTH_BASE_URL=https://auth.yourcompany.com

AUTH_CLIENT_ID=your-client-id-from-sso
AUTH_CLIENT_SECRET=your-client-secret-from-sso

AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
AUTH_SCOPES=openid_profile_email

SSO_WEBHOOK_SECRET=your-webhook-secret-for-global-logout
```
Didapatkan dari SSO_Server
**Important:** Gunakan HTTPS untuk AUTH_BASE_URL di production!

### 4. Run Database Migrations

```bash
php artisan migrate
```

Ini akan membuat tabel:
- `session_activities` - Untuk audit trail
- `security_events` - Untuk security monitoring

### 5. Verify Configuration

Pastikan semua environment variables ter-set dan config loaded dengan benar:

```bash
php artisan sso:check
```

Output seharusnya:
```
Checking SSO Configuration...

Environment Variables:
  ✅ AUTH_BASE_URL = https://auth.yourcompany.com
  ✅ AUTH_CLIENT_ID = xxxxx
  ✅ AUTH_CLIENT_SECRET = [hidden]
  ✅ AUTH_REDIRECT_URI = http://localhost:8000/auth/callback

SSO Configuration (services.mixuauth):
  ✅ base_url = https://auth.yourcompany.com
  ✅ client_id = xxxxx
  ✅ client_secret = [hidden]
  ✅ redirect_uri = http://localhost:8000/auth/callback
  ✅ scopes = openid profile email

SSO is fully configured and ready to use!
```

### 6. Register Middleware

Di Laravel 11+, middleware biasanya **otomatis ter-register** melalui package discovery. Jika tidak, daftarkan manual di `bootstrap/app.php`:

```php
use Mixu\SSOAuth\Http\Middleware\EnsureSSOAuthenticated;
use Mixu\SSOAuth\Http\Middleware\EnsureSSOSessionAlive;
use Mixu\SSOAuth\Http\Middleware\ValidateSessionIP;
use Mixu\SSOAuth\Http\Middleware\ValidateSessionUserAgent;
use Mixu\SSOAuth\Http\Middleware\TrackSessionActivity;
use Mixu\SSOAuth\Http\Middleware\CheckRole;
use Mixu\SSOAuth\Http\Middleware\CheckAccessArea;
use Mixu\SSOAuth\Http\Middleware\LogAuditTrail;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'sso.auth' => EnsureSSOAuthenticated::class,
            'sso.alive' => EnsureSSOSessionAlive::class,
            'validate.session.ip' => ValidateSessionIP::class,
            'validate.session.ua' => ValidateSessionUserAgent::class,
            'track.activity' => TrackSessionActivity::class,
            'audit.trail' => LogAuditTrail::class,
            'role' => CheckRole::class,
            'access_area' => CheckAccessArea::class,
        ]);
    })
```

### 7. Setup Routes 

Routes sudah **otomatis ter-load** dari package dan ada beberapa yang perlu ditambahkan:

Di web.php tambahkan ini di paling bawah :
```bash
require __DIR__.'/sso-auth.php';
```

Tambahkan juga route untuk redirect dashboard dan home (custom sesuai kebutuhan sistem):
```bash
// contoh route dashboard 
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('sso.auth')->name('dashboard');

// contoh route home
Route::get('/', function () {
    return view('welcome');
})->name('home');
```

Kemudian untuk custom bisa edit file `routes/sso-auth.php` sesuai kebutuhan.

### 8. Create Login/Logout Links

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

### Error: HTTP 500 saat akses routes SSO

**Penyebab:** Configuration belum lengkap atau environment variables tidak ter-set

**Solution:**
1. Jalankan diagnostic command:
```bash
php artisan sso:check
```

2. Jika ada MISSING/EMPTY, set environment variables di `.env`:
```env
AUTH_BASE_URL=https://auth.yourcompany.com
AUTH_CLIENT_ID=your-client-id
AUTH_CLIENT_SECRET=your-client-secret
AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
AUTH_SCOPES=openid profile email
```

3. Clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

4. Check Laravel logs untuk detail error:
```bash
tail -f storage/logs/laravel.log
```

5. Pastikan sudah publish assets:
```bash
php artisan vendor:publish --tag=mixu-sso-auth
php artisan migrate
```

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

### Error: "Assets not published"

**Penyebab:** Lupa publish assets setelah install

**Solution:**
```bash
# Publish semua assets sekaligus
php artisan vendor:publish --tag=mixu-sso-auth

# Atau publish specific asset saja
php artisan vendor:publish --tag=mixu-sso-auth-config
php artisan vendor:publish --tag=mixu-sso-auth-migrations
php artisan vendor:publish --tag=mixu-sso-auth-routes
php artisan vendor:publish --tag=mixu-sso-auth-views
```

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
