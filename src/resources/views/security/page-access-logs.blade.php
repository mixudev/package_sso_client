@extends('mixu-sso-auth::layouts.app')

@section('title', 'Page Access Logs')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

<div class="mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Page Access Logs</h1>
    <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">Monitor all page access attempts including successful and failed accesses</p>
</div>

<!-- Filters -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 mb-4">
    <div class="dark:text-slate-500 text-slate-400 uppercase font-semibold mb-3" style="font-size:0.6rem;letter-spacing:0.12em;">Filters</div>
    <form method="GET" action="{{ route('security.page-access') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">User ID</label>
                <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="e.g. 1024"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 dark:placeholder-slate-600 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">IP Address</label>
                <input type="text" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}" placeholder="e.g. 192.168.1.1"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 dark:placeholder-slate-600 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Path</label>
                <input type="text" name="path" value="{{ $filters['path'] ?? '' }}" placeholder="e.g. /admin"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 dark:placeholder-slate-600 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Status Code</label>
                <select name="status_code" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">All Codes</option>
                    <option value="200" @selected($filters['status_code'] == 200)>200 OK</option>
                    <option value="404" @selected($filters['status_code'] == 404)>404 Not Found</option>
                    <option value="403" @selected($filters['status_code'] == 403)>403 Forbidden</option>
                    <option value="500" @selected($filters['status_code'] == 500)>500 Error</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Time Range</label>
                <select name="days" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="7"  @selected($filters['days'] == 7)>Last 7 Days</option>
                    <option value="30" @selected($filters['days'] == 30)>Last 30 Days</option>
                    <option value="60" @selected($filters['days'] == 60)>Last 60 Days</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table" style="min-width:640px;">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User ID</th>
                    <th>IP Address</th>
                    <th>Method</th>
                    <th>Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="font-mono whitespace-nowrap">{{ $log->created_at }}</td>
                    <td>
                        @if($log->sso_user_id)
                            <div class="font-semibold dark:text-slate-200 text-slate-700">{{ $log->user_name ?? 'Unknown' }}</div>
                            <a href="{{ route('security.user-activity', ['user_id' => $log->sso_user_id]) }}" class="font-mono text-xs text-blue-400 hover:text-blue-300 hover:underline no-underline transition-colors">ID: {{ $log->sso_user_id }}</a>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('security.page-access', ['ip_address' => $log->ip_address]) }}" class="font-mono text-xs text-blue-400 hover:text-blue-300 hover:underline no-underline transition-colors whitespace-nowrap">{{ $log->ip_address }}</a>
                    </td>
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
                    <td colspan="6" class="text-center py-12">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-800 text-slate-300 mx-auto mb-3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p class="text-xs dark:text-slate-600 text-slate-400">No page access logs found for the selected filters</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-t dark:border-slate-800 border-slate-200">
        <span class="font-mono text-xs dark:text-slate-600 text-slate-400">{{ $logs->total() ?? '' }} records</span>
        {{ $logs->links() }}
    </div>
</div>

@endsection
