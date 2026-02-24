# Quick Start Guide

Mulai menggunakan Mixu SSO Auth dalam 5 menit!

## 1. Install Package (30 seconds)

```bash
composer require mixu/sso-auth
```

## 2. Publish Configuration (30 seconds)

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-config
```

## 3. Setup Environment Variables (1 minute)

Edit `.env`:

```env
AUTH_BASE_URL=https://auth.yourcompany.com
AUTH_CLIENT_ID=your-client-id
AUTH_CLIENT_SECRET=your-client-secret
AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
AUTH_SCOPES=openid profile email
SSO_WEBHOOK_SECRET=your-webhook-secret
```

## 4. Run Migrations (1 minute)

```bash
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider" --tag=mixu-sso-auth-migrations
php artisan migrate
```

## 5. Protect Your Routes (1 minute)

Edit `routes/web.php`:

```php
use Mixu\SSOAuth\Http\Middleware\EnsureSSOAuthenticated;
use Mixu\SSOAuth\Http\Middleware\EnsureSSOSessionAlive;
use Mixu\SSOAuth\Http\Middleware\ValidateSessionIP;
use Mixu\SSOAuth\Http\Middleware\TrackSessionActivity;

// Public routes
Route::get('/', fn() => view('welcome'))->name('home');

// Protected routes
Route::middleware([
    'sso.auth',           // Check login
    'sso.alive',          // Check token valid
    'validate.session.ip', // IP security
    'track.activity',     // Activity logging
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

## 6. Add Login/Logout Links (1 minute)

Di layout file (`resources/views/layouts/app.blade.php`):

```blade
<!-- Before login -->
@if (session('sso_user'))
    <div>
        Welcome, {{ session('sso_user.name') }}!
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button>Logout</button>
        </form>
    </div>
@else
    <a href="{{ route('auth.login') }}">Login</a>
@endif
```

## Test Installation

```bash
# Start server
php artisan serve

# Visit http://localhost:8000
# Click login link (redirect to SSO)
```

## Next Steps

1. **Setup Role-Based Access**
   ```php
   Route::middleware('role:admin')->group(function () {
       Route::get('/admin', [AdminController::class, 'index']);
   });
   ```

2. **Setup Area-Based Access**
   ```php
   Route::middleware('access_area:portal')->group(function () {
       Route::get('/portal', [PortalController::class, 'index']);
   });
   ```

3. **Monitor Security Events**
   ```php
   use Mixu\SSOAuth\Services\SecurityMonitoringService;
   
   $service = app(SecurityMonitoringService::class);
   $stats = $service->getSecurityStats(30); // Last 30 days
   ```

4. **Customize Middleware**
   - Disable IP binding if behind proxy
   - Adjust brute force thresholds
   - Add custom security checks

## Common Issues

### "SSO not configured"
→ Check `.env` variables are set correctly

### "Token exchange failed"
→ Verify `AUTH_CLIENT_ID` and `AUTH_CLIENT_SECRET` are correct

### "Session IP mismatch"
→ Remove `validate.session.ip` middleware if behind proxy/VPN

### "Logout not working"
→ Check `SSO_WEBHOOK_SECRET` matches SSO Server configuration

## Full Documentation

- 📖 [Complete README](README.md)
- 🚀 [Installation Guide](INSTALLATION_GUIDE.md)
- 🔧 [Configuration](STRUCTURE.md)
- 🧪 [Testing Guide](TESTING_GUIDE.md)
- 📦 [Publishing to Packagist](PUBLISHING.md)

## Support

- Issues: Create GitHub issue
- Email: support@mixu.io
- Website: https://mixu.io

---

**That's it!** Your app now has enterprise SSO authentication with security monitoring. 🎉
