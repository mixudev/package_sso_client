@extends('mixu-sso-auth::layouts.app')

@section('title', 'Page Access Logs')

@section('content')

<a href="{{ route('security.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs dark:text-slate-500 text-slate-400 dark:hover:text-blue-400 hover:text-blue-600 no-underline transition-colors mb-4 sm:mb-5">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Dashboard
</a>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold dark:text-slate-100 text-slate-800 tracking-tight">Page Access Logs</h1>
        <p class="text-sm dark:text-slate-500 text-slate-400 mt-1">Monitor all page access attempts including successful and failed accesses</p>
    </div>
    {{-- Single Delete Button --}}
    <button onclick="openDeleteModal('page-access')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border dark:border-red-500/30 border-red-200 dark:bg-red-500/10 bg-red-50 dark:text-red-400 text-red-600 text-sm font-semibold hover:dark:bg-red-500/20 hover:bg-red-100 transition-all duration-150 whitespace-nowrap self-start sm:self-auto flex-shrink-0">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        Delete Logs
    </button>
</div>

<!-- Filters -->
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 mb-4">
    <div class="dark:text-slate-500 text-slate-400 uppercase font-semibold mb-3" style="font-size:0.6rem;letter-spacing:0.12em;">Filters</div>
    <form method="GET" action="{{ route('security.page-access') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 xl:grid-cols-9 gap-3">
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">From</label>
                <input type="datetime-local" name="from" value="{{ $filters['from'] ?? '' }}"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium dark:text-slate-500 text-slate-400 mb-1.5">To</label>
                <input type="datetime-local" name="to" value="{{ $filters['to'] ?? '' }}"
                       class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
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
                    <th>Actions</th>
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
                    <td class="whitespace-nowrap">
                        <form method="POST" action="{{ route('security.page-access.delete', $log->id ?? 0) }}"
                              onsubmit="return confirmRowDelete(event, this, 'page access log')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium dark:text-red-400 text-red-600 dark:bg-red-500/10 bg-red-50 dark:border-red-500/20 border-red-200 border dark:hover:bg-red-500/20 hover:bg-red-100 transition-all">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12">
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
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     DELETE LOGS MODAL  —  Page Access
══════════════════════════════════════════════════ --}}
<div id="deleteModal-page-access"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="dm-title-page-access">

    {{-- Backdrop --}}
    <div class="absolute inset-0 dark:bg-slate-950/80 bg-slate-900/60 backdrop-blur-sm"
         onclick="closeDeleteModal('page-access')"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-md dark:bg-slate-900 bg-white rounded-2xl border dark:border-slate-800 border-slate-200 shadow-2xl overflow-hidden dm-panel"
         style="animation:dmIn .22s cubic-bezier(0.16,1,0.3,1);">

        {{-- Header --}}
        <div class="flex items-start gap-3 p-5 border-b dark:border-slate-800 border-slate-200">
            <div class="flex-shrink-0 w-9 h-9 rounded-xl dark:bg-red-500/10 bg-red-50 border dark:border-red-500/20 border-red-200 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-red-400 text-red-500">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="dm-title-page-access" class="text-sm font-bold dark:text-slate-100 text-slate-800">Delete Page Access Logs</h3>
                <p class="text-xs dark:text-slate-500 text-slate-400 mt-0.5">Choose a deletion scope. This action cannot be undone.</p>
            </div>
            <button onclick="closeDeleteModal('page-access')" class="flex-shrink-0 p-1 rounded-lg dark:text-slate-600 text-slate-400 dark:hover:text-slate-300 hover:text-slate-600 transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Step 1: Scope selector --}}
        <div id="dm-step1-page-access" class="p-5">
            <p class="text-xs font-semibold dark:text-slate-500 text-slate-400 uppercase tracking-widest mb-3" style="letter-spacing:0.1em;">Select deletion scope</p>
            <div class="space-y-2">
                {{-- Option: Date Range --}}
                <label class="dm-option flex items-start gap-3 p-3 rounded-xl border dark:border-slate-800 border-slate-200 cursor-pointer dark:hover:border-slate-700 hover:border-slate-300 dark:hover:bg-slate-800/50 hover:bg-slate-50 transition-all has-[:checked]:dark:border-blue-500/40 has-[:checked]:border-blue-400 has-[:checked]:dark:bg-blue-500/5 has-[:checked]:bg-blue-50">
                    <input type="radio" name="dm_scope_page-access" value="range" class="mt-0.5 accent-blue-500 flex-shrink-0" onchange="showDmFields('page-access','range')">
                    <div>
                        <div class="text-xs font-semibold dark:text-slate-200 text-slate-700 flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-blue-400 text-blue-500"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Delete by date range
                        </div>
                        <p class="text-xs dark:text-slate-500 text-slate-400 mt-0.5">Remove all logs between two specific dates</p>
                    </div>
                </label>

                {{-- Option: Single Day --}}
                <label class="dm-option flex items-start gap-3 p-3 rounded-xl border dark:border-slate-800 border-slate-200 cursor-pointer dark:hover:border-slate-700 hover:border-slate-300 dark:hover:bg-slate-800/50 hover:bg-slate-50 transition-all has-[:checked]:dark:border-amber-500/40 has-[:checked]:border-amber-400 has-[:checked]:dark:bg-amber-500/5 has-[:checked]:bg-amber-50">
                    <input type="radio" name="dm_scope_page-access" value="day" class="mt-0.5 accent-amber-500 flex-shrink-0" onchange="showDmFields('page-access','day')">
                    <div>
                        <div class="text-xs font-semibold dark:text-slate-200 text-slate-700 flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-amber-400 text-amber-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Delete single day
                        </div>
                        <p class="text-xs dark:text-slate-500 text-slate-400 mt-0.5">Remove all logs for one specific date</p>
                    </div>
                </label>

                {{-- Option: Delete All --}}
                <label class="dm-option flex items-start gap-3 p-3 rounded-xl border dark:border-slate-800 border-slate-200 cursor-pointer dark:hover:border-slate-700 hover:border-slate-300 dark:hover:bg-slate-800/50 hover:bg-slate-50 transition-all has-[:checked]:dark:border-red-500/40 has-[:checked]:border-red-400 has-[:checked]:dark:bg-red-500/5 has-[:checked]:bg-red-50">
                    <input type="radio" name="dm_scope_page-access" value="all" class="mt-0.5 accent-red-500 flex-shrink-0" onchange="showDmFields('page-access','all')">
                    <div>
                        <div class="text-xs font-semibold dark:text-slate-200 text-slate-700 flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="dark:text-red-400 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            Delete ALL logs
                        </div>
                        <p class="text-xs dark:text-slate-500 text-slate-400 mt-0.5">Permanently wipe the entire page access log table</p>
                    </div>
                </label>
            </div>

            {{-- Dynamic Fields --}}
            <div id="dm-fields-page-access" class="mt-4 hidden">

                {{-- Range fields --}}
                <div id="dm-fields-page-access-range" class="hidden space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium dark:text-slate-400 text-slate-500 mb-1.5">From date</label>
                            <input type="date" id="dm-from-page-access" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium dark:text-slate-400 text-slate-500 mb-1.5">To date</label>
                            <input type="date" id="dm-to-page-access" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        </div>
                    </div>
                </div>

                {{-- Day field --}}
                <div id="dm-fields-page-access-day" class="hidden">
                    <label class="block text-xs font-medium dark:text-slate-400 text-slate-500 mb-1.5">Select date</label>
                    <input type="date" id="dm-day-page-access" class="w-full px-3 py-2 rounded-lg text-sm dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                {{-- All warning --}}
                <div id="dm-fields-page-access-all" class="hidden">
                    <div class="flex gap-2.5 p-3 rounded-xl dark:bg-red-500/10 bg-red-50 border dark:border-red-500/20 border-red-200">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-red-400 text-red-500 flex-shrink-0 mt-0.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p class="text-xs dark:text-red-300 text-red-700 leading-relaxed">This will <strong>permanently delete every record</strong> in the page access log. This action is irreversible and cannot be recovered.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button onclick="proceedToConfirm('page-access')"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-sm font-semibold transition-all shadow-sm">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        Continue
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: Final confirmation --}}
        <div id="dm-step2-page-access" class="hidden">
            <div class="p-5 space-y-4">
                {{-- Summary box --}}
                <div class="p-3 rounded-xl dark:bg-slate-800/60 bg-slate-50 border dark:border-slate-700 border-slate-200">
                    <p class="text-xs font-semibold dark:text-slate-400 text-slate-500 uppercase tracking-widest mb-2" style="font-size:0.6rem;letter-spacing:0.1em;">Action summary</p>
                    <p id="dm-summary-page-access" class="text-sm font-semibold dark:text-red-300 text-red-600"></p>
                    <p id="dm-summary-sub-page-access" class="text-xs dark:text-slate-500 text-slate-400 mt-1"></p>
                </div>

                {{-- Typed confirmation --}}
                <div>
                    <label class="block text-xs font-medium dark:text-slate-400 text-slate-500 mb-1.5">
                        Type <span class="font-mono font-bold dark:text-red-400 text-red-600">DELETE</span> to confirm
                    </label>
                    <input type="text" id="dm-confirm-input-page-access"
                           placeholder="Type DELETE here"
                           autocomplete="off" spellcheck="false"
                           oninput="validateConfirmInput('page-access')"
                           class="w-full px-3 py-2 rounded-lg text-sm font-mono dark:bg-slate-800 bg-slate-50 border dark:border-slate-700 border-slate-200 dark:text-slate-200 text-slate-700 dark:placeholder-slate-600 placeholder-slate-400 focus:outline-none focus:border-red-500 transition-colors tracking-widest">
                </div>

                {{-- Hidden forms --}}
                <form id="dm-form-range-page-access" method="POST" action="{{ route('security.page-access.delete-range') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="from" id="dm-hidden-from-page-access">
                    <input type="hidden" name="to"   id="dm-hidden-to-page-access">
                </form>
                <form id="dm-form-day-page-access" method="POST" action="{{ route('security.page-access.delete-day') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="date" id="dm-hidden-day-page-access">
                </form>
                <form id="dm-form-all-page-access" method="POST" action="{{ route('security.page-access.delete-all') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="confirm_all" value="DELETE ALL">
                </form>

                {{-- Actions --}}
                <div class="flex gap-2 pt-1">
                    <button onclick="backToStep1('page-access')"
                            class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold dark:bg-slate-800 bg-slate-100 dark:text-slate-300 text-slate-600 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all">
                        Back
                    </button>
                    <button id="dm-submit-page-access"
                            onclick="submitDelete('page-access')"
                            disabled
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-red-600 text-white opacity-40 cursor-not-allowed transition-all"
                            style="transition:opacity 0.15s,background-color 0.15s">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
