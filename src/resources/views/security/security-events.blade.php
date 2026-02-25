@extends('layouts.app')

@section('title', 'Security Events')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Security Events</h1>
        <p class="text-gray-600">Monitor all security events including login failures, IP mismatches, and suspicious activities</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-bold text-gray-900 mb-4">Filters</h3>
        <form method="GET" action="{{ route('security.events') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                <select name="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Types</option>
                    <option value="login" @selected($filters['event_type'] == 'login')>Login</option>
                    <option value="auth_failure" @selected($filters['event_type'] == 'auth_failure')>Auth Failure</option>
                    <option value="access_denied" @selected($filters['event_type'] == 'access_denied')>Access Denied</option>
                    <option value="ip_mismatch" @selected($filters['event_type'] == 'ip_mismatch')>IP Mismatch</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Levels</option>
                    <option value="critical" @selected($filters['severity'] == 'critical')>Critical</option>
                    <option value="high" @selected($filters['severity'] == 'high')>High</option>
                    <option value="medium" @selected($filters['severity'] == 'medium')>Medium</option>
                    <option value="low" @selected($filters['severity'] == 'low')>Low</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="User ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input type="text" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}" placeholder="IP Address" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Filter</button>
            </div>
        </form>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Event Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($events as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $event->created_at }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($event->sso_user_id)
                                <a href="{{ route('security.user-activity', ['user_id' => $event->sso_user_id]) }}" class="text-blue-600 hover:underline">
                                    ID: {{ $event->sso_user_id }}
                                </a>
                                @if($event->email)
                                    <div class="text-xs text-gray-600">{{ $event->email }}</div>
                                @endif
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                            <a href="{{ route('security.events', ['ip_address' => $event->ip_address]) }}" class="text-blue-600 hover:underline">{{ $event->ip_address }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            @if($event->severity === 'critical')
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">🔴 Critical</span>
                            @elseif($event->severity === 'high')
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-bold">🟠 High</span>
                            @elseif($event->severity === 'medium')
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold">🟡 Medium</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold">🔵 Low</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($event->details)
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 hover:underline">View Details</summary>
                                    <pre class="mt-2 p-3 bg-gray-50 rounded text-xs overflow-auto max-h-40">{{ $event->details }}</pre>
                                </details>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No security events found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $events->links() }}
        </div>
    </div>

    <!-- Back Link -->
    <div class="mt-8">
        <a href="{{ route('security.dashboard') }}" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
</div>
@endsection
