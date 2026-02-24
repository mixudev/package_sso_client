# Package Structure

Dokumentasi lengkap struktur direktori dan file dalam Mixu SSO Auth Package.

## Directory Tree

```
packages/mixu-sso-auth/
├── src/                                    # Source code
│   ├── Facades/
│   │   ├── SSOAuth.php                    # Facade untuk SSOAuthService
│   │   └── SecurityMonitoring.php         # Facade untuk SecurityMonitoringService
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php     # OAuth2 auth flow (redirect, callback, logout)
│   │   │   └── SsoLogoutCallbackController.php  # Global logout webhook handler
│   │   │
│   │   └── Middleware/
│   │       ├── EnsureSSOAuthenticated.php      # Proteksi route memerlukan SSO login
│   │       ├── EnsureSSOSessionAlive.php       # Validasi token masih valid di SSO
│   │       ├── ValidateSessionIP.php           # IP binding untuk cegah session hijacking
│   │       ├── ValidateSessionUserAgent.php    # Monitor user-agent changes
│   │       ├── TrackSessionActivity.php        # Log semua user activities (audit trail)
│   │       ├── CheckRole.php                   # Role-based access control (RBAC)
│   │       └── CheckAccessArea.php             # Area-based access control (ABAC)
│   │
│   ├── Services/
│   │   ├── SSOAuthService.php             # OAuth2 authentication logic
│   │   └── SecurityMonitoringService.php  # Security monitoring & detection
│   │
│   ├── Providers/
│   │   └── MixuSSOAuthServiceProvider.php # Main service provider
│   │
│   ├── config/
│   │   └── mixuauth.php                   # Configuration template
│   │
│   ├── database/
│   │   └── migrations/
│   │       └── 2026_02_24_000001_create_session_security_tables.php
│   │           # Migration untuk session_activities dan security_events tables
│   │
│   ├── resources/
│   │   └── views/
│   │       └── auth/
│   │           └── sso-not-configured.blade.php  # Error view saat SSO belum config
│   │
│   └── routes/
│       └── sso-auth.php                   # Auth routes (login, callback, logout, webhook)
│
├── tests/                                  # Unit & feature tests
│   ├── Feature/
│   │   └── (akan ditambah saat development)
│   └── Unit/
│       └── (akan ditambah saat development)
│
├── composer.json                           # Composer configuration
├── README.md                               # Main documentation
├── INSTALLATION_GUIDE.md                   # Step-by-step installation
├── CHANGELOG.md                            # Version history & changes
├── TESTING_GUIDE.md                        # Testing instructions
├── PUBLISHING.md                           # How to publish to Packagist
├── LICENSE                                 # MIT License
├── .gitignore                              # Git ignore patterns
└── STRUCTURE.md                            # (This file) - Package structure docs
```

## File Descriptions

### Core Services

#### `src/Services/SSOAuthService.php`
OAuth2 authentication service yang handle:
- Generating authorize URL dengan CSRF state
- Exchanging authorization code untuk access token
- Fetching user info dari SSO Server
- Token refresh
- Token validation
- Logout flow
- Error handling dengan detailed messages

**Key Methods:**
```php
getAuthorizeUrl($state)
generateState()
exchangeCodeForToken($code)
getUser($accessToken)
refreshToken($refreshToken)
logout($accessToken)
isTokenValid($accessToken)
isConfigured()
```

#### `src/Services/SecurityMonitoringService.php`
Security monitoring & anomaly detection service:
- Brute force detection
- IP mismatch/anomaly detection
- Geographic impossibility detection
- Rapid request detection
- Security event logging
- Security statistics & reporting

**Key Methods:**
```php
checkBruteForceAttempts($ip, $minutes, $threshold)
checkIPMismatchPatterns($userId, $minutes)
logSecurityEvent($eventData)
detectAnomalies($userId)
getSecurityStats($days)
```

### Controllers

#### `src/Http/Controllers/Auth/AuthController.php`
Handles OAuth2 authentication flow:
- **redirect()**: Redirect user ke SSO Server authorize endpoint
- **callback()**: Handle callback dari SSO dengan auth code
- **logout()**: Logout user dari SSO dan local session

