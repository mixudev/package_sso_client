<?php

namespace Mixu\SSOAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mixu\SSOAuth\Services\SecurityMonitoringService;
use Symfony\Component\HttpFoundation\Response;

class LogAuditTrail
{
    public function __construct(
        private SecurityMonitoringService $security
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Store request data for later logging
        $request->merge([
            '_audit_start_time' => microtime(true),
        ]);

        $response = $next($request);

        // Log audit trail for state-changing requests (POST, PUT, DELETE, PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    /**
     * Log audit trail.
     */
    private function logAudit(Request $request, Response $response): void
    {
        try {
            $userId = $request->session()->get('sso_user.id');
            $statusCode = $response->getStatusCode();
            // treat any 2xx/3xx response as success, because redirects are common
            $isSuccess = $statusCode < 400;

            // Determine action from request
            $path = $request->path();
            $action = $this->determineAction($request->method(), $path);

            // prepare values payloads
            $newValues = $request->except(['password', 'password_confirmation', '_token']);
            $oldValues = null;

            // if update (PUT/PATCH), attempt to grab existing record from db by id
            if (in_array($request->method(), ['PUT','PATCH'])) {
                // guess table name from first path segment
                $segments = explode('/', trim($path, '/'));
                $id = $newValues['id'] ?? null;
                if (!$id) {
                    // try route parameter named id
                    $id = $request->route('id') ?? $request->route('user') ?? null;
                }
                if (isset($segments[0]) && $id) {
                    $table = $segments[0];
                    try {
                        $record = DB::table($table)->where('id', $id)->first();
                        if ($record) {
                            $oldValues = (array) $record;
                        }
                    } catch (\Exception $e) {
                        // ignore if table doesn't exist or query fails
                        \Log::debug('Could not fetch old values for audit', ['error' => $e->getMessage(), 'table' => $table]);
                    }
                }
            }

            $auditData = [
                'user_id' => $userId,
                'user_name' => $request->session()->get('sso_user.name'),
                'email' => $request->session()->get('sso_user.email'),
                'action' => $action,
                'method' => $request->method(),
                'path' => $path,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $statusCode,
                'new_values' => $newValues,
                'result' => $isSuccess ? 'success' : 'failed',
                'details' => [
                    'duration_ms' => round((microtime(true) - $request->get('_audit_start_time', microtime(true))) * 1000, 2),
                    'request_method' => $request->method(),
                ],
            ];
            if ($oldValues !== null) {
                $auditData['old_values'] = $oldValues;
            }

            $this->security->logAuditTrail($auditData);
        } catch (\Exception $e) {
            \Log::error('Failed to log audit trail', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine action from request method and path.
     */
    private function determineAction(string $method, string $path): string
    {
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'unknown',
        };
    }
}
