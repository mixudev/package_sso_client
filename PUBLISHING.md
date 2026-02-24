# Publishing to Packagist

Panduan untuk publish Mixu SSO Auth Package ke Packagist agar bisa diinstal via Composer.

## Prerequisites

- ✅ GitHub account
- ✅ Packagist account (free)
- ✅ Git repository (GitHub atau GitLab)
- ✅ Package dengan struktur yang valid

## Step 1: Prepare Your Package

### 1.1 Verify composer.json

```bash
composer validate
```

Pastikan output menunjukkan: `The composer.json file is valid`

### 1.2 Create Git Repository

Jika belum ada:

```bash
cd packages/mixu-sso-auth
git init
git add .
git commit -m "Initial commit: Mixu SSO Auth Package v1.0.0"
```

### 1.3 Add Remote & Push

```bash
git remote add origin https://github.com/your-username/mixu-sso-auth.git
git branch -M main
git push -u origin main
```

### 1.4 Create Release Tag

```bash
git tag -a v1.0.0 -m "Release v1.0.0: Initial SSO Auth Package"
git push origin v1.0.0
```

## Step 2: Register on Packagist

### 2.1 Sign Up

1. Kunjungi https://packagist.org
2. Click "Sign Up"
3. Register dengan GitHub atau email
4. Verify email

### 2.2 Submit Package

1. Kunjungi https://packagist.org/packages/submit
2. Input repository URL: `https://github.com/your-username/mixu-sso-auth.git`
3. Click "Check"
4. Click "Submit"

### 2.3 Setup Webhook (Auto-Update)

1. Di Packagist, klik package Anda
2. Di halaman "Edit", copy webhook URL
3. Di GitHub repository:
   - Settings → Webhooks → Add webhook
   - Payload URL: (paste Packagist webhook)
   - Content type: `application/json`
   - Just the push event
   - Click "Add webhook"

## Step 3: Update Package

Setiap kali ada update:

### 3.1 Make Changes

```bash
# Edit files
# Update CHANGELOG.md
# Update version di README.md (jika perlu)
```

### 3.2 Commit & Tag

```bash
git add .
git commit -m "Add new feature: XYZ"
git tag -a v1.1.0 -m "Release v1.1.0: Add new feature"
git push origin main main:v1.1.0
```

Packagist akan otomatis update dalam 1-2 menit.

## Step 4: Verify Package

### 4.1 Check on Packagist

Kunjungi: `https://packagist.org/packages/mixu/sso-auth`

Pastikan:
- ✅ Package name benar: `mixu/sso-auth`
- ✅ Description tampil
- ✅ Latest version terlihat
- ✅ README render dengan benar

### 4.2 Test Installation

```bash
# Create test directory
mkdir test-install
cd test-install
composer init -n

# Install package
composer require mixu/sso-auth

# Verify
php -r "echo 'Package installed: ' . (file_exists('vendor/mixu/sso-auth/composer.json') ? 'YES' : 'NO');"
```

## Package Metadata

Pastikan `composer.json` memiliki:

```json
{
    "name": "mixu/sso-auth",
    "description": "Comprehensive SSO Authentication for Laravel",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your-email@example.com",
            "homepage": "https://yourwebsite.com"
        }
    ],
    "homepage": "https://github.com/your-username/mixu-sso-auth",
    "repository": {
        "type": "git",
        "url": "https://github.com/your-username/mixu-sso-auth.git"
    },
    "keywords": [
        "laravel",
        "sso",
        "oauth2",
        "authentication"
    ],
    "support": {
        "issues": "https://github.com/your-username/mixu-sso-auth/issues",
        "source": "https://github.com/your-username/mixu-sso-auth"
    },
    "require": {
        "php": "^8.2",
        "illuminate/support": "^12.0"
    },
    "extra": {
        "laravel": {
            "providers": [
                "Mixu\\SSOAuth\\Providers\\MixuSSOAuthServiceProvider"
            ]
        }
    }
}
```

## Versioning Strategy

