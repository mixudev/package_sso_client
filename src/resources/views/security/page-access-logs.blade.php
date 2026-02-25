@extends('layouts.app')

@section('title', 'Page Access Logs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Page Access Logs</h1>
        <p class="text-gray-600">Monitor all page access attempts including successful and failed accesses</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-bold text-gray-900 mb-4">Filters</h3>
        <form method="GET" action="{{ route('security.page-access') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="User ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input type="text" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}" placeholder="IP Address" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Path</label>
                <input type="text" name="path" value="{{ $filters['path'] ?? '' }}" placeholder="Path" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Code</label>
                <select name="status_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="200" @selected($filters['status_code'] == 200)>200 OK</option>
                    <option value="404" @selected($filters['status_code'] == 404)>404 Not Found</option>
                    <option value="403" @selected($filters['status_code'] == 403)>403 Forbidden</option>
                    <option value="500" @selected($filters['status_code'] == 500)>500 Error</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Days</label>
                <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="7" @selected($filters['days'] == 7)>Last 7 Days</option>
                    <option value="30" @selected($filters['days'] == 30)>Last 30 Days</option>
                    <option value="60" @selected($filters['days'] == 60)>Last 60 Days</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Filter</button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Path</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $log->created_at }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                            @if($log->sso_user_id)
                                <a href="{{ route('security.user-activity', ['user_id' => $log->sso_user_id]) }}" class="text-blue-600 hover:underline">{{ $log->sso_user_id }}</a>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                            <a href="{{ route('security.page-access', ['ip_address' => $log->ip_address]) }}" class="text-blue-600 hover:underline">{{ $log->ip_address }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">{{ $log->method }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 truncate font-mono" title="{{ $log->path }}">{{ $log->path }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            @if($log->status_code < 300)
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">{{ $log->status_code }}</span>
                            @elseif($log->status_code < 400)
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold">{{ $log->status_code }}</span>
                            @elseif($log->status_code == 403)
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">{{ $log->status_code }}</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-bold">{{ $log->status_code }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No page access logs found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Back Link -->
    <div class="mt-8">
        <a href="{{ route('security.dashboard') }}" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
</div>
@endsection