@keyframes dmIn {
    from { opacity:0; transform:scale(0.96) translateY(8px); }
    to   { opacity:1; transform:scale(1)    translateY(0); }
}
</style>
<script>
/* ── Single-row delete (per-record) ── */
function confirmRowDelete(e, form, label) {
    e.preventDefault();
    showRowDeleteModal(form, label);
    return false;
}

function showRowDeleteModal(form, label) {
    var modal = document.getElementById('rowDeleteModal');
    var btn   = document.getElementById('rowDeleteConfirmBtn');
    if (!modal) {
        // build inline if not present
        var el = document.createElement('div');
        el.id = 'rowDeleteModal';
        el.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
        el.innerHTML = `
          <div class="absolute inset-0 dark:bg-slate-950/80 bg-slate-900/60 backdrop-blur-sm" onclick="closeRowDeleteModal()"></div>
          <div class="relative w-full max-w-sm dark:bg-slate-900 bg-white rounded-2xl border dark:border-slate-800 border-slate-200 shadow-2xl p-5" style="animation:dmIn .22s cubic-bezier(0.16,1,0.3,1)">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-9 h-9 rounded-xl dark:bg-red-500/10 bg-red-50 border dark:border-red-500/20 border-red-200 flex items-center justify-center flex-shrink-0">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-red-400 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <div>
                <p class="text-sm font-bold dark:text-slate-100 text-slate-800">Delete Record</p>
                <p class="text-xs dark:text-slate-500 text-slate-400 mt-0.5" id="rowDeleteLabel"></p>
              </div>
            </div>
            <p class="text-xs dark:text-slate-400 text-slate-500 mb-4">This record will be permanently removed and cannot be recovered.</p>
            <div class="flex gap-2">
              <button onclick="closeRowDeleteModal()" class="flex-1 px-3 py-2 rounded-xl text-sm font-semibold dark:bg-slate-800 bg-slate-100 dark:text-slate-300 text-slate-600 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all">Cancel</button>
              <button id="rowDeleteConfirmBtn" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition-all">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Delete
              </button>
            </div>
          </div>`;
        document.body.appendChild(el);
    }
    document.getElementById('rowDeleteLabel').textContent = 'You are about to delete this ' + label + '.';
    document.getElementById('rowDeleteConfirmBtn').onclick = function() {
        closeRowDeleteModal();
        form.submit();
    };
    document.getElementById('rowDeleteModal').style.display = 'flex';
}
function closeRowDeleteModal() {
    var m = document.getElementById('rowDeleteModal');
    if (m) m.style.display = 'none';
}

