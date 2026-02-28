@extends('mixu-sso-auth::layouts.app')

@section('title', 'Export Logs')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

{{-- ── Page Header ── --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5 sm:mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Security Log Export</h1>
            @if(isset($logs) && $logs->total())
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-xs font-semibold dark:bg-slate-800 bg-slate-100 dark:text-slate-400 text-slate-500 border dark:border-slate-700 border-slate-200">
                    {{ number_format($logs->total()) }} records
                </span>
            @endif
        </div>
        <p class="text-sm dark:text-slate-500 text-slate-400">Browse and download combined logs from all security sources</p>
    </div>

    {{-- Download CSV --}}
    <a href="{{ route('security.export-logs.download') }}?{{ http_build_query(request()->query()) }}"
       class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg dark:bg-emerald-500/10 bg-emerald-50 border dark:border-emerald-500/25 border-emerald-200 dark:text-emerald-400 text-emerald-700 text-sm font-semibold dark:hover:bg-emerald-500/20 hover:bg-emerald-100 transition-all whitespace-nowrap flex-shrink-0 self-start sm:self-auto">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-y-0.5 transition-transform duration-150">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Download CSV
    </a>
</div>

{{-- ── Filters ── --}}
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 mb-4">
    <div class="flex items-center gap-2 mb-3">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-600 text-slate-400"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        <span class="dark:text-slate-500 text-slate-400 uppercase font-semibold" style="font-size:0.6rem;letter-spacing:0.12em;">Date Range Filter</span>
    </div>
    <form method="GET" action="{{ route('security.export-logs') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">From</label>
                <div class="relative">
                    <input type="datetime-local" name="from" value="{{ $from ?? '' }}"
                           class="w-full pl-9 pr-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none dark:text-slate-600 text-slate-400" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">To</label>
                <div class="relative">
                    <input type="datetime-local" name="to" value="{{ $to ?? '' }}"
                           class="w-full pl-9 pr-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none dark:text-slate-600 text-slate-400" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>

            {{-- Quick range presets --}}
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Quick Range</label>
                <select id="quickRange" onchange="applyQuickRange(this.value)"
                        class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">— Select preset —</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="7d">Last 7 days</option>
                    <option value="30d">Last 30 days</option>
                    <option value="this_month">This month</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply
                </button>
                @if(request('from') || request('to'))
                <a href="{{ route('security.export-logs') }}"
                   class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg dark:bg-slate-800 bg-slate-100 dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-colors" title="Clear filters">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
                @endif
            </div>
        </div>

        {{-- Active filter indicator --}}
        @if(request('from') || request('to'))
        <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="text-xs dark:text-slate-500 text-slate-400">Active:</span>
            @if(request('from'))
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-mono dark:bg-blue-500/10 bg-blue-50 dark:text-blue-400 text-blue-600 border dark:border-blue-500/20 border-blue-200">
                    from: {{ request('from') }}
                </span>
            @endif
            @if(request('to'))
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-mono dark:bg-blue-500/10 bg-blue-50 dark:text-blue-400 text-blue-600 border dark:border-blue-500/20 border-blue-200">
                    to: {{ request('to') }}
                </span>
            @endif
        </div>
        @endif
    </form>
</div>

{{-- ── Table ── --}}
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 overflow-hidden">
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling:touch;">
        <table class="data-table" style="min-width:960px;">
            <thead>
                <tr>
                    <th style="width:148px;">Timestamp</th>
                    <th style="width:110px;">Source</th>
                    <th style="width:72px;">User ID</th>
                    <th style="width:160px;">Email</th>
                    <th>Description</th>
                    <th style="width:120px;">IP Address</th>
                    <th style="width:68px;">Method</th>
                    <th>Path</th>
                    <th style="width:72px;">Status</th>
                    <th style="width:72px;">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    {{-- Timestamp --}}
                    <td class="whitespace-nowrap">
                        <span class="font-mono text-xs dark:text-slate-400 text-slate-500">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d') }}
                        </span>
                        <div class="font-mono text-xs dark:text-slate-600 text-slate-400 mt-0.5">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                        </div>
                    </td>

                    {{-- Source / Log Type --}}
                    <td>
                        @php
                            $typeMap = [
                                'page_access'    => ['badge-blue',   'Page Access'],
                                'audit'          => ['badge-cyan',   'Audit'],
                                'security_event' => ['badge-purple', 'Security'],
                                'login'          => ['badge-success','Login'],
                                'logout'         => ['badge-gray',   'Logout'],
                            ];
                            $typeKey  = strtolower($log->log_type ?? '');
                            $typeCfg  = $typeMap[$typeKey] ?? ['badge-gray', ucfirst($typeKey ?: '—')];
                        @endphp
                        <span class="badge {{ $typeCfg[0] }}">{{ $typeCfg[1] }}</span>
                    </td>

                    {{-- User ID --}}
                    <td>
                        @if($log->user_id)
                            <a href="{{ route('security.user-activity', ['user_id' => $log->user_id]) }}"
                               class="font-mono text-xs text-blue-400 hover:text-blue-300 hover:underline no-underline transition-colors">
                                {{ $log->user_id }}
                            </a>
                        @else
                            <span class="dark:text-slate-700 text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Email --}}
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 max-w-0" style="max-width:160px;">
                        @if($log->email)
                            <span class="block truncate" title="{{ $log->email }}">{{ $log->email }}</span>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>

                    {{-- Description --}}
                    <td class="dark:text-slate-400 text-slate-500 text-xs max-w-0" style="max-width:220px;">
                        <span class="block truncate" title="{{ $log->description }}">{{ $log->description ?: '—' }}</span>
                    </td>

                    {{-- IP --}}
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 whitespace-nowrap">
                        {{ $log->ip_address ?? '—' }}
                    </td>

                    {{-- Method --}}
                    <td>
                        @if($log->method)
                            @php $mm = ['GET'=>'badge-blue','POST'=>'badge-cyan','PUT'=>'badge-warning','PATCH'=>'badge-orange','DELETE'=>'badge-danger']; @endphp
                            <span class="badge {{ $mm[$log->method] ?? 'badge-gray' }}">{{ $log->method }}</span>
                        @else
                            <span class="dark:text-slate-700 text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Path --}}
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 max-w-0" style="max-width:200px;">
                        @if($log->path)
                            <span class="block truncate" title="{{ $log->path }}">{{ $log->path }}</span>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>

                    {{-- Status Code --}}
                    <td>
                        @if($log->status_code)
                            @if($log->status_code < 300)
                                <span class="badge badge-success">{{ $log->status_code }}</span>
                            @elseif($log->status_code < 400)
                                <span class="badge badge-blue">{{ $log->status_code }}</span>
                            @elseif($log->status_code == 403)
                                <span class="badge badge-danger">{{ $log->status_code }}</span>
                            @elseif($log->status_code < 500)
                                <span class="badge badge-orange">{{ $log->status_code }}</span>
                            @else
                                <span class="badge badge-danger">{{ $log->status_code }}</span>
                            @endif
                        @else
                            <span class="dark:text-slate-700 text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Details --}}
                    <td>
                        @if($log->details)
                            <details>
                                <summary>
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    View
                                </summary>
                                <pre>{{ $log->details }}</pre>
                            </details>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-16">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-xl dark:bg-slate-800 bg-slate-100 border dark:border-slate-700 border-slate-200 flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-600 text-slate-400">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-slate-500 text-slate-400">No logs found</p>
                                <p class="text-xs dark:text-slate-600 text-slate-400 mt-0.5">Try adjusting the date range filter</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer: count + pagination --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-t dark:border-slate-800 border-slate-200 dark:bg-slate-950/30">
        <div class="flex items-center gap-3">
            <span class="font-mono text-xs dark:text-slate-600 text-slate-400">
                {{ number_format($logs->total() ?? 0) }} records
            </span>
            @if($logs->hasPages())
                <span class="dark:text-slate-800 text-slate-300 text-xs">·</span>
                <span class="font-mono text-xs dark:text-slate-600 text-slate-400">
                    page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                </span>
            @endif
        </div>
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
function applyQuickRange(preset) {
    if (!preset) return;
    const now   = new Date();
    const pad   = n => String(n).padStart(2, '0');
    const fmt   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    const sod   = d => { const c = new Date(d); c.setHours(0,0,0,0); return c; };
    const eod   = d => { const c = new Date(d); c.setHours(23,59,59,0); return c; };

    let from, to;
    if (preset === 'today') {
        from = sod(now); to = eod(now);
    } else if (preset === 'yesterday') {
        const y = new Date(now); y.setDate(y.getDate()-1);
        from = sod(y); to = eod(y);
    } else if (preset === '7d') {
        const s = new Date(now); s.setDate(s.getDate()-6);
        from = sod(s); to = eod(now);
    } else if (preset === '30d') {
        const s = new Date(now); s.setDate(s.getDate()-29);
        from = sod(s); to = eod(now);
    } else if (preset === 'this_month') {
        from = new Date(now.getFullYear(), now.getMonth(), 1);
        to   = eod(now);
    }

    if (from && to) {
        document.querySelector('input[name="from"]').value = fmt(from);
        document.querySelector('input[name="to"]').value   = fmt(to);
    }
    document.getElementById('quickRange').value = '';
}
</script>
@endpush