<?php

namespace Mixu\SSOAuth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOAuthService
{
    protected array $config;

    /** Detail error terakhir untuk debugging (step, message, url, status, body, suggestion) */
    protected array $lastError = [];

    public function __construct()
    {
        $this->config = config('services.mixuauth', []);
    }

    /**
     * Ambil detail error terakhir (setelah exchangeCodeForToken/getUser gagal).
     * Dipakai untuk tampilan debugging di halaman.
     */
    public function getLastError(): array
    {
        return $this->lastError;
    }

    public function clearLastError(): void
    {
        $this->lastError = [];
    }

    protected function setLastError(string $step, string $message, ?string $url = null, ?int $status = null, ?string $body = null, ?string $suggestion = null): void
    {
        $this->lastError = [
            'step' => $step,
            'message' => $message,
            'url' => $url,
            'status' => $status,
            'body' => $body !== null ? Str::limit($body, 1000) : null,
            'suggestion' => $suggestion,
            'at' => now()->toIso8601String(),
        ];
        Log::warning('SSO error: ' . $step, $this->lastError);
    }

    /**
     * Generate URL untuk redirect user ke MixuAuth (authorize).
     * State = CSRF protection.
     */
    public function getAuthorizeUrl(string $state): string
    {
        $base = rtrim($this->config['base_url'], '/');
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'scope' => $this->config['scopes'],
            'state' => $state,
        ]);

        return $base . $this->config['authorize_url'] . '?' . $params;
    }

    /**
     * Generate state (random) untuk CSRF protection.
     */
    public function generateState(): string
    {
        return Str::random(40);
    }

    /**
     * Tukar authorization code ke access token + refresh token.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}|null
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        $this->clearLastError();
        $base = rtrim($this->config['base_url'], '/');
        $url = $base . $this->config['token_url'];

        $response = Http::asForm()
            ->timeout(15)
            ->post($url, [
                'grant_type' => 'authorization_code',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'redirect_uri' => $this->config['redirect_uri'],
                'code' => $code,
            ]);

        if (! $response->successful()) {
            $this->setLastError(
                'token_exchange',
                'Tukar authorization code ke access token gagal.',
                $url,
                $response->status(),
                $response->body(),
                'Cek: AUTH_BASE_URL benar? Redirect URI di SSO = ' . ($this->config['redirect_uri'] ?? '') . '? Client ID/Secret benar? Cek log SSO.'
            );
            return null;
        }

        $data = $response->json();
        if (empty($data['access_token'])) {
            $this->setLastError(
                'token_exchange',
                'Response /oauth/token tidak berisi access_token.',
                $url,
                $response->status(),
                $response->body(),
                'Cek format response Passport di SSO (harus ada access_token, expires_in).'
            );
            return null;
        }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_in' => (int) ($data['expires_in'] ?? 1800),
        ];
    }

    /**
     * Ambil profil user dari MixuAuth (GET /api/user).
     * Mendukung format: object langsung atau dibungkus dalam "data".
     * roles/access_areas: array string atau array object dengan key "name"/"slug".
     *
     * @return array{id: int, name: string, email: string, roles: array, access_areas: array}|null
     */
    public function getUser(string $accessToken): ?array
    {
        $this->clearLastError();
        $base = rtrim($this->config['base_url'], '/');
        $url = $base . $this->config['user_url'];

        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->acceptJson()
            ->get($url);

        if (! $response->successful()) {
            $suggestion = '401: Token tidak diterima — di SSO pastikan config/auth.php guard api pakai driver passport. ';
            $suggestion .= '404: URL salah — pastikan AUTH_BASE_URL benar (mis. http://sso.test atau http://127.0.0.1:8000). ';
            $suggestion .= '5xx: Cek log SSO.';
            $this->setLastError(
                'get_user',
                'Request GET /api/user gagal (HTTP ' . $response->status() . ').',
                $url,
                $response->status(),
                $response->body(),
                $suggestion
            );
            return null;
        }

        $data = $response->json();
        if (! is_array($data)) {
            $this->setLastError(
                'get_user',
                'Response /api/user bukan JSON array.',
                $url,
                $response->status(),
                is_string($data) ? $data : json_encode($data),
                'Pastikan endpoint GET /api/user di SSO return JSON object dengan id, name, email, roles, access_areas.'
            );
            return null;
        }
        // Bisa dibungkus dalam "data" atau "user" (Laravel API resource / convention)
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }
        if (isset($data['user']) && is_array($data['user'])) {
            $data = $data['user'];
        }

        $id = (int) ($data['id'] ?? 0);
        if ($id === 0) {
            $this->setLastError(
                'get_user',
                'Response /api/user tidak berisi field "id" (atau id = 0).',
                $url,
                $response->status(),
                json_encode($data),
                'Pastikan UserInfoController di SSO return: id (int), name, email, roles (array), access_areas (array).'
            );
            return null;
        }

        return [
            'id' => $id,
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'roles' => $this->normalizeList($data['roles'] ?? []),
            'access_areas' => $this->normalizeList($data['access_areas'] ?? []),
        ];
    }

    /**
     * Normalisasi array: bisa array of string atau array of object {name} / {slug}.
     */
    private function normalizeList(array $list): array
    {
        $out = [];
        foreach ($list as $item) {
            if (is_string($item)) {
                $out[] = $item;
            } elseif (is_array($item)) {
                $out[] = $item['name'] ?? $item['slug'] ?? (string) ($item['id'] ?? '');
            }
        }
        return array_values(array_filter($out));
    }

    /**
     * Refresh access token (opsional, untuk session panjang).
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}|null
     */
    public function refreshToken(string $refreshToken): ?array
    {
        if (empty($refreshToken)) {
            return null;
        }

        $base = rtrim($this->config['base_url'], '/');
        $url = $base . $this->config['token_url'];

        $response = Http::asForm()
            ->timeout(15)
            ->post($url, [
                'grant_type' => 'refresh_token',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'refresh_token' => $refreshToken,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (empty($data['access_token'])) {
            return null;
        }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_in' => (int) ($data['expires_in'] ?? 1800),
        ];
    }

    /**
     * Logout session web di SSO Server (default, recommended).
     * POST /api/logout dengan Bearer token.
     * Hanya hapus session web di SSO Server, token OAuth tetap valid.
     * 
     * @return array{success: bool, message: string, session_cleared: bool}|null
     */
    public function logout(string $accessToken): ?array
    {
        if (empty($accessToken)) {
            return null;
        }

        $base = rtrim($this->config['base_url'], '/');
        $url = $base . '/api/logout';

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(10)
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    return [
                        'success' => true,
                        'message' => $data['message'] ?? 'Successfully logged out from SSO Server',
                        'session_cleared' => $data['session_cleared'] ?? false,
                    ];
                }
            }

            // Log jika gagal
            Log::warning('SSO logout failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to logout from SSO Server',
                'session_cleared' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('SSO logout exception.', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);

            return [
                'success' => false,
                'message' => 'Error contacting SSO Server: ' . $e->getMessage(),
                'session_cleared' => false,
            ];
        }
    }

    /**
     * Cek apakah konfigurasi SSO lengkap.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->config['base_url'])
            && ! empty($this->config['client_id'])
            && ! empty($this->config['client_secret'])
            && ! empty($this->config['redirect_uri']);
    }

    /**
     * Cek apakah access token masih valid / diterima oleh SSO (lightweight check).
     * Mengembalikan true jika endpoint user menerima token (HTTP 200).
     */
    public function isTokenValid(string $accessToken): bool
    {
        if (empty($accessToken)) {
            return false;
        }

        $base = rtrim($this->config['base_url'], '/');
        $url = $base . $this->config['user_url'];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(6)
                ->acceptJson()
                ->get($url);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('SSO token validation failed: ' . $e->getMessage(), ['url' => $url]);
            return false;
        }
    }
}
