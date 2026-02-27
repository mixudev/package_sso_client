<?php

namespace Mixu\SSOAuth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateSecurityStats extends Command
{
    protected $signature = 'security:stats {--days=30 : Number of past days to recalculate}';
    protected $description = 'Recalculate security daily/hourly summary statistics';

    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Rebuilding security stats for last {$days} days...");

        $from = now()->subDays($days);

        // daily aggregates
        $dailyRequests = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as requests')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyFailed = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->where('status_code', '>=', 400)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as failed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // successful (<400) and denied (403 only)
        $dailySuccessful = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->where('status_code', '<', 400)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as successful')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyDenied = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->where('status_code', '>=', 403)
            ->where('status_code', '<', 404)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as denied')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyLogins = DB::table('security_events')
            ->where('event_type', 'login')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as logins')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->info('Updating daily stats');
        foreach ($dailyRequests as $row) {
            DB::table('security_daily_stats')->updateOrInsert(
                ['date' => $row->date],
                [
                    'requests' => $row->requests,
                    'logins' => $dailyLogins->firstWhere('date', $row->date)->logins ?? 0,
                    'failed' => $dailyFailed->firstWhere('date', $row->date)->failed ?? 0,
                    'successful' => $dailySuccessful->firstWhere('date', $row->date)->successful ?? 0,
                    'denied' => $dailyDenied->firstWhere('date', $row->date)->denied ?? 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        // ensure any days with only logins, failed, successful or denied also exist
        foreach ($dailyLogins as $row) {
            DB::table('security_daily_stats')->updateOrInsert(
                ['date' => $row->date],
                ['logins' => $row->logins, 'updated_at' => now(), 'created_at' => now()],
            );
        }
        foreach ($dailyFailed as $row) {
            DB::table('security_daily_stats')->updateOrInsert(
                ['date' => $row->date],
                ['failed' => $row->failed, 'updated_at' => now(), 'created_at' => now()],
            );
        }
        foreach ($dailySuccessful as $row) {
            DB::table('security_daily_stats')->updateOrInsert(
                ['date' => $row->date],
                ['successful' => $row->successful, 'updated_at' => now(), 'created_at' => now()],
            );
        }
        foreach ($dailyDenied as $row) {
            DB::table('security_daily_stats')->updateOrInsert(
                ['date' => $row->date],
                ['denied' => $row->denied, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        // hourly aggregates for last 1 day (could extend if needed)
        $this->info('Updating hourly stats');
        $hourlyRequests = DB::table('session_activities')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, HOUR(created_at) as hour, COUNT(*) as requests')
            ->groupBy('date','hour')
            ->orderBy('date')->orderBy('hour')
            ->get();

        $hourlyLogins = DB::table('security_events')
            ->where('event_type', 'login')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, HOUR(created_at) as hour, COUNT(*) as logins')
            ->groupBy('date','hour')
            ->orderBy('date')->orderBy('hour')
            ->get();

        foreach ($hourlyRequests as $row) {
            DB::table('security_hourly_stats')->updateOrInsert(
                ['date' => $row->date, 'hour' => $row->hour],
                ['requests' => $row->requests, 'logins' => $hourlyLogins->firstWhere('date', $row->date)->hour == $row->hour ? $hourlyLogins->firstWhere('date', $row->date)->logins : 0, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        // ensure rows with only logins or requests present
        foreach ($hourlyLogins as $row) {
            DB::table('security_hourly_stats')->updateOrInsert(
                ['date' => $row->date, 'hour' => $row->hour],
                ['logins' => $row->logins, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        // clear caches for affected ranges
        for ($i = 1; $i <= $days; $i++) {
            Cache::forget("security.stats.{$i}");
            Cache::forget("security.trends.{$i}");
            Cache::forget("security.topPages.{$i}");
        }
        Cache::forget("security.dailyStats.{$days}");
        // hourly key uses today's date
        $todayStart = now()->startOfDay();
        Cache::forget("security.hourlyActivity.{$todayStart->format('Y-m-d')}");

        $this->info('Security stats rebuild complete. Caches cleared.');
    }
}