/* ── Bulk delete modal ── */
function openDeleteModal(ns) {
    var m = document.getElementById('deleteModal-' + ns);
    if (!m) return;
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // reset state
    document.querySelectorAll('input[name="dm_scope_' + ns + '"]').forEach(function(r){ r.checked = false; });
    var fields = document.getElementById('dm-fields-' + ns);
    if (fields) { fields.classList.add('hidden'); fields.classList.remove('block'); }
    ['range','day','all'].forEach(function(t){
        var el = document.getElementById('dm-fields-' + ns + '-' + t);
        if (el) el.classList.add('hidden');
    });
    var s1 = document.getElementById('dm-step1-' + ns);
    var s2 = document.getElementById('dm-step2-' + ns);
    if (s1) { s1.style.display = ''; }
    if (s2) s2.classList.add('hidden');
    var ci = document.getElementById('dm-confirm-input-' + ns);
    if (ci) ci.value = '';
}

function closeDeleteModal(ns) {
    var m = document.getElementById('deleteModal-' + ns);
    if (m) m.style.display = 'none';
    document.body.style.overflow = '';
}

function showDmFields(ns, type) {
    var container = document.getElementById('dm-fields-' + ns);
    if (container) { container.classList.remove('hidden'); }
    ['range','day','all'].forEach(function(t){
        var el = document.getElementById('dm-fields-' + ns + '-' + t);
        if (el) el.classList.add('hidden');
    });
    var target = document.getElementById('dm-fields-' + ns + '-' + type);
    if (target) target.classList.remove('hidden');
}