#### `src/Http/Controllers/SsoLogoutCallbackController.php`
Handles global logout webhook dari SSO Server:
- Verify HMAC signature
- Invalidate user sessions
- Support multiple user identification (by ID atau email)

### Middleware

Semua middleware files ada di `src/Http/Middleware/`:

| Middleware | Alias | Tujuan |
|-----------|-------|--------|
| EnsureSSOAuthenticated | sso.auth | Check user sudah login SSO |
| EnsureSSOSessionAlive | sso.alive | Check token masih valid di SSO |
| ValidateSessionIP | validate.session.ip | IP binding untuk cegah hijacking |
| ValidateSessionUserAgent | validate.session.ua | Monitor user-agent changes |
| TrackSessionActivity | track.activity | Log semua activities |
| CheckRole | role | Role-based access control |
| CheckAccessArea | access_area | Area-based access control |

### Facades

#### `src/Facades/SSOAuth.php`
```php
use Mixu\SSOAuth\Facades\SSOAuth;
SSOAuth::getAuthorizeUrl($state);
SSOAuth::getUser($token);
SSOAuth::isTokenValid($token);
```

#### `src/Facades/SecurityMonitoring.php`
```php
use Mixu\SSOAuth\Facades\SecurityMonitoring;
SecurityMonitoring::checkBruteForceAttempts($ip);
SecurityMonitoring::detectAnomalies($userId);
SecurityMonitoring::getSecurityStats(30);
```

### Configuration

#### `src/config/mixuauth.php`
OAuth2 configuration template dengan environment variables:
```php
'mixuauth' => [
    'base_url' => env('AUTH_BASE_URL'),
    'client_id' => env('AUTH_CLIENT_ID'),
    'client_secret' => env('AUTH_CLIENT_SECRET'),
    'redirect_uri' => env('AUTH_REDIRECT_URI'),
    'scopes' => env('AUTH_SCOPES'),
    'authorize_url' => '/oauth/authorize',
    'token_url' => '/oauth/token',
    'user_url' => '/api/user',
    'revoke_url' => '/oauth/revoke',
    'webhook_secret' => env('SSO_WEBHOOK_SECRET'),
]
```

### Database Migrations

#### `src/database/migrations/2026_02_24_000001_create_session_security_tables.php`
Creates 2 tables:

**session_activities**
- Audit trail untuk setiap user request
- Fields: id, sso_user_id, session_id, ip_address, method, path, status_code, user_agent, created_at
- Indexes: sso_user_id, session_id, ip_address, created_at

**security_events**
- Security events logging
- Fields: id, event_type, sso_user_id, email, ip_address, session_id, severity, details (JSON), user_agent, created_at
- Indexes: event_type, sso_user_id, ip_address, severity

### Routes

#### `src/routes/sso-auth.php`
Registers 4 routes:
- `GET /login` → AuthController@redirect (throttle 20/minute)
- `GET /auth/callback` → AuthController@callback
- `POST /logout` → AuthController@logout (throttle 10/minute)
- `POST /auth/sso/logout-callback` → SsoLogoutCallbackController@handle (no CSRF)

### Views

#### `src/resources/views/auth/sso-not-configured.blade.php`
Tampilan error saat SSO belum dikonfigurasi, dengan instruksi setup.

### Service Provider

#### `src/Providers/MixuSSOAuthServiceProvider.php`
Main service provider yang:
- Merges configuration
- Registers services
- Registers facades
- Publishes assets (config, migrations, routes, views)
- Loads migrations
- Loads routes
- Loads views

## Auto-Discovery

Package akan otomatis di-discover melalui `composer.json` extra section:

```json
"extra": {
    "laravel": {
        "providers": [
            "Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider"
        ],
        "aliases": {
            "SSOAuth": "Mixu\\SSOAuth\\Facades\\SSOAuth",
            "SecurityMonitoring": "Mixu\\SSOAuth\\Facades\\SecurityMonitoring"
        }
    }
}
```

## Namespace Structure

