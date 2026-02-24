# Changelog

Semua perubahan penting dalam project ini akan didokumentasikan di file ini.

Format based pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
dan project ini menggunakan [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-02-24

### Added
- Initial release dari Mixu SSO Auth Package
- OAuth2 authentication flow dengan authorization code grant
- Session security dengan IP binding dan User-Agent validation
- Activity tracking dan audit trail lengkap
- Security monitoring dengan brute force detection
- Anomaly detection untuk suspicious patterns
- Global logout webhook support
- Role-based access control (RBAC)
- Area-based access control (ABAC)
- Rate limiting di login dan logout
- Comprehensive security event logging
- Database migrations untuk session_activities dan security_events
- Middleware untuk authentication dan session validation
- Service untuk SSO dan security monitoring
- Facades untuk easy access ke services
- Complete documentation dan installation guide
- PHPUnit test support

### Features
- `EnsureSSOAuthenticated` middleware - Proteksi routes memerlukan SSO login
- `EnsureSSOSessionAlive` middleware - Validasi token masih aktif di SSO
- `ValidateSessionIP` middleware - Cegah session hijacking dengan IP binding
- `ValidateSessionUserAgent` middleware - Monitor perubahan user agent
- `TrackSessionActivity` middleware - Log semua user activities
- `CheckRole` middleware - Role-based access control
- `CheckAccessArea` middleware - Area-based access control
- `SSOAuthService` - OAuth2 authentication
- `SecurityMonitoringService` - Security monitoring dan detection
- `SSOAuth` Facade - Easy access ke SSOAuthService
- `SecurityMonitoring` Facade - Easy access ke SecurityMonitoringService

### Security
- CSRF protection dengan state parameter
- Session regeneration setelah login
- Secure session storage (server-side, encrypted)
- Token expiration handling
- HMAC signature validation untuk webhooks
- IP-based session binding
- User-Agent change monitoring
- Brute force attack detection
- Anomaly detection dengan geographic impossibility check

## [Unreleased]

### Planned
- Geolocation-based anomaly detection (MaxMind, IPStack integration)
- 2FA/MFA support
- Device fingerprinting
- Session management dashboard
- Security analytics dashboard
- Email notifications untuk critical events
- Slack notifications integration
- Rate limiting yang more sophisticated
- Transaction signing untuk sensitive operations

### Under Discussion
- Redis caching support untuk performance
- Distributed tracing support
- OpenTelemetry integration
- GraphQL API endpoint

---

## Installation

```bash
composer require mixu/sso-auth
php artisan vendor:publish --provider="Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider"
php artisan migrate
```

## Configuration

```env
AUTH_BASE_URL=https://auth.example.com
AUTH_CLIENT_ID=your-client-id
AUTH_CLIENT_SECRET=your-client-secret
AUTH_REDIRECT_URI=http://localhost:8000/auth/callback
SSO_WEBHOOK_SECRET=your-webhook-secret
```

## Support

- 📖 Documentation: [README.md](README.md)
- 🚀 Quick Start: [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)
- 🐛 Issues: [GitHub Issues](https://github.com/mixu/sso-auth/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/mixu/sso-auth/discussions)

---

**Note:** Ini adalah open-source package. Kontribusi welcome!
