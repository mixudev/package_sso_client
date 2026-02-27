<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Mixu\SSOAuth\Services\SecurityMonitoringService;

class SecurityController extends Controller
{
    public function __construct(
        private SecurityMonitoringService $security
    ) {}

    /**
     * Display security dashboard dengan overview.
     */
    public function dashboard(Request $request)
    {
        $days = $request->query('days', 30);
        $from = now()->subDays($days); // used for queries below
        
        // Get stats untuk periode tertentu (cache 5 menit)
        $stats = Cache::remember("security.stats.{$days}", now()->addMinutes(5), fn() => $this->security->getSecurityStats($days));
        
        // Get activity trends (also cached)
        $trends = Cache::remember("security.trends.{$days}", now()->addMinutes(5), fn() => $this->security->getActivityTrends($days));
        
        // Detect anomalies untuk user saat ini
        $userId = $request->session()->get('sso_user.id');
        $anomalies = $userId ? $this->security->detectAnomalies($userId) : [];

        // Get current user activity summary
        $userActivity = $userId ? $this->security->getUserActivitySummary($userId, 7) : null;

        // Get daily stats untuk Traffic Trend Chart
        $cacheKey = "security.dailyStats.{$days}";
        $dailyStats = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($days) {
            $from = now()->subDays($days);

            // attempt to read from summary table
            $rows = DB::table('security_daily_stats')
                ->where('date', '>=', $from->format('Y-m-d'))
                ->orderBy('date')
                ->get(['date','requests','logins','failed'])
                ->keyBy('date');

            if ($rows->isEmpty()) {
                // fallback to expensive calculation if summary missing
                $requestsByDay = DB::table('session_activities')
                    ->where('created_at', '>=', $from)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as requests')
                    ->groupBy('date')
                    ->pluck('requests', 'date');

                $loginsByDay = DB::table('security_events')
                    ->where('event_type', 'login')
                    ->where('created_at', '>=', $from)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as logins')
                    ->groupBy('date')
                    ->pluck('logins', 'date');

                $failedByDay = DB::table('session_activities')
                    ->where('created_at', '>=', $from)
                    ->where('status_code', '>=', 400)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as failed')
                    ->groupBy('date')
                    ->pluck('failed', 'date');

                $stats = [];
                for ($i = 0; $i < $days; $i++) {
                    $date = now()->subDays($days - 1 - $i)->format('Y-m-d');
                    $stats[] = [
                        'date' => $date,
                        'requests' => $requestsByDay[$date] ?? 0,
                        'logins' => $loginsByDay[$date] ?? 0,
                        'failed' => $failedByDay[$date] ?? 0,
                    ];
                }

                return $stats;
            }

            // convert summary rows into full list for the range
            $stats = [];
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($days - 1 - $i)->format('Y-m-d');
                $entry = $rows->get($date);
                $stats[] = [
                    'date' => $date,
                    'requests' => $entry->requests ?? 0,
                    'logins' => $entry->logins ?? 0,
                    'failed' => $entry->failed ?? 0,
                ];
            }
            return $stats;
        });

        // Get top accessed pages (cached briefly)
        $topPages = \Illuminate\Support\Facades\Cache::remember("security.topPages.{$days}", now()->addMinutes(5), function () use ($from) {
            return DB::table('session_activities')
                ->where('created_at', '>=', $from)
                ->where('status_code', '<', 400)
                ->groupBy('path')
                ->selectRaw('path, COUNT(*) as hits, COUNT(DISTINCT sso_user_id) as users')
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($item) => [
                    'path' => $item->path,
                    'hits' => $item->hits ?? 0,
                    'requests' => $item->hits ?? 0,
                    'users' => $item->users ?? 0,
                ])
                ->toArray();
        });

        // Get hourly activity untuk hari ini (cached 5min)
        $todayStart = now()->startOfDay();
        $hourlyActivity = \Illuminate\Support\Facades\Cache::remember("security.hourlyActivity.{$todayStart->format('Y-m-d')}", now()->addMinutes(5), function () use ($todayStart) {
            // read from summary table first
            $rows = DB::table('security_hourly_stats')
                ->where('date', '=', $todayStart->format('Y-m-d'))
                ->orderBy('hour')
                ->get(['hour','requests','logins'])
                ->keyBy('hour');

            $stats = [];
            for ($h = 0; $h < 24; $h++) {
                $entry = $rows->get($h);
                if ($entry) {
                    $stats[] = ['hour'=>$h,'logins'=>$entry->logins,'requests'=>$entry->requests];
                } else {
                    $stats[] = ['hour'=>$h,'logins'=>0,'requests'=>0];
                }
            }
            // fallback: if summary empty, compute old way
            $totalRequests = DB::table('session_activities')
                ->where('created_at', '>=', $todayStart)
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as requests')
                ->groupBy('hour')
                ->pluck('requests','hour');
            $logins = DB::table('security_events')
                ->where('event_type','login')
                ->where('created_at','>=',$todayStart)
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as logins')
                ->groupBy('hour')
                ->pluck('logins','hour');
            // only replace if rows were all zero
            if (collect($stats)->sum('requests') === 0 && collect($stats)->sum('logins') === 0) {
                $stats = [];
                for ($h = 0; $h < 24; $h++) {
                    $stats[] = [
                        'hour' => $h,
                        'logins' => $logins[$h] ?? 0,
                        'requests' => $totalRequests[$h] ?? 0,
                    ];
                }
            }
            return $stats;
        });

        // Prepare severity data
        $sevData = [
            'critical' => $stats['security_events']['by_severity']['critical'] ?? 0,
            'high' => $stats['security_events']['by_severity']['high'] ?? 0,
            'medium' => $stats['security_events']['by_severity']['medium'] ?? 0,
            'low' => $stats['security_events']['by_severity']['low'] ?? 0,
        ];

        return view('mixu-sso-auth::security.dashboard', [
            'stats' => $stats,
            'trends' => $trends,
            'anomalies' => $anomalies,
            'userActivity' => $userActivity,
            'selectedDays' => $days,
            'dailyStats' => $dailyStats,
            'topPages' => $topPages,
            'hourlyActivity' => $hourlyActivity,
            'sevData' => $sevData,
        ]);
    }

    /**
     * Display page access logs.
     */
    public function pageAccessLogs(Request $request)
    {
        $filters = [
            'user_id' => $request->query('user_id'),
            'ip_address' => $request->query('ip_address'),
            'path' => $request->query('path'),
            'status_code' => $request->query('status_code'),
            'method' => $request->query('method'),
            'days' => $request->query('days', 7),
        ];

        $logs = $this->security->getPageAccessLogs(array_filter($filters), 50);

        return view('mixu-sso-auth::security.page-access-logs', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    /**
     * Display security events.
     */
    public function securityEvents(Request $request)
    {
        $filters = [
            'event_type' => $request->query('event_type'),
            'severity' => $request->query('severity'),
            'user_id' => $request->query('user_id'),
            'ip_address' => $request->query('ip_address'),
            'days' => $request->query('days', 7),
        ];

        $events = $this->security->getSecurityEvents(array_filter($filters), 50);

        return view('mixu-sso-auth::security.security-events', [
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    /**
     * Display audit logs.
     */
    public function auditLogs(Request $request)
    {
        $filters = [
            'user_id' => $request->query('user_id'),
            'action' => $request->query('action'),
            'entity_type' => $request->query('entity_type'),
            'result' => $request->query('result'),
            'days' => $request->query('days', 7),
        ];

        $logs = $this->security->getAuditLogs(array_filter($filters), 50);

        return view('mixu-sso-auth::security.audit-logs', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    /**
     * Display user activity details.
     */
    public function userActivity(Request $request)
    {
        $userId = $request->query('user_id');
        $days = $request->query('days', 7);

        if (!$userId) {
            return redirect()->route('security.dashboard')->with('error', 'User ID required');
        }

        $activity = $this->security->getUserActivitySummary($userId, $days);
        $pageAccessLogs = $this->security->getPageAccessLogs(['user_id' => $userId, 'days' => $days], 20);
        $anomalies = $this->security->detectAnomalies($userId);

        return view('mixu-sso-auth::security.user-activity', [
            'activity' => $activity,
            'pageAccessLogs' => $pageAccessLogs,
            'anomalies' => $anomalies,
        ]);
    }

    /**
     * Check brute force attempts.
     */
    public function checkBruteForce(Request $request)
    {
        $ip = $request->ip();
        $isBruteForce = $this->security->checkBruteForceAttempts($ip, 15, 3);

        return response()->json([
            'ip' => $ip,
            'is_brute_force' => $isBruteForce,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Export security logs (CSV).
     */
    public function exportLogs(Request $request)
    {
        $type = $request->query('type', 'page_access'); // page_access, security_events, audit_logs
        $days = $request->query('days', 30);

        $filename = 'security-logs-' . $type . '-' . now()->format('Y-m-d-His') . '.csv';

        return response()->stream(function () use ($type, $days) {
            $output = fopen('php://output', 'w');

            if ($type === 'page_access') {
                $this->exportPageAccessLogs($output, $days);
            } elseif ($type === 'security_events') {
                $this->exportSecurityEvents($output, $days);
            } elseif ($type === 'audit_logs') {
                $this->exportAuditLogs($output, $days);
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export page access logs to CSV.
     */
    private function exportPageAccessLogs($output, int $days): void
    {
        // Use cursor to stream data
        fputcsv($output, ['Timestamp', 'User ID', 'Session ID', 'IP Address', 'Method', 'Path', 'Status Code', 'User Agent']);

        $query = DB::table('session_activities')->where('created_at', '>=', now()->subDays($days));
        $query->orderBy('id');

        foreach ($query->cursor() as $log) {
            fputcsv($output, [
                $log->created_at,
                $log->sso_user_id,
                $log->session_id,
                $log->ip_address,
                $log->method,
                $log->path,
                $log->status_code,
                $log->user_agent,
            ]);
        }
    }

    /**
     * Export security events to CSV.
     */
    private function exportSecurityEvents($output, int $days): void
    {
        fputcsv($output, ['Timestamp', 'Event Type', 'User ID', 'Email', 'IP Address', 'Severity', 'Details']);

        $query = DB::table('security_events')->where('created_at', '>=', now()->subDays($days));
        $query->orderBy('id');
        foreach ($query->cursor() as $event) {
            fputcsv($output, [
                $event->created_at,
                $event->event_type,
                $event->sso_user_id,
                $event->email,
                $event->ip_address,
                $event->severity,
                $event->details,
            ]);
        }
    }

    /**
     * Export audit logs to CSV.
     */
    private function exportAuditLogs($output, int $days): void
    {
        fputcsv($output, ['Timestamp', 'User ID', 'Action', 'Entity Type', 'Entity ID', 'IP Address', 'Result', 'Details']);

        $query = DB::table('audit_logs')->where('created_at', '>=', now()->subDays($days));
        $query->orderBy('id');
        foreach ($query->cursor() as $log) {
            fputcsv($output, [
                $log->created_at,
                $log->user_id,
                $log->action,
                $log->entity_type,
                $log->entity_id,
                $log->ip_address,
                $log->result,
                $log->details,
            ]);
        }
    }
}