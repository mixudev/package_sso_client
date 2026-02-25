<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        
        // Get stats untuk periode tertentu
        $stats = $this->security->getSecurityStats($days);
        
        // Get activity trends
        $trends = $this->security->getActivityTrends(7);
        
        // Detect anomalies untuk user saat ini
        $userId = $request->session()->get('sso_user.id');
        $anomalies = $userId ? $this->security->detectAnomalies($userId) : [];

        // Get current user activity summary
        $userActivity = $userId ? $this->security->getUserActivitySummary($userId, 7) : null;

        return view('mixu-sso-auth::security.dashboard', [
            'stats' => $stats,
            'trends' => $trends,
            'anomalies' => $anomalies,
            'userActivity' => $userActivity,
            'selectedDays' => $days,
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
        $logs = $this->security->getPageAccessLogs(['days' => $days], 10000);

        // Header
        fputcsv($output, ['Timestamp', 'User ID', 'Session ID', 'IP Address', 'Method', 'Path', 'Status Code', 'User Agent']);

        foreach ($logs as $log) {
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
        $events = $this->security->getSecurityEvents(['days' => $days], 10000);

        // Header
        fputcsv($output, ['Timestamp', 'Event Type', 'User ID', 'Email', 'IP Address', 'Severity', 'Details']);

        foreach ($events as $event) {
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
        $logs = $this->security->getAuditLogs(['days' => $days], 10000);

        // Header
        fputcsv($output, ['Timestamp', 'User ID', 'Action', 'Entity Type', 'Entity ID', 'IP Address', 'Result', 'Details']);

        foreach ($logs as $log) {
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