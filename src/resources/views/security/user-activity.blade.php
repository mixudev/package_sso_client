@extends('mixu-sso-auth::layouts.app')

@section('title', 'User Activity')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">User Activity</h1>
        <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">
            Monitoring profile for
            <span class="font-mono text-blue-400 font-semibold">User #{{ $activity['user_id'] }}</span>
        </p>
    </div>
    @if(!empty($anomalies))
    <span class="badge badge-danger self-start px-3 py-1.5">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        {{ count($anomalies) }} Anomal{{ count($anomalies) > 1 ? 'ies' : 'y' }} Detected
    </span>
    @endif
</div>

@if(!empty($anomalies))
<div class="rounded-xl dark:bg-red-950/40 bg-red-50 border dark:border-red-900/50 border-red-200 p-4 mb-5 sm:mb-6">
    <div class="flex items-center gap-2.5 mb-3">
        <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-red-400">Detected Anomalies</span>
    </div>
    <div class="flex flex-col gap-2">
        @foreach($anomalies as $anomaly)
        <div class="flex items-start gap-3 dark:bg-red-950/50 bg-red-100/70 rounded-lg px-3 py-2.5">
            <span class="badge badge-danger flex-shrink-0 mt-0.5">{{ strtoupper($anomaly['severity']) }}</span>
            <p class="text-sm dark:text-red-200 text-red-700 m-0">{{ $anomaly['message'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Metric Cards — 2 col mobile, 4 col desktop -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">

    @php
        $userMetrics = [
            ['label'=>'Total Requests', 'value'=>number_format($activity['total_requests'] ?? 0), 'sub'=>'Last 7 days',         'color'=>'blue',    'icon'=>'<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
            ['label'=>'Unique IPs',     'value'=>$activity['unique_ips'] ?? 0,                    'sub'=>'Different addresses', 'color'=>'emerald', 'icon'=>'<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
            ['label'=>'Failed Requests','value'=>number_format($activity['failed_requests'] ?? 0), 'sub'=>'4xx & 5xx responses', 'color'=>'orange',  'icon'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
            ['label'=>'Security Flags', 'value'=>$activity['security_flags'] ?? 0,                'sub'=>'Events triggered',   'color'=>'red',     'icon'=>'<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
        ];
        $uColorMap = [
            'blue'   =>['ib'=>'bg-blue-500/10',   'it'=>'text-blue-400',   'vt'=>'dark:text-blue-400 text-blue-600',   'b'=>'dark:border-blue-500/10 border-blue-100'],
            'emerald'=>['ib'=>'bg-emerald-500/10', 'it'=>'text-emerald-400','vt'=>'dark:text-emerald-400 text-emerald-600','b'=>'dark:border-emerald-500/10 border-emerald-100'],
            'orange' =>['ib'=>'bg-orange-500/10',  'it'=>'text-orange-400', 'vt'=>'dark:text-orange-400 text-orange-600', 'b'=>'dark:border-orange-500/10 border-orange-100'],
            'red'    =>['ib'=>'bg-red-500/10',     'it'=>'text-red-400',    'vt'=>'dark:text-red-400 text-red-600',       'b'=>'dark:border-red-500/10 border-red-100'],
        ];
    @endphp

    @foreach($userMetrics as $m)
    @php $c = $uColorMap[$m['color']]; @endphp
    <div class="rounded-xl dark:bg-slate-900 bg-white border {{ $c['b'] }} p-4 sm:p-5">
        <div class="flex items-start justify-between gap-2 mb-2">
            <div class="min-w-0">
                <div class="dark:text-slate-500 text-slate-400 font-medium uppercase mb-1" style="font-size:0.62rem;letter-spacing:0.08em;">{{ $m['label'] }}</div>
                <div class="text-2xl sm:text-3xl font-bold {{ $c['vt'] }} font-mono tabular-nums">{{ $m['value'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl {{ $c['ib'] }} flex items-center justify-center flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $c['it'] }}">{!! $m['icon'] !!}</svg>
            </div>
        </div>
        <div class="text-xs dark:text-slate-500 text-slate-400">{{ $m['sub'] }}</div>
    </div>
    @endforeach

</div>

<!-- Detail Cards — 1 col mobile, 2 cols desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5 sm:mb-6">

    <!-- Top Paths -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-3 sm:mb-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Top Accessed Paths</h3>
        </div>
        @if(!empty($activity['top_paths']))
        <div class="flex flex-col gap-1.5">
            @foreach($activity['top_paths'] as $path)
            <div class="flex items-center justify-between px-3 py-2 rounded-lg dark:bg-slate-800/60 bg-slate-50 border dark:border-slate-700/50 border-slate-200 gap-2">
                <span class="font-mono text-xs dark:text-slate-400 text-slate-500 truncate-path">{{ $path->path }}</span>
                <span class="badge badge-blue flex-shrink-0">{{ $path->count }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs dark:text-slate-600 text-slate-400 py-4 text-center">No activity data available</p>
        @endif
    </div>

    <!-- IP History -->
    <div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-3 sm:mb-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">IP Address History</h3>
        </div>
        @if(!empty($activity['ip_history']))
        <div class="flex flex-col gap-1.5">
            @foreach($activity['ip_history'] as $ip)
            <div class="flex items-center justify-between px-3 py-2 rounded-lg dark:bg-slate-800/60 bg-slate-50 border dark:border-slate-700/50 border-slate-200 gap-2">
                <span class="font-mono text-xs dark:text-slate-400 text-slate-500">{{ $ip->ip_address }}</span>
                <span class="font-mono text-xs dark:text-slate-600 text-slate-400 flex-shrink-0">{{ $ip->last_used }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs dark:text-slate-600 text-slate-400 py-4 text-center">No IP history available</p>
        @endif
    </div>

</div>

<!-- Recent Activity Table -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-4 sm:px-5 py-3.5 border-b dark:border-slate-800 border-slate-200">
        <div class="flex items-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-500 text-slate-400 flex-shrink-0">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <h3 class="text-sm font-semibold dark:text-slate-200 text-slate-700">Recent Activity</h3>
        </div>
        <span class="font-mono text-xs dark:text-slate-600 text-slate-400">Last 20 requests</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table" style="min-width:580px;">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>IP Address</th>
                    <th>Method</th>
                    <th>Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pageAccessLogs as $log)
                <tr>
                    <td class="font-mono whitespace-nowrap">{{ $log->created_at }}</td>
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 whitespace-nowrap">{{ $log->ip_address }}</td>
                    <td>
                        @php $mm = ['GET'=>'badge-blue','POST'=>'badge-cyan','PUT'=>'badge-warning','PATCH'=>'badge-orange','DELETE'=>'badge-danger']; @endphp
                        <span class="badge {{ $mm[$log->method] ?? 'badge-gray' }}">{{ $log->method }}</span>
                    </td>
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 truncate-path" title="{{ $log->path }}">{{ $log->path }}</td>
                    <td>
                        @if($log->status_code < 300)
                            <span class="badge badge-success">{{ $log->status_code }}</span>
                        @elseif($log->status_code < 400)
                            <span class="badge badge-blue">{{ $log->status_code }}</span>
                        @elseif($log->status_code == 403)
                            <span class="badge badge-danger">{{ $log->status_code }}</span>
                        @else
                            <span class="badge badge-orange">{{ $log->status_code }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-800 text-slate-300 mx-auto mb-3"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <p class="text-xs dark:text-slate-600 text-slate-400">No recent activity found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-t dark:border-slate-800 border-slate-200">
        <span class="font-mono text-xs dark:text-slate-600 text-slate-400">{{ $pageAccessLogs->total() ?? '' }} records</span>
        {{ $pageAccessLogs->links() }}
    </div>
</div>

@endsection
