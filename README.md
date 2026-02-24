# Mixu SSO Auth Package

Sistem SSO (Single Sign-On) authentication lengkap untuk Laravel dengan fitur security monitoring, session binding, dan activity tracking.

## 📋 Fitur Utama

- ✅ **OAuth2 Integration** - Mudah integrase dengan Mixu Auth Server atau OAuth2 provider lainnya
- ✅ **Session Security** - IP binding dan User-Agent validation untuk mencegah session hijacking
- ✅ **Activity Tracking** - Audit trail lengkap untuk setiap user activity
- ✅ **Security Monitoring** - Brute force detection, anomaly detection, security event logging
- ✅ **Role-Based Access** - Kontrol akses berbasis role dari SSO Server
- ✅ **Area-Based Access** - Kontrol akses berbasis access area dari SSO Server
- ✅ **Global Logout Webhook** - Logout otomatis di semua aplikasi saat logout di SSO Server
- ✅ **Laravel 12+ Support** - Compatible dengan Laravel 12 dan PHP 8.2+

## 🚀 Instalasi

### 1. Install via Composer

```bash
composer require mixu/sso-auth
```

### 2. Publish Configuration & Migrations

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-config
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-migrations
```

### 3. Setup Environment Variables

Tambahkan di file `.env`:

```env
AUTH_BASE_URL=https://auth.example.com
AUTH_CLIENT_ID=your-client-id
AUTH_CLIENT_SECRET=your-client-secret
AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
AUTH_SCOPES=openid profile email
SSO_WEBHOOK_SECRET=your-webhook-secret
```

### 4. Run Migrations

```bash
php artisan migrate
```

## ⚙️ Konfigurasi

### Service Provider Otomatis

Package akan secara otomatis terdaftar melalui package auto-discovery. Jika tidak, tambahkan di `config/app.php`:

```php
'providers' => [
    // ...
    Mixu\SSOAuth\Providers\MixuSSOAuthServiceProvider::class,
],

