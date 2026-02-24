<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionUserAgent
{
    /**
     * Handle an incoming request.
     * Validate bahwa User-Agent sama (detect browser/device changes).
     * Complementary ke IP binding untuk mendeteksi session hijacking.
     * 
     * Note: User-Agent bisa change, jadi warning saja (tidak strict failure).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // IMPORTANT: Check jika session sudah initialized
        if (!$this->isSessionAvailable($request)) {
            return $next($request);
        }

        // Skip validation jika user belum login
        if (!$request->session()->has('sso_user')) {
            return $next($request);
        }

        $userAgent = $request->userAgent();
        $sessionUserAgent = $request->session()->get('session_user_agent');

        // Jika session belum punya user agent, store-nya
        if (!$sessionUserAgent) {
            $request->session()->put('session_user_agent', $userAgent);
            return $next($request);
        }

        // Check user agent (warning saja, jangan block)
        if ($sessionUserAgent !== $userAgent) {
            Log::warning('Session User-Agent changed', [
                'original_ua' => substr($sessionUserAgent, 0, 100),
                'current_ua' => substr($userAgent, 0, 100),
                'user_id' => $request->session()->get('sso_user.id'),
                'ip_address' => $request->ip(),
            ]);

            // Update user agent di session (user bisa change browser/device)
            $request->session()->put('session_user_agent', $userAgent);
        }

        return $next($request);
    }

    /**
     * Check jika session sudah tersedia.
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
