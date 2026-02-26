@extends('mixu-sso-auth::layouts.app')

@section('title', 'Audit Trail')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

<div class="mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Audit Trail</h1>
    <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">Complete audit log of all user actions and system changes</p>
</div>

<!-- Filters -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 mb-4">
    <div class="dark:text-slate-500 text-slate-400 uppercase font-semibold mb-3" style="font-size:0.6rem;letter-spacing:0.12em;">Filters</div>
    <form method="GET" action="{{ route('security.audit') }}">
        <!-- Responsive grid: 1 col → 2 col → 5 col -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">User ID</label>
                <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="e.g. 1024"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 dark:placeholder-slate-600 placeholder-slate-400 focus:outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Action</label>
                <select name="action" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">All Actions</option>
                    <option value="create"      @selected($filters['action'] == 'create')>Create</option>
                    <option value="update"      @selected($filters['action'] == 'update')>Update</option>
                    <option value="delete"      @selected($filters['action'] == 'delete')>Delete</option>
                    <option value="page_access" @selected($filters['action'] == 'page_access')>Page Access</option>
                    <option value="login"       @selected($filters['action'] == 'login')>Login</option>
                    <option value="logout"      @selected($filters['action'] == 'logout')>Logout</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">Result</label>
                <select name="result" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="">All Results</option>
                    <option value="success" @selected($filters['result'] == 'success')>Success</option>
                    <option value="failed"  @selected($filters['result'] == 'failed')>Failed</option>
                    <option value="denied"  @selected($filters['result'] == 'denied')>Denied</option>
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
            <div class="flex items-end sm:col-span-2 lg:col-span-1">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply Filters
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Table — always horizontally scrollable -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 overflow-hidden">
    <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
        <table class="data-table" style="min-width:700px;">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>IP Address</th>
                    <th>Result</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="font-mono whitespace-nowrap">{{ $log->created_at }}</td>
                    <td>
                        @if($log->user_id)
                            <span class="font-mono text-xs font-semibold dark:text-slate-300 text-slate-600">{{ $log->user_id }}</span>
                            @if($log->email)
                                <div class="font-mono text-xs dark:text-slate-600 text-slate-400 mt-0.5">{{ $log->email }}</div>
                            @endif
                        @else
                            <span class="badge badge-gray">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $am = ['create'=>'badge-success','update'=>'badge-blue','delete'=>'badge-danger','page_access'=>'badge-gray','login'=>'badge-cyan','logout'=>'badge-gray'];
                        @endphp
                        <span class="badge {{ $am[$log->action] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                    </td>
                    <td>
                        @if($log->entity_type)
                            <span class="text-xs dark:text-slate-400 text-slate-500">{{ $log->entity_type }}</span>
                            @if($log->entity_id)
                                <div class="font-mono text-xs dark:text-slate-600 text-slate-400 mt-0.5">#{{ $log->entity_id }}</div>
                            @endif
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="font-mono text-xs dark:text-slate-500 text-slate-400 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                    <td>
                        @if($log->result === 'success')
                            <span class="badge badge-success"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Success</span>
                        @elseif($log->result === 'failed')
                            <span class="badge badge-danger"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Failed</span>
                        @elseif($log->result === 'denied')
                            <span class="badge badge-orange"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>Denied</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($log->result) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($log->details)
                            <details>
                                <summary><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>View</summary>
                                <pre>{{ $log->details }}</pre>
                            </details>
                        @else
                            <span class="dark:text-slate-700 text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-slate-800 text-slate-300 mx-auto mb-3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <p class="text-xs dark:text-slate-600 text-slate-400">No audit logs found for the selected filters</p>
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