Gunakan [Semantic Versioning](https://semver.org/):

- **MAJOR** (v1.0.0 → v2.0.0): Breaking changes
- **MINOR** (v1.0.0 → v1.1.0): New features, backward compatible
- **PATCH** (v1.0.0 → v1.0.1): Bug fixes, backward compatible

Contoh:

```bash
# New feature
git tag -a v1.1.0 -m "Add OAuth2 provider support"
git push origin v1.1.0

# Bug fix
git tag -a v1.1.1 -m "Fix IP binding issue"
git push origin v1.1.1

# Breaking change
git tag -a v2.0.0 -m "Refactor middleware structure"
git push origin v2.0.0
```

Sebelum release ke Packagist:

- ✅ Update CHANGELOG.md dengan semua changes
- ✅ Update version di README.md (jika ada)
- ✅ Run tests: `php artisan test`
- ✅ Check coverage: `php artisan test --coverage`
- ✅ Validate composer: `composer validate`
- ✅ **Verify publish tags configured** (see "Package Structure & Asset Publishing")
- ✅ Update documentation
- ✅ Create clean commit: `git commit -m "v1.x.x release"`
- ✅ Create tagged release: `git tag -a v1.x.x`
- ✅ Push to GitHub: `git push && git push --tags`
- ✅ Verify di Packagist
- ✅ Test installation: `composer require mixu/sso-auth`

## Package Structure & Asset Publishing

Package sudah di-setup dengan publish tags yang optimal. Client bisa publish semua assets dengan **satu command**:

```bash
php artisan vendor:publish --tag=mixu-sso-auth
```

### Assets Included

| Asset | Tag | Location |
|-------|-----|----------|
| Configuration | `mixu-sso-auth-config` | `config/mixuauth.php` |
| Migrations | `mixu-sso-auth-migrations` | `database/migrations/` |
| Routes | `mixu-sso-auth-routes` | `routes/sso-auth.php` |
| Views | `mixu-sso-auth-views` | `resources/views/vendor/mixu-sso-auth/` |
| **All Assets** | `mixu-sso-auth` | All above locations |

### Service Provider Configuration

File `src/Providers/MixuSSOAuthServiceProvider.php` mengkonfigurasi semua publish tags:

```php
// Publish Configuration
$this->publishes([...], ['mixu-sso-auth-config', 'mixu-sso-auth']);

// Publish Migrations
$this->publishes([...], ['mixu-sso-auth-migrations', 'mixu-sso-auth']);

// Publish Routes
$this->publishes([...], ['mixu-sso-auth-routes', 'mixu-sso-auth']);

// Publish Views
$this->publishes([...], ['mixu-sso-auth-views', 'mixu-sso-auth']);
```

**Note**: Setiap asset memiliki **dua tags**:
1. Specific tag (e.g., `mixu-sso-auth-config`) - untuk publish asset tertentu
2. Default tag (`mixu-sso-auth`) - untuk publish semua assets sekaligus

Ini memudahkan client untuk publish semua atau hanya asset yang diperlukan.

## Release Checklist

## Maintenance

### Monitor Package Health

Packagist dashboard menunjukkan:
- Downloads per month
- GitHub stars
- Issues & PRs
- Maintenance status

### Keep Dependencies Updated

```bash
# Check for outdated packages
composer outdated

# Update dependencies
composer update

# Test
php artisan test

# Commit & release
git commit -am "Update dependencies"
git tag -a vX.X.X
git push origin --all --tags
```

### Handle Issues & PRs

1. **Issues**: Respond cepat, request details jika perlu
2. **PRs**: Review, test, merge to main
3. **Release hotfix**: Tag new version untuk critical bugs

## Marketing Your Package

### Share Package

1. **Documentation**: Update README dengan examples
2. **Blog Post**: Tulis artikel tentang package
3. **Social Media**: Tweet tentang package
4. **Communities**: Share di Laravel communities
5. **Changelog**: Keep CHANGELOG.md updated

### Get Badges

Tambahkan ke README:

```markdown
[![Latest Stable Version](https://poser.pugx.org/mixu/sso-auth/version)](https://packagist.org/packages/mixu/sso-auth)
[![Total Downloads](https://poser.pugx.org/mixu/sso-auth/downloads)](https://packagist.org/packages/mixu/sso-auth)
[![License](https://poser.pugx.org/mixu/sso-auth/license)](https://packagist.org/packages/mixu/sso-auth)
[![PHP Version](https://poser.pugx.org/mixu/sso-auth/require/php)](https://packagist.org/packages/mixu/sso-auth)
```

## Troubleshooting

### Package Not Appearing

**Problem**: Package tidak muncul di Packagist setelah submit

**Solution**:
1. Check repository URL correct
2. Verify composer.json valid: `composer validate`
3. Check PHP version >= 5.3
4. Wait 2-3 minutes untuk indexing

### Webhook Not Working

**Problem**: Manual updates required, webhook tidak bekerja

**Solution**:
1. Check webhook URL benar
2. Check GitHub has write access
3. Re-add webhook
4. Manual trigger: Packagist "Force Update" button

### Version Not Updating

**Problem**: Push tag tapi Packagist tidak update

**Solution**:
1. Verify tag format: `v1.2.3` (exact format)
2. Check tag berisi correct version di composer.json
3. Manual update: Click "Update" di Packagist page
4. Wait 2-3 minutes

## Resources

- 📖 [Packagist Documentation](https://packagist.org/docs/)
- 📖 [Composer Documentation](https://getcomposer.org/doc/)
- 📖 [Semantic Versioning](https://semver.org/)
- 📖 [PHP Package Checklist](https://phppackagechecklist.com/)

## Summary

```bash
# Final checklist
composer validate              # ✅ Valid
php artisan test              # ✅ All tests pass
git status                    # ✅ Clean
git tag v1.0.0               # ✅ Tag created
git push --tags              # ✅ Pushed to GitHub
# Visit packagist.org/packages/mixu/sso-auth  # ✅ Appears!
```

Selesai! Package Anda sudah live di Packagist dan bisa diinstal via Composer.