function proceedToConfirm(ns) {
    var scope = document.querySelector('input[name="dm_scope_' + ns + '"]:checked');
    if (!scope) { alert('Please select a deletion scope.'); return; }
    var type = scope.value;

    var summary = '', sub = '';
    if (type === 'range') {
        var from = document.getElementById('dm-from-' + ns).value;
        var to   = document.getElementById('dm-to-' + ns).value;
        if (!from || !to) { alert('Please select both From and To dates.'); return; }
        if (from > to)    { alert('From date must be before To date.'); return; }
        summary = 'Delete all logs from ' + from + ' to ' + to;
        sub     = 'All page access records in this date range will be permanently deleted.';
        document.getElementById('dm-hidden-from-' + ns).value = from;
        document.getElementById('dm-hidden-to-' + ns).value   = to;
    } else if (type === 'day') {
        var day = document.getElementById('dm-day-' + ns).value;
        if (!day) { alert('Please select a date.'); return; }
        summary = 'Delete all logs on ' + day;
        sub     = 'All page access records for this single day will be permanently deleted.';
        document.getElementById('dm-hidden-day-' + ns).value = day;
    } else if (type === 'all') {
        summary = 'Delete ALL page access logs';
        sub     = 'Every single record in the page access log table will be wiped permanently.';
    }

    document.getElementById('dm-summary-' + ns).textContent     = summary;
    document.getElementById('dm-summary-sub-' + ns).textContent = sub;
    document.getElementById('dm-confirm-input-' + ns).value = '';

    var btn = document.getElementById('dm-submit-' + ns);
    btn.disabled = true;
    btn.classList.add('opacity-40','cursor-not-allowed');
    btn.classList.remove('opacity-100','cursor-pointer','hover:bg-red-700');

    document.getElementById('dm-step1-' + ns).style.display = 'none';
    document.getElementById('dm-step2-' + ns).classList.remove('hidden');
}

function backToStep1(ns) {
    document.getElementById('dm-step1-' + ns).style.display = '';
    document.getElementById('dm-step2-' + ns).classList.add('hidden');
}

function validateConfirmInput(ns) {
    var val = document.getElementById('dm-confirm-input-' + ns).value;
    var btn = document.getElementById('dm-submit-' + ns);
    if (val === 'DELETE') {
        btn.disabled = false;
        btn.classList.remove('opacity-40','cursor-not-allowed');
        btn.classList.add('opacity-100','hover:bg-red-700');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-40','cursor-not-allowed');
        btn.classList.remove('opacity-100','hover:bg-red-700');
    }
}

function submitDelete(ns) {
    var scope = document.querySelector('input[name="dm_scope_' + ns + '"]:checked');
    if (!scope) return;
    var type = scope.value;
    var formId = 'dm-form-' + type + '-' + ns;
    var form = document.getElementById(formId);
    if (form) form.submit();
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['page-access'].forEach(function(ns){ closeDeleteModal(ns); });
        closeRowDeleteModal();
    }
});
</script>
@endpush