@extends('mixu-sso-auth::layouts.app')

@section('title', 'Dashboard')

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
    .chart-container { position: relative; }
    .progress-fill { transition: width 0.8s cubic-bezier(.4,0,.2,1); }
    .animate-pulse-soft { animation: pulse-soft 2s infinite; }
    @keyframes pulse-soft { 0%,100%{opacity:1} 50%{opacity:.6} }
</style>
@endpush

@section('content')

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Security &amp; Audit Monitoring</h1>
        <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">Real-time security monitoring with comprehensive audit trail</p>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
        @foreach([['7','7D'],['30','30D'],['60','60D'],['90','90D']] as [$val,$label])
        <a href="?days={{ $val }}"
           class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all no-underline
                  {{ $selectedDays == $val
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/30'
                    : 'dark:bg-slate-800 bg-slate-100 dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 border dark:border-slate-700 border-slate-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

@if(!empty($anomalies))
<div class="rounded-xl dark:bg-red-950/40 bg-red-50 border dark:border-red-900/50 border-red-200 p-4 mb-6">
    <div class="flex items-center gap-2.5 mb-3">
        <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-red-400">{{ count($anomalies) }} Security Anomal{{ count($anomalies) > 1 ? 'ies' : 'y' }} Detected</span>
    </div>
    <div class="flex flex-col gap-2">
        @foreach($anomalies as $anomaly)
        <div class="flex items-start gap-3 dark:bg-red-950/50 bg-red-100/70 rounded-lg px-3 py-2.5">
            <span class="badge badge-danger flex-shrink-0 mt-0.5">{{ strtoupper($anomaly['severity']) }}</span>
            <div>
                <div class="text-sm font-semibold dark:text-red-200 text-red-700 mb-0.5">{{ ucfirst(str_replace('_',' ',$anomaly['type'])) }}</div>
                <div class="text-xs dark:text-red-400/80 text-red-600/80">{{ $anomaly['message'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Metrics Row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
    @php
        $colorMap = [
            'blue'    => ['icon_bg'=>'bg-blue-500/10',    'icon_txt'=>'text-blue-400',    'val_txt'=>'dark:text-blue-400 text-blue-600',    'border'=>'dark:border-blue-500/10 border-blue-100'],
            'orange'  => ['icon_bg'=>'bg-orange-500/10',  'icon_txt'=>'text-orange-400',  'val_txt'=>'dark:text-orange-400 text-orange-600',  'border'=>'dark:border-orange-500/10 border-orange-100'],
            'emerald' => ['icon_bg'=>'bg-emerald-500/10', 'icon_txt'=>'text-emerald-400', 'val_txt'=>'dark:text-emerald-400 text-emerald-600', 'border'=>'dark:border-emerald-500/10 border-emerald-100'],
            'purple'  => ['icon_bg'=>'bg-purple-500/10',  'icon_txt'=>'text-purple-400',  'val_txt'=>'dark:text-purple-400 text-purple-600',  'border'=>'dark:border-purple-500/10 border-purple-100'],
        ];
        $metrics = [
            ['label'=>'Total Requests',    'value'=>number_format($stats['page_access']['total_requests'] ?? 0), 'color'=>'blue',    'sub'=>'<span class="text-emerald-400 font-medium">'.number_format($stats['page_access']['successful'] ?? 0).' ok</span> <span class="dark:text-slate-700 text-slate-300">·</span> <span class="text-red-400">'.number_format($stats['page_access']['failed'] ?? 0).' fail</span>', 'icon'=>'<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
            ['label'=>'Security Events',   'value'=>number_format($stats['security_events']['total'] ?? 0),    'color'=>'orange',  'sub'=>'<span class="text-red-400">'.($stats['security_events']['critical'] ?? 0).' critical</span> <span class="dark:text-slate-700 text-slate-300">·</span> <span class="text-orange-400">'.($stats['security_events']['high'] ?? 0).' high</span>', 'icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
            ['label'=>'Active Users',      'value'=>number_format($stats['users']['total_active'] ?? 0),       'color'=>'emerald', 'sub'=>'<span>'.number_format($stats['users']['with_anomalies'] ?? 0).' with anomalies</span>', 'icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label'=>'Unique IPs',        'value'=>number_format($stats['ips']['unique_ips'] ?? 0),           'color'=>'purple',  'sub'=>'<span>Audit: '.number_format($stats['audit']['total_logs'] ?? 0).' logs</span>', 'icon'=>'<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
        ];
    @endphp

    @foreach($metrics as $m)
    @php $c = $colorMap[$m['color']]; @endphp
    <div class="rounded-xl dark:bg-slate-900 bg-white border {{ $c['border'] }} p-4 sm:p-5 transition-all dark:hover:bg-slate-800/60 hover:shadow-md">
        <div class="flex items-start justify-between gap-2 mb-2">
            <div class="min-w-0">
                <div class="dark:text-slate-500 text-slate-400 font-medium uppercase mb-1" style="font-size:0.62rem;letter-spacing:0.08em;">{{ $m['label'] }}</div>
                <div class="text-2xl sm:text-3xl font-bold {{ $c['val_txt'] }} font-mono tabular-nums">{{ $m['value'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl {{ $c['icon_bg'] }} flex items-center justify-center flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $c['icon_txt'] }}">{!! $m['icon'] !!}</svg>
            </div>
        </div>
        <div class="text-xs dark:text-slate-500 text-slate-400 truncate">{!! $m['sub'] !!}</div>
    </div>
    @endforeach
</div>

{{-- ============================================================
     CHART ROW 1 — Traffic Trend (full width)
     Data: $dailyStats = [['date'=>'2025-01-01','requests'=>120,'failed'=>5,'logins'=>30], ...]
     ============================================================ --}}
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5 mb-5 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Traffic & Login Trend</h3>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-1 rounded-full bg-blue-500"></span><span class="dark:text-slate-400 text-slate-500">Requests</span></span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-1 rounded-full bg-emerald-500"></span><span class="dark:text-slate-400 text-slate-500">Logins</span></span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-1 rounded-full bg-red-500"></span><span class="dark:text-slate-400 text-slate-500">Failed</span></span>
        </div>
    </div>
    <div class="chart-container" style="height:220px;">
        <canvas id="trafficTrendChart"></canvas>
    </div>
</div>

{{-- ============================================================
     CHART ROW 2 — Access Status (Doughnut) + Event Severity (Bar)
     ============================================================ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5 sm:mb-6">

    <!-- Access Status Breakdown — Doughnut Chart -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Access Status Breakdown</h3>
        </div>
        @php
            $total    = max($stats['page_access']['total_requests'] ?? 1, 1);
            $success  = $stats['page_access']['successful'] ?? 0;
            $denied   = $stats['page_access']['denied'] ?? 0;
            $failed   = $stats['page_access']['failed'] ?? 0;
        @endphp
        <div class="flex items-center gap-4">
            <div class="chart-container flex-shrink-0" style="width:160px;height:160px;">
                <canvas id="accessStatusChart"></canvas>
            </div>
            <div class="flex flex-col gap-3 min-w-0 flex-1">
                @foreach([
                    ['label'=>'Successful (2xx–3xx)', 'val'=>$success, 'pct'=>round($success/$total*100), 'dot'=>'bg-emerald-500', 'txt'=>'text-emerald-400'],
                    ['label'=>'Denied (403)',          'val'=>$denied,  'pct'=>round($denied/$total*100),  'dot'=>'bg-orange-500',  'txt'=>'text-orange-400'],
                    ['label'=>'Failed (4xx–5xx)',      'val'=>$failed,  'pct'=>round($failed/$total*100),  'dot'=>'bg-red-500',     'txt'=>'text-red-400'],
                ] as $item)
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-sm {{ $item['dot'] }} flex-shrink-0"></span>
                        <span class="text-xs dark:text-slate-400 text-slate-500 truncate">{{ $item['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="font-mono text-xs {{ $item['txt'] }} font-bold">{{ $item['pct'] }}%</span>
                        <span class="font-mono text-xs dark:text-slate-500 text-slate-400">{{ number_format($item['val']) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Event Severity Distribution — Horizontal Bar Chart -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Event Severity Distribution</h3>
        </div>
        @php
            $sevData = [
                'critical' => $stats['security_events']['by_severity']['critical'] ?? 0,
                'high'     => $stats['security_events']['by_severity']['high']     ?? 0,
                'medium'   => $stats['security_events']['by_severity']['medium']   ?? 0,
                'low'      => $stats['security_events']['by_severity']['low']      ?? 0,
            ];
        @endphp
        <div class="chart-container" style="height:160px;">
            <canvas id="severityChart"></canvas>
        </div>
        <div class="flex items-center justify-center gap-4 mt-3 flex-wrap">
            @foreach(['critical'=>['text-red-400','Critical'],'high'=>['text-orange-400','High'],'medium'=>['text-amber-400','Medium'],'low'=>['text-blue-400','Low']] as $key=>[$cls,$lbl])
            <span class="flex items-center gap-1.5 text-xs">
                <span class="w-2 h-2 rounded-sm {{ $key === 'critical' ? 'bg-red-500' : ($key === 'high' ? 'bg-orange-500' : ($key === 'medium' ? 'bg-amber-500' : 'bg-blue-500')) }}"></span>
                <span class="dark:text-slate-400 text-slate-500">{{ $lbl }}: <span class="{{ $cls }} font-semibold font-mono">{{ $sevData[$key] }}</span></span>
            </span>
            @endforeach
        </div>
    </div>
</div>

{{-- ============================================================
     CHART ROW 3 — Top Pages (Bar) + Hourly Activity Today (Line)
     Data: $topPages = [['path'=>'/dashboard','hits'=>450], ...]
           $hourlyActivity = [['hour'=>0,'logins'=>2,'requests'=>10], ...]
     ============================================================ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5 sm:mb-6">

    <!-- Top Accessed Pages -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Top Accessed Pages</h3>
        </div>
        <div class="chart-container" style="height:200px;">
            <canvas id="topPagesChart"></canvas>
        </div>
    </div>

    <!-- Hourly Activity Today -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Hourly Activity Today</h3>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-1 rounded-full bg-emerald-500"></span><span class="dark:text-slate-400 text-slate-500">Logins</span></span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-1 rounded-full bg-purple-500"></span><span class="dark:text-slate-400 text-slate-500">Activity</span></span>
            </div>
        </div>
        <div class="chart-container" style="height:200px;">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>
</div>

<!-- Lists Row -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5 sm:mb-6">

    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-3 sm:mb-4">
            <div class="w-7 h-7 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Top Failed Access Paths</h3>
        </div>
        @if(!empty($stats['page_access']['top_failed_paths']))
        <div class="flex flex-col gap-1.5">
            @foreach($stats['page_access']['top_failed_paths'] as $item)
            <div class="flex items-center justify-between px-3 py-2 rounded-lg dark:bg-slate-800/60 bg-slate-50 border dark:border-slate-700/50 border-slate-200 gap-2">
                <span class="font-mono text-xs dark:text-slate-400 text-slate-500 truncate-path">{{ $item->path }}</span>
                <span class="badge badge-danger flex-shrink-0">{{ $item->attempts }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs dark:text-slate-600 text-slate-400 py-4 text-center">No failed access attempts in this period</p>
        @endif
    </div>

    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-3 sm:mb-4">
            <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400">
                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Top Accessing IPs</h3>
        </div>
        @if(!empty($stats['page_access']['top_ips']))
        <div class="flex flex-col gap-1.5">
            @foreach($stats['page_access']['top_ips'] as $item)
            <div class="flex items-center justify-between px-3 py-2 rounded-lg dark:bg-slate-800/60 bg-slate-50 border dark:border-slate-700/50 border-slate-200 gap-2">
                <span class="font-mono text-xs dark:text-slate-400 text-slate-500">{{ $item->ip_address }}</span>
                <span class="badge badge-blue flex-shrink-0">{{ $item->requests }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs dark:text-slate-600 text-slate-400 py-4 text-center">No activity in this period</p>
        @endif
    </div>
</div>

<!-- Navigation Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    @php
        $navCards = [
            ['route'=>'security.page-access', 'title'=>'Page Access Logs', 'desc'=>'All access attempts',    'bg'=>'bg-blue-500/10',    'txt'=>'text-blue-400',    'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
            ['route'=>'security.events',      'title'=>'Security Events',  'desc'=>'Threats & flags',        'bg'=>'bg-orange-500/10',  'txt'=>'text-orange-400',  'icon'=>'<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
            ['route'=>'security.audit',       'title'=>'Audit Trail',      'desc'=>'User actions',           'bg'=>'bg-emerald-500/10', 'txt'=>'text-emerald-400', 'icon'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
            ['route'=>'security.export-logs', 'title'=>'Export Logs',      'desc'=>'Download as CSV',        'bg'=>'bg-purple-500/10',  'txt'=>'text-purple-400',  'icon'=>'<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>'],
        ];
    @endphp
    @foreach($navCards as $card)
    <a href="{{ route($card['route']) }}"
       class="group rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3 no-underline
              dark:hover:bg-slate-800 hover:bg-slate-50 dark:hover:border-slate-700 hover:border-slate-300 hover:shadow-md transition-all duration-200">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $card['bg'] }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $card['txt'] }}">{!! $card['icon'] !!}</svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm font-semibold dark:text-slate-200 text-slate-700 group-hover:dark:text-white transition-colors">{{ $card['title'] }}</div>
            <div class="text-xs dark:text-slate-500 text-slate-400 mt-0.5">{{ $card['desc'] }}</div>
        </div>
    </a>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
// ─── Initialize Charts with proper error handling ──────────────────────────
function initCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        setTimeout(initCharts, 200);
        return;
    }

    try {
        // Dark/Light mode detection
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor   = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';
        const tickColor   = isDark ? '#64748b' : '#94a3b8';
        const tooltipBg   = isDark ? '#1e293b' : '#fff';
        const tooltipBorder = isDark ? '#334155' : '#e2e8f0';
        const tooltipTxt  = isDark ? '#e2e8f0' : '#1e293b';

        Chart.defaults.font.family = 'ui-monospace, SFMono-Regular, Menlo, monospace';
        Chart.defaults.color = tickColor;

        const tooltipStyle = {
            backgroundColor: tooltipBg,
            borderColor: tooltipBorder,
            borderWidth: 1,
            titleColor: tooltipTxt,
            bodyColor: tickColor,
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 4,
        };

        const gridStyle = {
            color: gridColor,
            drawBorder: false,
        };

        // ─── 1. Traffic Trend Chart ───────────────────────────────────────
        const dailyData = @json($dailyStats ?? []);
        console.log('Traffic data loaded:', dailyData);

        if (dailyData.length > 0 && document.getElementById('trafficTrendChart')) {
            const trendLabels   = dailyData.map(d => d.date);
            const trendRequests = dailyData.map(d => d.requests ?? 0);
            const trendLogins   = dailyData.map(d => d.logins ?? 0);
            const trendFailed   = dailyData.map(d => d.failed ?? 0);

            new Chart(document.getElementById('trafficTrendChart'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Requests',
                            data: trendRequests,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.10)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: trendLabels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                        },
                        {
                            label: 'Logins',
                            data: trendLogins,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: trendLabels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                        },
                        {
                            label: 'Failed',
                            data: trendFailed,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: trendLabels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: tooltipStyle },
                    scales: {
                        x: { grid: gridStyle, ticks: { maxTicksLimit: 10, color: tickColor, font:{size:10} } },
                        y: { grid: gridStyle, ticks: { color: tickColor, font:{size:10} }, beginAtZero: true },
                    },
                }
            });
        }

        // ─── 2. Access Status Doughnut ────────────────────────────────────
        if (document.getElementById('accessStatusChart')) {
            const successVal = {{ $stats['page_access']['successful'] ?? 0 }};
            const deniedVal  = {{ $stats['page_access']['denied']     ?? 0 }};
            const failedVal  = {{ $stats['page_access']['failed']     ?? 0 }};

            new Chart(document.getElementById('accessStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Successful', 'Denied', 'Failed'],
                    datasets: [{
                        data: [successVal, deniedVal, failedVal],
                        backgroundColor: ['#10b981','#f97316','#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltipStyle, callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${Math.round(ctx.parsed / Math.max(successVal+deniedVal+failedVal,1) * 100)}%)`
                        }}
                    },
                }
            });
        }

        // ─── 3. Severity Distribution Bar Chart ─────────────────────────────
        if (document.getElementById('severityChart')) {
            // sevData from PHP below

            new Chart(document.getElementById('severityChart'), {
                type: 'bar',
                data: {
                    labels: ['Critical','High','Medium','Low'],
                    datasets: [{
                        label: 'Events',
                        data: [
                            {{ $stats["security_events"]["by_severity"]["critical"] ?? 0 }},
                            {{ $stats["security_events"]["by_severity"]["high"] ?? 0 }},
                            {{ $stats["security_events"]["by_severity"]["medium"] ?? 0 }},
                            {{ $stats["security_events"]["by_severity"]["low"] ?? 0 }},
                        ],
                        backgroundColor: ['rgba(239,68,68,0.85)','rgba(249,115,22,0.85)','rgba(245,158,11,0.85)','rgba(59,130,246,0.85)'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false }, tooltip: tooltipStyle },
                    scales: {
                        x: { grid: gridStyle, ticks: { color: tickColor, font:{size:10} }, beginAtZero: true },
                        y: { grid: { display: false }, ticks: { color: tickColor, font:{size:11} } },
                    },
                }
            });
        }

        // ─── 4. Top Pages Bar Chart ──────────────────────────────────────
        if (document.getElementById('topPagesChart')) {
            const topPages = @json($topPages ?? []);
            console.log('Top pages data loaded:', topPages);

            if (topPages.length > 0) {
                new Chart(document.getElementById('topPagesChart'), {
                    type: 'bar',
                    data: {
                        labels: topPages.map(p => p.path.length > 25 ? p.path.substring(0,24)+'…' : p.path),
                        datasets: [{
                            label: 'Hits',
                            data: topPages.map(p => p.hits ?? p.requests ?? 0),
                            backgroundColor: 'rgba(139,92,246,0.75)',
                            borderRadius: 5,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false }, tooltip: { ...tooltipStyle, callbacks: {
                            title: (items) => {
                                const idx = items[0].dataIndex;
                                return topPages[idx]?.path ?? items[0].label;
                            }
                        }}},
                        scales: {
                            x: { grid: gridStyle, ticks: { color: tickColor, font:{size:10} }, beginAtZero: true },
                            y: { grid: { display: false }, ticks: { color: tickColor, font:{size:10} } },
                        },
                    }
                });
            }
        }

        // ─── 5. Hourly Activity Chart ────────────────────────────────────
        if (document.getElementById('hourlyChart')) {
            const hourly = @json($hourlyActivity ?? []);
            console.log('hourlyActivity:', hourly);

            const hours  = Array.from({length:24}, (_,i) => `${String(i).padStart(2,'0')}:00`);
            const loginsByHour   = hours.map((_,i) => hourly.find(h=>h.hour==i)?.logins   ?? 0);
            const requestsByHour = hours.map((_,i) => hourly.find(h=>h.hour==i)?.requests ?? 0);

            console.log('loginsByHour:', loginsByHour);
            console.log('requestsByHour:', requestsByHour);

            new Chart(document.getElementById('hourlyChart'), {
                type: 'line',
                data: {
                    labels: hours,
                    datasets: [
                        {
                            label: 'Logins',
                            data: loginsByHour,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.10)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Activity',
                            data: requestsByHour,
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168,85,247,0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: tooltipStyle },
                    scales: {
                        x: { grid: gridStyle, ticks: { maxTicksLimit: 8, color: tickColor, font:{size:9} } },
                        y: { grid: gridStyle, ticks: { color: tickColor, font:{size:10} }, beginAtZero: true },
                    },
                }
            });
        }

        console.log('✅ All charts initialized successfully');
    } catch (error) {
        console.error('❌ Error initializing charts:', error);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
</script>
@endpush