<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Mixu\SSOAuth\Services\SSOAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSSOSessionAlive
{
    public function __construct(protected SSOAuthService $sso)
    {
    }

    /**
     * Periksa apakah access token di session masih diterima oleh SSO.
     * Jika tidak, clear session dan redirect ke login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->session()->get('sso_access_token');

        // Jika tidak ada token, biarkan middleware EnsureSSOAuthenticated yang menangani.
        if (empty($accessToken)) {
            return $next($request);
        }

        // Cek token ke SSO 
        $valid = $this->sso->isTokenValid($accessToken);
        if (! $valid) {
            Log::info('SSO session no longer valid; clearing local session');

            $request->session()->forget([
                'sso_user',
                'sso_access_token',
                'sso_refresh_token',
                'sso_token_expires_at',
            ]);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'SSO session expired. Please login again.'], 401);
            }
            return redirect()->route('login')->with('error', 'SSO session expired. Please login again.');
        }

        return $next($request);
    }
}
