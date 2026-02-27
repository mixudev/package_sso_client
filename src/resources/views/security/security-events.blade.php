@extends('mixu-sso-auth::layouts.app')

@section('title', 'Security Events')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

<div class="mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Security Events</h1>
    <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">Login failures, IP mismatches, and suspicious activity monitoring</p>
</div>

<!-- Filters -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 mb-4">
    <div class="dark:text-slate-500 text-slate-400 uppercase font-semibold mb-3" style="font-size:0.6rem;letter-spacing:0.12em;">Filters</div>
    <form method="GET" action="{{ route('security.events') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Event Type</label>
                <select name="event_type" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">All Types</option>
                    <option value="login"         @selected($filters['event_type'] == 'login')>Login</option>
                    <option value="auth_failure"  @selected($filters['event_type'] == 'auth_failure')>Auth Failure</option>
                    <option value="access_denied" @selected($filters['event_type'] == 'access_denied')>Access Denied</option>
                    <option value="ip_mismatch"   @selected($filters['event_type'] == 'ip_mismatch')>IP Mismatch</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Severity</label>
                <select name="severity" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">All Levels</option>
                    <option value="critical" @selected($filters['severity'] == 'critical')>Critical</option>
                    <option value="high"     @selected($filters['severity'] == 'high')>High</option>
                    <option value="medium"   @selected($filters['severity'] == 'medium')>Medium</option>
                    <option value="low"      @selected($filters['severity'] == 'low')>Low</option>
                </select>
            </div>
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
            <div class="flex items-end sm:col-span-2 lg:col-span-1">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply Filters
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
                    <th>Event Type</th>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>Severity</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td class="font-mono whitespace-nowrap">{{ $event->created_at }}</td>
                    <td><span class="badge badge-purple">{{ ucfirst(str_replace('_',' ',$event->event_type)) }}</span></td>
                    <td>
                        @if($event->sso_user_id)
                            <div class="font-semibold dark:text-slate-200 text-slate-700">{{ $event->user_name ?? 'Unknown' }}</div>
                            <a href="{{ route('security.user-activity', ['user_id' => $event->sso_user_id]) }}" class="font-mono text-xs text-blue-400 hover:text-blue-300 hover:underline no-underline transition-colors">ID: {{ $event->sso_user_id }}</a>
                            @if($event->email)
                                <div class="font-mono text-xs dark:text-slate-600 text-slate-400 mt-0.5">{{ $event->email }}</div>
                            @endif
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('security.events', ['ip_address' => $event->ip_address]) }}" class="font-mono text-xs text-blue-400 hover:text-blue-300 hover:underline no-underline transition-colors whitespace-nowrap">{{ $event->ip_address }}</a>
                    </td>
                    <td>
                        @if($event->severity === 'critical')
                            <span class="badge badge-danger"><svg width="6" height="6" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>Critical</span>
                        @elseif($event->severity === 'high')
                            <span class="badge badge-orange"><svg width="6" height="6" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>High</span>
                        @elseif($event->severity === 'medium')
                            <span class="badge badge-warning"><svg width="6" height="6" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>Medium</span>
                        @else
                            <span class="badge badge-blue"><svg width="6" height="6" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>Low</span>
                        @endif
                    </td>
                    <td>
                        @if($event->details)
                            <details>
                                <summary><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>View</summary>
                                <pre>{{ $event->details }}</pre>
                            </details>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-800 text-slate-300 mx-auto mb-3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <p class="text-xs dark:text-slate-600 text-slate-400">No security events found for the selected filters</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 border-t dark:border-slate-800 border-slate-200">
        <span class="font-mono text-xs dark:text-slate-600 text-slate-400">{{ $events->total() ?? '' }} records</span>
        {{ $events->links() }}
    </div>
</div>

@endsection
