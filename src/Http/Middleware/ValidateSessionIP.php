<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionIP
{
    /**
     * Handle an incoming request.
     * Validate bahwa session digunakan dari IP address yang sama saat login.
     * Prevent session hijacking via IP-based binding.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // IMPORTANT: Check jika session sudah initialized
        // Jika belum, session middleware belum jalan, skip validation
        if (!$this->isSessionAvailable($request)) {
            return $next($request);
        }

        // Skip validation jika user belum login
        if (!$request->session()->has('sso_user')) {
            return $next($request);
        }

        $sessionIP = $request->session()->get('session_ip');
        $currentIP = $request->ip();

        // Jika session belum punya IP (login pertama kali), store IP-nya
        if (!$sessionIP) {
            $request->session()->put('session_ip', $currentIP);
            Log::info('Session IP bound', [
                'ip' => $currentIP,
                'user_id' => $request->session()->get('sso_user.id'),
            ]);
            return $next($request);
        }

        // Validasi IP address cocok
        if ($sessionIP !== $currentIP) {
            Log::warning('Session IP mismatch - potential session hijacking attempt', [
                'original_ip' => $sessionIP,
                'current_ip' => $currentIP,
                'user_id' => $request->session()->get('sso_user.id'),
                'user_email' => $request->session()->get('sso_user.email'),
                'user_agent' => substr($request->userAgent(), 0, 100),
            ]);

            // Clear session dan force re-login
            $request->session()->forget([
                'sso_user',
                'sso_access_token',
                'sso_refresh_token',
                'sso_token_expires_at',
                'session_ip',
            ]);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Return error response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session security check failed. Please login again.',
                    'reason' => 'ip_mismatch',
                ], 401);
            }

            return redirect()->route('login')->with('error', 
                'Your session was accessed from a different location. Please login again for security.');
        }

        return $next($request);
    }

    /**
     * Check jika session sudah tersedia (session middleware sudah jalan).
     */
    private function isSessionAvailable(Request $request): bool
    {
        try {
            $request->session()->getId();
            return true;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Session store not set')) {
                return false;
            }
            throw $e;
        }
    }
}