```
Mixu\SSOAuth\
├── Facades\
│   ├── SSOAuth
│   └── SecurityMonitoring
├── Http\
│   ├── Controllers\
│   │   ├── Auth\AuthController
│   │   └── SsoLogoutCallbackController
│   └── Middleware\
│       ├── EnsureSSOAuthenticated
│       ├── EnsureSSOSessionAlive
│       ├── ValidateSessionIP
│       ├── ValidateSessionUserAgent
│       ├── TrackSessionActivity
│       ├── CheckRole
│       └── CheckAccessArea
├── Services\
│   ├── SSOAuthService
│   └── SecurityMonitoringService
└── Providers\
    └── MixuSSOAuthServiceProvider
```

## Installation & Publishing Flow

### 1. Install Package
```bash
composer require mixu/sso-auth
```

### 2. Publish Assets
```bash
# Publish all
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider"

# Atau publish individual
php artisan vendor:publish --tag=mixu-sso-auth-config
php artisan vendor:publish --tag=mixu-sso-auth-migrations
php artisan vendor:publish --tag=mixu-sso-auth-routes
php artisan vendor:publish --tag=mixu-sso-auth-views
```

### 3. Published Files Location
```
project-root/
├── config/
│   └── mixuauth.php (from src/config/)
├── database/
│   └── migrations/
│       └── 2026_02_24_000001_create_session_security_tables.php
├── routes/
│   └── sso-auth.php (optional)
└── resources/
    └── views/
        └── vendor/
            └── mixu-sso-auth/ (optional)
```

## Documentation Files

| File | Purpose |
|------|---------|
| README.md | Main documentation dengan features & usage |
| INSTALLATION_GUIDE.md | Step-by-step installation & configuration |
| CHANGELOG.md | Version history & breaking changes |
| TESTING_GUIDE.md | How to test the package |
| PUBLISHING.md | How to publish to Packagist |
| STRUCTURE.md | (This) Package structure documentation |

## How to Extend/Customize

### 1. Override Service
```php
// app/Services/CustomSSOAuthService.php
class CustomSSOAuthService extends \Mixu\SSOAuth\Services\SSOAuthService
{
    public function getUser($accessToken)
    {
        $user = parent::getUser($accessToken);
        // Custom logic
        return $user;
    }
}

// Register in service provider
$this->app->bind(SSOAuthService::class, CustomSSOAuthService::class);
```

### 2. Override Middleware
```php
// app/Http/Middleware/CustomValidateSessionIP.php
class CustomValidateSessionIP extends \Mixu\SSOAuth\Http\Middleware\ValidateSessionIP
{
    // Custom IP validation logic
}

// Register in bootstrap/app.php
'validate.session.ip' => CustomValidateSessionIP::class,
```

### 3. Override Views
```bash
php artisan vendor:publish --tag=mixu-sso-auth-views
# Edit resources/views/vendor/mixu-sso-auth/
```

### 4. Add Custom Migrations
```php
// database/migrations/XXXX_XX_XX_add_custom_column.php
Schema::table('security_events', function (Blueprint $table) {
    $table->string('custom_field')->nullable();
});
```

## Performance Considerations

- **Activity Logging**: Semua requests di-log, use queue untuk high-traffic apps
- **Security Events**: Brute force checking menggunakan DB query, consider redis cache
- **Session Storage**: Gunakan database sessions atau redis untuk reliability

## Security Considerations

- ✅ CSRF protection dengan state parameter
- ✅ Session regeneration setelah login
- ✅ Server-side session (no JWT/tokens in cookies)
- ✅ IP binding untuk prevent hijacking
- ✅ HMAC signature verification untuk webhooks
- ✅ User-Agent monitoring
- ✅ Brute force detection
- ✅ SQL injection protection (Eloquent/query builder)
- ✅ XSS protection (Blade escaping)

## Compatibility

- **PHP**: 8.2+
- **Laravel**: 12.0+
- **Databases**: MySQL, PostgreSQL, SQLite, SQL Server
- **Session Drivers**: file, database, redis, cache

---

Untuk informasi detail setiap file, lihat file-nya langsung atau dokumentasi spesifik.
