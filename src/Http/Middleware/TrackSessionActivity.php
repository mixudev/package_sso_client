<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    /**
     * Handle an incoming request.
     * Track user activity untuk audit trail dan anomaly detection.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // IMPORTANT: Check jika session sudah initialized
        if (!$this->isSessionAvailable($request)) {
            return $response;
        }

        // Update last activity untuk authenticated users
        if ($request->session()->has('sso_user')) {
            $user = $request->session()->get('sso_user');
            
            // Jika database table 'session_activities' ada, update activity log
            if ($this->shouldLogActivity($request)) {
                try {
                    DB::table('session_activities')->insert([
                        'sso_user_id' => $user['id'] ?? null,
                        'session_id' => $request->session()->getId(),
                        'ip_address' => $request->ip(),
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'status_code' => $response->getStatusCode(),
                        'user_agent' => substr($request->userAgent(), 0, 255),
                        'created_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Table mungkin belum ada, skip saja
                    Log::debug('Could not log activity (table may not exist)', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update last_activity_at di session
            $request->session()->put('last_activity_at', now());
        }

        return $response;
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

    /**
     * Skip logging untuk asset requests (js, css, images, etc).
     */
    private function shouldLogActivity(Request $request): bool
    {
        $ignorePaths = [
            '/up',
            '/health',
        ];

        $ignorePatterns = [
            '/\.js$/',
            '/\.css$/',
            '/\.map$/',
            '/\.png$/',
            '/\.jpg$/',
            '/\.jpeg$/',
            '/\.gif$/',
            '/\.svg$/',
            '/\.woff$/',
            '/\.woff2$/',
            '/\.ttf$/',
            '/\.eot$/',
            '/manifest\.json$/',
        ];

        $path = $request->path();

        // Check direct path match
        if (in_array($path, $ignorePaths)) {
            return false;
        }

        // Check pattern match
        foreach ($ignorePatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }
}