'aliases' => [
    // ...
    'SSOAuth' => Mixu\SSOAuth\Facades\SSOAuth::class,
    'SecurityMonitoring' => Mixu\SSOAuth\Facades\SecurityMonitoring::class,
],
```

### Register Middleware

Daftarkan middleware di `bootstrap/app.php` atau `Kernel.php`:

```php
// Middleware untuk authentication & session validation
\Mixu\SSOAuth\Http\Middleware\EnsureSSOAuthenticated::class, // alias: sso.auth
\Mixu\SSOAuth\Http\Middleware\EnsureSSOSessionAlive::class, // alias: sso.alive
\Mixu\SSOAuth\Http\Middleware\ValidateSessionIP::class, // alias: validate.session.ip
\Mixu\SSOAuth\Http\Middleware\ValidateSessionUserAgent::class, // alias: validate.session.ua
\Mixu\SSOAuth\Http\Middleware\TrackSessionActivity::class, // alias: track.activity
\Mixu\SSOAuth\Http\Middleware\CheckRole::class, // alias: role
\Mixu\SSOAuth\Http\Middleware\CheckAccessArea::class, // alias: access_area
```

## 📖 Cara Penggunaan

### 1. Setup Routes

Routes sudah otomatis terdaftar:

- `GET /login` - Redirect ke SSO login
- `GET /auth/callback` - Callback setelah login dari SSO
- `POST /logout` - Logout user
- `POST /auth/sso/logout-callback` - Webhook untuk global logout

### 2. Protect Routes dengan Middleware

```php
Route::middleware(['sso.auth', 'sso.alive', 'validate.session.ip'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

### 3. Role & Area-Based Access Control

```php
// Hanya admin dan super_admin yang bisa akses
Route::middleware(['role:admin,super_admin'])->group(function () {
    Route::get('/admin', ...);
});

// Hanya area portal yang bisa akses
Route::middleware(['access_area:portal'])->group(function () {
    Route::get('/portal', ...);
});
```

### 4. Menggunakan Service di Controller

```php
use Mixu\SSOAuth\Services\SSOAuthService;
use Mixu\SSOAuth\Services\SecurityMonitoringService;

class DashboardController extends Controller
{
    public function __construct(
        private SSOAuthService $sso,
        private SecurityMonitoringService $security
    ) {}

    public function index()
    {
        // Get user dan token dari session
        $user = auth()->user(); // atau request()->session()->get('sso_user')
        
        // Check token masih valid
        if (!$this->sso->isTokenValid($user['access_token'])) {
            return redirect()->route('auth.login');
        }

        // Get security stats
        $stats = $this->security->getSecurityStats(30);

        return view('dashboard', compact('stats'));
    }
}
```

### 5. Mengakses Data User di Session

```php
// Di controller
$user = request()->session()->get('sso_user');
echo $user['id'];       // User ID dari SSO
echo $user['name'];     // User name
echo $user['email'];    // User email
print_r($user['roles']);        // Array of roles
print_r($user['access_areas']); // Array of access areas

// Di Blade template
{{ Auth::guard('web')->user()?->name }}
// atau
{{ session('sso_user.name') }}
```

## 🔐 Security Features

### IP Binding & Session Hijacking Detection

Session di-bind ke IP address saat login. Jika IP berubah, session otomatis dihapus:

```php
// Middleware: validate.session.ip
Route::middleware(['sso.auth', 'validate.session.ip'])->group(function () {
    // Routes di sini dilindungi dari session hijacking
});
```

### User-Agent Monitoring

Perubahan User-Agent dicatat tapi tidak memblokir request (komplementer ke IP binding):

```php
// Middleware: validate.session.ua
Route::middleware(['validate.session.ua'])->group(function () {
    // User-Agent changes are logged
});
```

### Activity Tracking

Setiap request dari authenticated user dicatat di tabel `session_activities`:

```php
// Middleware: track.activity
Route::middleware(['track.activity'])->group(function () {
    // Semua activity dicatat
});
```

### Security Event Logging

Login, logout, dan anomalous events dicatat di tabel `security_events`:

```php
$this->security->logSecurityEvent([
    'event_type' => 'suspicious_activity',
    'sso_user_id' => $user['id'],
    'email' => $user['email'],
    'ip_address' => request()->ip(),
    'severity' => 'high',
    'details' => ['reason' => 'Multiple failed attempts'],
]);
```

### Anomaly Detection

Deteksi pola mencurigakan:

```php
$anomalies = $this->security->detectAnomalies($userId);
// [
//     ['type' => 'multiple_ips', 'message' => '...', 'severity' => 'high'],
//     ...
// ]
```

### Brute Force Detection

```php
if ($this->security->checkBruteForceAttempts($ip, minutes: 15, threshold: 3)) {
    // Block login attempt
}
```

## 🔄 Global Logout Webhook

Ketika user logout di SSO Server, webhook akan secara otomatis logout user di semua aplikasi:

```php
// Webhook endpoint (sudah auto-register):
POST /auth/sso/logout-callback

// Header diperlukan:
X-SSO-Signature: <hmac-sha256>

// Payload:
{
    "event": "global_logout",
    "user_id": 123,
    "email": "user@example.com"
}
```

## 📊 Database Tables

### session_activities
Audit trail untuk setiap user request:
- `id`, `sso_user_id`, `session_id`, `ip_address`
- `method`, `path`, `status_code`, `user_agent`
- `created_at`

### security_events
Security events untuk monitoring:
- `id`, `event_type`, `sso_user_id`, `email`
- `ip_address`, `session_id`, `severity`
- `details` (JSON), `user_agent`, `created_at`

## 🛠️ API Reference

### SSOAuthService

```php
// Generate authorize URL
$url = $sso->getAuthorizeUrl($state);

// Generate CSRF state
$state = $sso->generateState();

// Exchange code untuk token
$tokens = $sso->exchangeCodeForToken($code);

// Get user info dari SSO
$user = $sso->getUser($accessToken);

// Refresh token
$tokens = $sso->refreshToken($refreshToken);

// Logout dari SSO
$result = $sso->logout($accessToken);

// Check token validity
$valid = $sso->isTokenValid($accessToken);

// Check if configured
$configured = $sso->isConfigured();

// Get last error
$error = $sso->getLastError();
```

### SecurityMonitoringService

```php
// Check brute force attempts
$isBruteForce = $security->checkBruteForceAttempts($ip, $minutes, $threshold);

// Get IP mismatch patterns
$ips = $security->checkIPMismatchPatterns($userId, $minutes);

// Log security event
$security->logSecurityEvent($eventData);

// Detect anomalies
$anomalies = $security->detectAnomalies($userId);

// Get security statistics
$stats = $security->getSecurityStats($days);
```

## 🧪 Testing

```php
// Unit test example
public function test_sso_login()
{
    $sso = app(SSOAuthService::class);
    
    $this->assertTrue($sso->isConfigured());
    
    $state = $sso->generateState();
    $this->assertNotEmpty($state);
}
```

##🐛 Troubleshooting

### SSO Not Configured

**Error:** "SSO not configured. Set AUTH_BASE_URL..."

**Solution:** Pastikan semua envvar di `.env` sudah diatur:
```env
AUTH_BASE_URL=https://auth.example.com
AUTH_CLIENT_ID=your-id
AUTH_CLIENT_SECRET=your-secret
AUTH_REDIRECT_URI=http://yourapp.test/auth/callback
```

### Token Exchange Failed

**Error:** "Tukar authorization code ke access token gagal"

**Solution:** Periksa:
1. `AUTH_BASE_URL` benar
2. `AUTH_CLIENT_ID` dan `AUTH_CLIENT_SECRET` benar
3. `AUTH_REDIRECT_URI` sama dengan di SSO Server
4. Network connectivity ke SSO Server

### Session IP Mismatch

**Error:** "Your session was accessed from a different location"

**Solution:** User mengakses dari IP berbeda. Normal jika:
- Mobile user pindah dari wifi ke cellular
- User di behind proxy/VPN yang berubah

Jika perlu melonggarkan, disable middleware `validate.session.ip`.

## 📝 License

MIT License. See LICENSE file for details.

## 🤝 Contributing

Contributions welcome! Silakan buat issue atau pull request.

## 📧 Support

Email: support@mixu.io
Website: https://mixu.io
