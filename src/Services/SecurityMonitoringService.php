<?php

namespace Mixu\SSOAuth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SecurityMonitoringService
{
    /**
     * Log audit trail untuk semua aktivitas.
     */
    public function logAuditTrail(array $data): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => $data['user_id'] ?? null,
                'user_name' => $data['user_name'] ?? null,
                'email' => $data['email'] ?? null,
                'action' => $data['action'] ?? 'unknown',
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ? substr($data['user_agent'], 0, 255) : null,
                'method' => $data['method'] ?? null,
                'path' => $data['path'] ?? null,
                'status_code' => $data['status_code'] ?? null,
                'old_values' => isset($data['old_values']) ? json_encode($data['old_values']) : null,
                'new_values' => isset($data['new_values']) ? json_encode($data['new_values']) : null,
                'result' => $data['result'] ?? 'success', // success, failed, denied
                'details' => isset($data['details']) ? json_encode($data['details']) : null,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log audit trail', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Check untuk anomalous login patterns (brute force, etc).
     */
    public function checkBruteForceAttempts(string $ip, int $minutes = 15, int $threshold = 3): bool
    {
        $failures = DB::table('security_events')
            ->where('event_type', 'auth_failure')
            ->where('ip_address', $ip)
            ->where('created_at', '>', now()->subMinutes($minutes))
            ->count();

        if ($failures >= $threshold) {
            Log::warning('Potential brute force attack detected', [
                'ip' => $ip,
                'attempts' => $failures,
                'timeframe_minutes' => $minutes,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check untuk IP mismatch attempts (session hijacking).
     */
    public function checkIPMismatchPatterns(int $userId, int $minutes = 60): array
    {
        $activities = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->where('created_at', '>', now()->subMinutes($minutes))
            ->orderBy('created_at', 'desc')
            ->pluck('ip_address')
            ->unique()
            ->toArray();

        if (count($activities) > 3) {
            Log::warning('Multiple IP addresses detected for single user', [
                'user_id' => $userId,
                'ip_count' => count($activities),
                'ips' => $activities,
                'timeframe_minutes' => $minutes,
            ]);
        }

        return $activities;
    }

    /**
     * Log page access attempt (success atau failed).
     */
    public function logPageAccess(array $data): void
    {
        try {
            $result = $data['result'] ?? 'success';
            $severity = match($result) {
                'denied' => 'high',
                'failed' => 'medium',
                default => 'low',
            };

            DB::table('session_activities')->insert([
                'sso_user_id' => $data['user_id'] ?? null,
                'session_id' => $data['session_id'] ?? '',
                'ip_address' => $data['ip_address'] ?? '',
                'method' => $data['method'] ?? 'GET',
                'path' => $data['path'] ?? '',
                'status_code' => $data['status_code'] ?? 0,
                'user_agent' => $data['user_agent'] ? substr($data['user_agent'], 0, 255) : null,
                'created_at' => now(),
            ]);

            // Log ke audit juga for detailed tracking
            $this->logAuditTrail([
                'user_id' => $data['user_id'] ?? null,
                'user_name' => $data['user_name'] ?? null,
                'action' => 'page_access',
                'method' => $data['method'] ?? 'GET',
                'path' => $data['path'] ?? '',
                'status_code' => $data['status_code'] ?? 0,
                'ip_address' => $data['ip_address'] ?? '',
                'user_agent' => $data['user_agent'] ?? null,
                'result' => $result,
            ]);

            // Log security event if access was denied or failed
            if ($result !== 'success') {
                DB::table('security_events')->insert([
                    'event_type' => $result === 'denied' ? 'access_denied' : 'access_failed',
                    'sso_user_id' => $data['user_id'] ?? null,
                    'email' => $data['email'] ?? null,
                    'ip_address' => $data['ip_address'] ?? '',
                    'session_id' => $data['session_id'] ?? null,
                    'severity' => $severity,
                    'details' => json_encode([
                        'path' => $data['path'] ?? '',
                        'method' => $data['method'] ?? 'GET',
                        'status_code' => $data['status_code'] ?? 0,
                        'reason' => $data['reason'] ?? null,
                    ]),
                    'user_agent' => $data['user_agent'] ? substr($data['user_agent'], 0, 255) : null,
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log page access', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Log security event dengan automatic alerting untuk critical events.
     */
    public function logSecurityEvent(array $eventData): void
    {
        // Ensure required fields
        $eventData = array_merge([
            'event_type' => 'unknown',
            'sso_user_id' => null,
            'email' => null,
            'user_name' => null,
            'ip_address' => null,
            'session_id' => null,
            'severity' => 'medium',
            'details' => null,
            'user_agent' => null,
        ], $eventData);

        try {
            DB::table('security_events')->insert([
                'event_type' => $eventData['event_type'],
                'sso_user_id' => $eventData['sso_user_id'],
                'email' => $eventData['email'],
                'user_name' => $eventData['user_name'],
                'ip_address' => $eventData['ip_address'],
                'session_id' => $eventData['session_id'],
                'severity' => $eventData['severity'],
                'details' => is_array($eventData['details']) ? json_encode($eventData['details']) : $eventData['details'],
                'user_agent' => $eventData['user_agent'] ? substr($eventData['user_agent'], 0, 255) : null,
                'created_at' => now(),
            ]);

            // Log to audit trail
            $this->logAuditTrail([
                'user_id' => $eventData['sso_user_id'],
                'user_name' => $eventData['user_name'],
                'email' => $eventData['email'],
                'action' => 'security_event',
                'ip_address' => $eventData['ip_address'],
                'user_agent' => $eventData['user_agent'],
                'details' => $eventData['details'],
                'result' => 'logged',
            ]);

            // Persist notification for UI (store all severities)
            try {
                DB::table('security_notifications')->insert([
                    'sso_user_id' => $eventData['sso_user_id'] ?? null,
                    'user_name' => $eventData['user_name'] ?? null,
                    'email' => $eventData['email'] ?? null,
                    'event_type' => $eventData['event_type'] ?? null,
                    'severity' => $eventData['severity'] ?? 'medium',
                    'title' => $eventData['title'] ?? (isset($eventData['event_type']) ? ucfirst(str_replace('_', ' ', $eventData['event_type'])) : 'Security Event'),
                    'message' => is_string($eventData['details']) ? $eventData['details'] : (is_array($eventData['details']) ? json_encode($eventData['details']) : null),
                    'details' => is_array($eventData['details']) ? json_encode($eventData['details']) : (is_string($eventData['details']) ? $eventData['details'] : null),
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to persist security notification (logSecurityEvent)', [
                    'error' => $e->getMessage(),
                    'event' => $eventData,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log security event', [
                'error' => $e->getMessage(),
                'event' => $eventData,
            ]);
        }

        // Send alerts untuk critical events
        if ($eventData['severity'] === 'critical' || $eventData['severity'] === 'high') {
            $this->sendAlert($eventData);
        }
    }

    /**
     * Detect suspicious patterns dalam activity log.
     */
    public function detectAnomalies(int $userId): array
    {
        $anomalies = [];

        // Check 1: Multiple IP addresses dalam short timeframe
        $ips = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(30))
            ->distinct('ip_address')
            ->count('ip_address');

        if ($ips > 2) {
            $anomalies[] = [
                'type' => 'multiple_ips',
                'message' => "User accessed from $ips different IPs in last 30 minutes",
                'severity' => 'high',
            ];
        }

        // Check 2: Rapid requests dari IP (potential automation)
        $rapidRequests = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($rapidRequests > 50) {
            $anomalies[] = [
                'type' => 'rapid_requests',
                'message' => "User made $rapidRequests requests in 5 minutes",
                'severity' => 'medium',
            ];
        }

        // Check 3: Geographic impossibility (too fast travel between locations)
        $lastActivities = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get(['ip_address', 'created_at']);

        if ($lastActivities->count() === 2) {
            $timeDiff = Carbon::parse($lastActivities[1]->created_at)->diffInMinutes(Carbon::parse($lastActivities[0]->created_at));
            if ($lastActivities[0]->ip_address !== $lastActivities[1]->ip_address && $timeDiff < 5) {
                $anomalies[] = [
                    'type' => 'geographic_impossibility',
                    'message' => "IP changed from {$lastActivities[1]->ip_address} to {$lastActivities[0]->ip_address} in $timeDiff minutes",
                    'severity' => 'critical',
                ];
            }
        }

        // Check 4: Failed access patterns
        $failedAccess = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->where('status_code', '>=', 400)
            ->where('status_code', '<', 500)
            ->where('created_at', '>', now()->subMinutes(30))
            ->count();

        if ($failedAccess > 5) {
            $anomalies[] = [
                'type' => 'failed_access',
                'message' => "User had $failedAccess failed access attempts in last 30 minutes",
                'severity' => 'medium',
            ];
        }

        // Persist anomalies as notifications (avoid duplicates within 30 minutes)
        if (!empty($anomalies)) {
            $userName = null;
            if ($userId) {
                $u = DB::table('users')->where('id', $userId)->first();
                $userName = $u->name ?? null;
            }

            // When an anomaly of same type exists recently, update it if details changed
            foreach ($anomalies as $anomaly) {
                try {
                    $recent = DB::table('security_notifications')
                        ->where('event_type', $anomaly['type'])
                        ->when($userId, fn($q) => $q->where('sso_user_id', $userId))
                        ->where('created_at', '>', now()->subMinutes(60))
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $newMessage = $anomaly['message'] ?? null;
                    $newDetails = is_array($anomaly) ? json_encode($anomaly) : null;
                    $newSeverity = $anomaly['severity'] ?? 'medium';

                    // severity ranking
                    $rank = ['low'=>1,'medium'=>2,'high'=>3,'critical'=>4];

                    if ($recent) {
                        $shouldUpdate = false;

                        // update if message or details changed
                        if (($recent->message ?? null) !== $newMessage) {
                            $shouldUpdate = true;
                        }

                        // update if severity increased
                        $existingRank = $rank[$recent->severity ?? 'medium'] ?? 2;
                        $incomingRank = $rank[$newSeverity] ?? 2;
                        if ($incomingRank > $existingRank) {
                            $shouldUpdate = true;
                        }

                        if ($shouldUpdate) {
                            DB::table('security_notifications')
                                ->where('id', $recent->id)
                                ->update([
                                    'severity' => $newSeverity,
                                    'title' => ucfirst(str_replace('_',' ',$anomaly['type'])),
                                    'message' => $newMessage,
                                    'details' => $newDetails,
                                    'is_read' => false,
                                    'created_at' => now(),
                                ]);
                        }
                    } else {
                        DB::table('security_notifications')->insert([
                            'sso_user_id' => $userId ?? null,
                            'user_name' => $userName,
                            'email' => null,
                            'event_type' => $anomaly['type'],
                            'severity' => $newSeverity,
                            'title' => ucfirst(str_replace('_', ' ', $anomaly['type'])),
                            'message' => $newMessage,
                            'details' => $newDetails,
                            'is_read' => false,
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to persist anomaly notification', ['error' => $e->getMessage(), 'anomaly' => $anomaly]);
                }
            }
        }

        return $anomalies;
    }

    /**
     * Get page access logs dengan filtering dan pagination.
     */
    public function getPageAccessLogs(array $filters = [], int $perPage = 20)
    {
        $query = DB::table('session_activities');

        if (isset($filters['user_id'])) {
            $query->where('sso_user_id', $filters['user_id']);
        }

        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        if (isset($filters['path'])) {
            $query->where('path', 'like', '%' . $filters['path'] . '%');
        }

        if (isset($filters['status_code'])) {
            $query->where('status_code', $filters['status_code']);
        }

        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['days'])) {
            $query->where('created_at', '>', now()->subDays($filters['days']));
        } else {
            $query->where('created_at', '>', now()->subDays(7));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get security events dengan filtering dan pagination.
     */
    public function getSecurityEvents(array $filters = [], int $perPage = 20)
    {
        $query = DB::table('security_events');

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['user_id'])) {
            $query->where('sso_user_id', $filters['user_id']);
        }

        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        if (isset($filters['days'])) {
            $query->where('created_at', '>', now()->subDays($filters['days']));
        } else {
            $query->where('created_at', '>', now()->subDays(7));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get audit logs dengan filtering dan pagination.
     */
    public function getAuditLogs(array $filters = [], int $perPage = 20)
    {
        $query = DB::table('audit_logs');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (isset($filters['result'])) {
            $query->where('result', $filters['result']);
        }

        if (isset($filters['days'])) {
            $query->where('created_at', '>', now()->subDays($filters['days']));
        } else {
            $query->where('created_at', '>', now()->subDays(7));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Send alert notification untuk critical security events.
     */
    private function sendAlert(array $eventData): void
    {
        // Log to critical channel
        Log::critical('SECURITY ALERT', [
            'event' => $eventData,
            'requires_immediate_action' => true,
        ]);

        // Persist notification to security_notifications so UI can show alerts
        try {
            DB::table('security_notifications')->insert([
                'sso_user_id' => $eventData['sso_user_id'] ?? null,
                'user_name' => $eventData['user_name'] ?? null,
                'email' => $eventData['email'] ?? null,
                'event_type' => $eventData['event_type'] ?? null,
                'severity' => $eventData['severity'] ?? 'medium',
                'title' => isset($eventData['title']) ? $eventData['title'] : (isset($eventData['event_type']) ? ucfirst(str_replace('_',' ',$eventData['event_type'])) : 'Security Alert'),
                'message' => is_string($eventData['details']) ? $eventData['details'] : (is_array($eventData['details']) ? json_encode($eventData['details']) : null),
                'details' => is_array($eventData['details']) ? json_encode($eventData['details']) : (is_string($eventData['details']) ? $eventData['details'] : null),
                'is_read' => false,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to persist security notification', [
                'error' => $e->getMessage(),
                'event' => $eventData,
            ]);
        }
    }

    /**
     * Get comprehensive security statistics.
     */
    public function getSecurityStats(int $days = 30): array
    {
        $from = now()->subDays($days);

        // try to read page access totals from summary
        $dailyRows = DB::table('security_daily_stats')
            ->where('date', '>=', $from->format('Y-m-d'))
            ->get(['requests','logins','failed','successful','denied']);

        $totalRequests = $dailyRows->sum('requests');
        $totalLogins = $dailyRows->sum('logins');
        $totalFailed = $dailyRows->sum('failed');
        $totalSuccessful = $dailyRows->sum('successful');
        $totalDenied = $dailyRows->sum('denied');

        // fallback to raw log if summary empty
        if ($dailyRows->isEmpty()) {
            $pageAccessByStatus = DB::table('session_activities')
                ->where('created_at', '>=', $from)
                ->groupBy('status_code')
                ->selectRaw('status_code, COUNT(*) as count')
                ->pluck('count', 'status_code')
                ->toArray();

            $failedAccess = DB::table('session_activities')
                ->where('status_code', '>=', 400)
                ->where('created_at', '>=', $from)
                ->count();

            $deniedAccess = DB::table('session_activities')
                ->where('status_code', '>=', 403)
                ->where('status_code', '<', 404)
                ->where('created_at', '>=', $from)
                ->count();
        } else {
            // we don't need heavy breakdowns when using summary
            $pageAccessByStatus = [];
            $failedAccess = $totalFailed;
            $deniedAccess = $totalDenied;
        }

        // Top failed paths
        $topFailedPaths = DB::table('session_activities')
            ->where('status_code', '>=', 400)
            ->where('created_at', '>=', $from)
            ->groupBy('path')
            ->selectRaw('path, COUNT(*) as attempts')
            ->orderBy('attempts', 'desc')
            ->limit(10)
            ->get();

        // Top accessing IPs
        $topIPs = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->groupBy('ip_address')
            ->selectRaw('ip_address, COUNT(*) as requests')
            ->orderBy('requests', 'desc')
            ->limit(10)
            ->get();

        // Security events summary
        $eventsBySeverity = DB::table('security_events')
            ->where('created_at', '>=', $from)
            ->groupBy('severity')
            ->selectRaw('severity, COUNT(*) as count')
            ->pluck('count', 'severity')
            ->toArray();

        $eventsByType = DB::table('security_events')
            ->where('created_at', '>=', $from)
            ->groupBy('event_type')
            ->selectRaw('event_type, COUNT(*) as count')
            ->pluck('count', 'event_type')
            ->toArray();

        return [
            'date_range' => [
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => now()->format('Y-m-d H:i:s'),
                'days' => $days,
            ],
            'page_access' => [
                'total_requests' => $totalRequests,
                'successful' => $totalSuccessful,
                'failed' => $failedAccess,
                'denied' => $deniedAccess,
                'by_status_code' => $pageAccessByStatus,
                'top_failed_paths' => $topFailedPaths,
                'top_ips' => $topIPs,
            ],
            'security_events' => [
                'total' => DB::table('security_events')
                    ->where('created_at', '>=', $from)
                    ->count(),
                'critical' => $eventsBySeverity['critical'] ?? 0,
                'high' => $eventsBySeverity['high'] ?? 0,
                'medium' => $eventsBySeverity['medium'] ?? 0,
                'low' => $eventsBySeverity['low'] ?? 0,
                'by_type' => $eventsByType,
                'by_severity' => $eventsBySeverity,
            ],
            'users' => [
                'total_active' => DB::table('session_activities')
                    ->where('created_at', '>=', $from)
                    ->distinct('sso_user_id')
                    ->count('sso_user_id'),
                'with_anomalies' => DB::table('security_events')
                    ->where('created_at', '>=', $from)
                    ->distinct('sso_user_id')
                    ->count('sso_user_id'),
            ],
            'ips' => [
                'unique_ips' => DB::table('session_activities')
                    ->where('created_at', '>=', $from)
                    ->distinct('ip_address')
                    ->count('ip_address'),
            ],
            'audit' => [
                'total_logs' => DB::table('audit_logs')
                    ->where('created_at', '>=', $from)
                    ->count(),
                'by_action' => DB::table('audit_logs')
                    ->where('created_at', '>=', $from)
                    ->groupBy('action')
                    ->selectRaw('action, COUNT(*) as count')
                    ->pluck('count', 'action')
                    ->toArray(),
                'by_result' => DB::table('audit_logs')
                    ->where('created_at', '>=', $from)
                    ->groupBy('result')
                    ->selectRaw('result, COUNT(*) as count')
                    ->pluck('count', 'result')
                    ->toArray(),
            ],
        ];
    }

    /**
     * Get daily activity trends.
     */
    public function getActivityTrends(int $days = 7): array
    {
        $from = now()->subDays($days);

        // try to use daily summary for page_access
        $activity = DB::table('security_daily_stats')
            ->where('date', '>=', $from->format('Y-m-d'))
            ->selectRaw('date as date, requests as count')
            ->orderBy('date')
            ->get();

        // fallback if summary empty
        if ($activity->isEmpty()) {
            $activity = DB::table('session_activities')
                ->where('created_at', '>=', $from)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        $events = DB::table('security_events')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $auditLogs = DB::table('audit_logs')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'page_access' => $activity,
            'security_events' => $events,
            'audit_logs' => $auditLogs,
        ];
    }

    /**
     * Get user activity summary.
     */
    public function getUserActivitySummary(int $userId, int $days = 7): array
    {
        $from = now()->subDays($days);

        return [
            'user_id' => $userId,
            'date_range' => [
                'from' => $from->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'total_requests' => DB::table('session_activities')
                ->where('sso_user_id', $userId)
                ->where('created_at', '>=', $from)
                ->count(),
            'unique_ips' => DB::table('session_activities')
                ->where('sso_user_id', $userId)
                ->where('created_at', '>=', $from)
                ->distinct('ip_address')
                ->count('ip_address'),
            'failed_requests' => DB::table('session_activities')
                ->where('sso_user_id', $userId)
                ->where('status_code', '>=', 400)
                ->where('created_at', '>=', $from)
                ->count(),
            'security_flags' => DB::table('security_events')
                ->where('sso_user_id', $userId)
                ->where('created_at', '>=', $from)
                ->count(),
            'top_paths' => DB::table('session_activities')
                ->where('sso_user_id', $userId)
                ->where('created_at', '>=', $from)
                ->groupBy('path')
                ->selectRaw('path, COUNT(*) as count')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'ip_history' => DB::table('session_activities')
                ->where('sso_user_id', $userId)
                ->where('created_at', '>=', $from)
                ->selectRaw('ip_address, MAX(created_at) as last_used')
                ->groupBy('ip_address')
                ->orderByDesc('last_used')
                ->get(),
        ];
    }
}
