<?php

namespace Mixu\SSOAuth\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Mixu\SSOAuth\Services\SSOAuthService;

class AuthController
{
    public function __construct(
        protected SSOAuthService $sso
    ) {}

    /**
     * Redirect ke MixuAuth untuk login.
     */
    public function redirect(Request $request): RedirectResponse|View
    {
        if (! $this->sso->isConfigured()) {
            Log::warning('SSO not configured. Set AUTH_BASE_URL, AUTH_CLIENT_ID, AUTH_CLIENT_SECRET.');
            return view('mixu-sso-auth::auth.sso-not-configured');
        }

        $state = $this->sso->generateState();
        $request->session()->put('oauth_state', $state);
        $request->session()->put('oauth_intended_url', $request->query('intended', url()->previous()));

        $url = $this->sso->getAuthorizeUrl($state);
        return redirect()->away($url);
    }

    /**
     * Callback dari MixuAuth: tukar code → token → user → session.
     */
    public function callback(Request $request): RedirectResponse
    {
        $this->sso->clearLastError();

        // CSRF: verifikasi state
        $state = $request->session()->pull('oauth_state');
        if (! $state || $state !== $request->query('state')) {
            Log::warning('OAuth callback state mismatch.');
            return redirect()->route('home')->with('error', __('Invalid session. Please try again.'));
        }

        $code = $request->query('code');
        if (! $code) {
            $error = $request->query('error', 'unknown');
            Log::warning('OAuth callback missing code.', ['error' => $error]);
            return redirect()->route('home')->with('error', __('Login was cancelled or failed.'));
        }

        $tokens = $this->sso->exchangeCodeForToken($code);
        if (! $tokens) {
            Log::warning('Failed to exchange OAuth code for token.');
            return redirect()->route('home')->with('error', __('Could not complete login. Please try again.'));
        }

        $user = $this->sso->getUser($tokens['access_token']);
        if (! $user || empty($user['id'])) {
            Log::warning('Failed to fetch user from SSO.');
            return redirect()->route('home')->with('error', __('Could not load your profile. Please try again.'));
        }

        // Session lokal: simpan user + token (hanya di server, tidak di cookie)
        $request->session()->put('sso_user', $user);
        $request->session()->put('sso_access_token', $tokens['access_token']);
        $request->session()->put('sso_token_expires_at', now()->addSeconds($tokens['expires_in']));
        if (! empty($tokens['refresh_token'])) {
            $request->session()->put('sso_refresh_token', $tokens['refresh_token']);
        }
        
        // SECURITY: Bind session ke IP address dan User-Agent (detect session hijacking)
        $request->session()->put('session_ip', $request->ip());
        $request->session()->put('session_user_agent', $request->userAgent());
        $request->session()->put('login_at', now());
        
        $request->session()->regenerate();

        // Log login event ke security_events table
        try {
            DB::table('security_events')->insert([
                'event_type' => 'login',
                'sso_user_id' => $user['id'] ?? null,
                'email' => $user['email'] ?? null,
                'ip_address' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'severity' => 'low',
                'details' => json_encode([
                    'roles' => $user['roles'] ?? [],
                    'access_areas' => $user['access_areas'] ?? [],
                ]),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::debug('Could not log login event (table may not exist yet)', [
                'error' => $e->getMessage(),
            ]);
        }

        $intended = $request->session()->pull('oauth_intended_url', route('dashboard'));
        if ($intended && $intended !== route('login') && $intended !== url()->current()) {
            return redirect()->to($intended);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout: logout session web di SSO Server, lalu hapus session lokal.
     * Token OAuth tetap valid (tidak di-revoke), hanya session web yang dihapus.
     */
    public function logout(Request $request): RedirectResponse
    {
        $accessToken = $request->session()->get('sso_access_token');
        $user = $request->session()->get('sso_user');

        // Log logout event
        try {
            DB::table('security_events')->insert([
                'event_type' => 'logout',
                'sso_user_id' => $user['id'] ?? null,
                'email' => $user['email'] ?? null,
                'ip_address' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'severity' => 'low',
                'details' => json_encode([
                    'logout_reason' => 'user_initiated',
                    'session_duration_seconds' => $user ? (now()->diffInSeconds($request->session()->get('login_at'))) : null,
                ]),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::debug('Could not log logout event (table may not exist yet)', [
                'error' => $e->getMessage(),
            ]);
        }

        // Logout session web di SSO Server (token tetap valid)
        if ($accessToken) {
            $logoutResult = $this->sso->logout($accessToken);
            
            if ($logoutResult && $logoutResult['success']) {
                Log::info('User logged out from SSO Server (session cleared).');
            } else {
                Log::warning('Failed to logout from SSO Server, but continuing with local logout.');
            }
        }

        // Hapus semua data SSO dari session
        $request->session()->forget([
            'sso_user',
            'sso_access_token',
            'sso_refresh_token',
            'sso_token_expires_at',
            'session_ip',
            'session_user_agent',
            'login_at',
        ]);

        // Invalidate session dan regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Berhasil logout');
    }
}
