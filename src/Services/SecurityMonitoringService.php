<?php

namespace Mixu\SSOAuth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SecurityMonitoringService
{
    /**
     * Check untuk anomalous login patterns (brute force, etc).
     */
    public function checkBruteForceAttempts(string $ip, int $minutes = 15, int $threshold = 3): bool
    {
        $failures = DB::table('security_events')
            ->where('event_type', 'login')
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
     * Log security event dengan automatic alerting untuk critical events.
     */
    public function logSecurityEvent(array $eventData): void
    {
        // Ensure required fields
        $eventData = array_merge([
            'event_type' => 'unknown',
            'sso_user_id' => null,
            'email' => null,
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
                'ip_address' => $eventData['ip_address'],
                'session_id' => $eventData['session_id'],
                'severity' => $eventData['severity'],
                'details' => is_array($eventData['details']) ? json_encode($eventData['details']) : $eventData['details'],
                'user_agent' => $eventData['user_agent'] ? substr($eventData['user_agent'], 0, 255) : null,
                'created_at' => now(),
            ]);
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
        // This requires IP geolocation service (MaxMind, IPStack, etc)
        // Simplified version: just log different IPs
        $lastActivities = DB::table('session_activities')
            ->where('sso_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get(['ip_address', 'created_at']);

        if ($lastActivities->count() === 2) {
            $timeDiff = $lastActivities[1]->created_at->diffInMinutes($lastActivities[0]->created_at);
            if ($lastActivities[0]->ip_address !== $lastActivities[1]->ip_address && $timeDiff < 5) {
                $anomalies[] = [
                    'type' => 'geographic_impossibility',
                    'message' => "IP changed from {$lastActivities[1]->ip_address} to {$lastActivities[0]->ip_address} in $timeDiff minutes",
                    'severity' => 'critical',
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Send alert notification untuk critical security events.
     */
    private function sendAlert(array $eventData): void
    {
        // TODO: Integrate dengan Slack, Email, atau notification service
        // Example untuk Slack:
        /*
        Notification::route('slack', config('services.slack.security_alerts'))
            ->notify(new SecurityAlert($eventData));
        */

        Log::critical('SECURITY ALERT', [
            'event' => $eventData,
            'requires_immediate_action' => true,
        ]);
    }

    /**
     * Get security statistics untuk dashboard/reporting.
     */
    public function getSecurityStats(int $days = 30): array
    {
        $from = now()->subDays($days);

        return [
            'total_logins' => DB::table('security_events')
                ->where('event_type', 'login')
                ->where('created_at', '>=', $from)
                ->count(),

            'total_logouts' => DB::table('security_events')
                ->where('event_type', 'logout')
                ->where('created_at', '>=', $from)
                ->count(),

            'failed_logins' => DB::table('security_events')
                ->where('event_type', 'auth_failure')
                ->where('created_at', '>=', $from)
                ->count(),

            'ip_mismatches' => DB::table('security_events')
                ->where('event_type', 'ip_mismatch')
                ->where('created_at', '>=', $from)
                ->count(),

            'critical_events' => DB::table('security_events')
                ->where('severity', 'critical')
                ->where('created_at', '>=', $from)
                ->count(),

            'unique_ips' => DB::table('session_activities')
                ->where('created_at', '>=', $from)
                ->distinct('ip_address')
                ->count('ip_address'),

            'unique_users' => DB::table('session_activities')
                ->where('created_at', '>=', $from)
                ->distinct('sso_user_id')
                ->count('sso_user_id'),

            'by_event_type' => DB::table('security_events')
                ->where('created_at', '>=', $from)
                ->groupBy('event_type')
                ->selectRaw('event_type, COUNT(*) as count')
                ->pluck('count', 'event_type')
                ->toArray(),

            'by_severity' => DB::table('security_events')
                ->where('created_at', '>=', $from)
                ->groupBy('severity')
                ->selectRaw('severity, COUNT(*) as count')
                ->pluck('count', 'severity')
                ->toArray(),
        ];
    }
}
