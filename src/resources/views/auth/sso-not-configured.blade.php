<!-- Tampilan saat SSO tidak dikonfigurasi -->
<div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">SSO Authentication Not Configured</h4>
    <p>
        MixuAuth SSO belum dikonfigurasi. Silakan set environment variable berikut di file <code>.env</code>:
    </p>
    <ul>
        <li><code>AUTH_BASE_URL</code> - URL SSO Server (contoh: https://auth.example.com)</li>
        <li><code>AUTH_CLIENT_ID</code> - OAuth2 Client ID</li>
        <li><code>AUTH_CLIENT_SECRET</code> - OAuth2 Client Secret</li>
        <li><code>AUTH_REDIRECT_URI</code> - Redirect URI (default: APP_URL/auth/callback)</li>
        <li><code>AUTH_SCOPES</code> - Requested Scopes (default: empty)</li>
        <li><code>SSO_WEBHOOK_SECRET</code> - Secret untuk Global Logout webhook</li>
    </ul>
    <hr>
    <p class="mb-0">
        Setelah konfigurasi, jalankan:
        <code>php artisan migrate</code>
    </p>
</div>
