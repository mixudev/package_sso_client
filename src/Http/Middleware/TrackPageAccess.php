<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mixu\SSOAuth\Services\SecurityMonitoringService;
use Symfony\Component\HttpFoundation\Response;

class TrackPageAccess
{
    public function __construct(
        private SecurityMonitoringService $security
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track the page access
        $this->trackAccess($request, $response);

        return $response;
    }

    /**
     * Track page access after response.
     */
    private function trackAccess(Request $request, Response $response): void
    {
        try {
            $userId = $request->session()->get('sso_user.id');
            $sessionId = $request->session()->getId();
            $statusCode = $response->getStatusCode();

            // Determine result based on status code
            if ($statusCode < 400) {
                $result = 'success';
            } elseif ($statusCode === 403) {
                $result = 'denied';
            } else {
                $result = 'failed';
            }

            // Log page access
            $this->security->logPageAccess([
                'user_id' => $userId,
                'email' => $request->session()->get('sso_user.email'),
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $statusCode,
                'result' => $result,
            ]);

            // Log security event for 4xx errors
            if ($statusCode >= 400) {
                $this->security->logSecurityEvent([
                    'event_type' => $statusCode === 403 ? 'access_denied' : 'access_failed',
                    'sso_user_id' => $userId,
                    'email' => $request->session()->get('sso_user.email'),
                    'ip_address' => $request->ip(),
                    'session_id' => $sessionId,
                    'severity' => $statusCode === 403 ? 'high' : 'medium',
                    'details' => [
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'status_code' => $statusCode,
                    ],
                    'user_agent' => $request->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            // Don't let tracking errors break the application
            \Log::error('Failed to track page access', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
