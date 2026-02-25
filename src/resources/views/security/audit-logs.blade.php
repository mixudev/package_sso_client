@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Audit Logs</h1>
        <p class="text-gray-600">Complete audit trail of all user actions and system changes</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-bold text-gray-900 mb-4">Filters</h3>
        <form method="GET" action="{{ route('security.audit') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="User ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Actions</option>
                    <option value="create" @selected($filters['action'] == 'create')>Create</option>
                    <option value="update" @selected($filters['action'] == 'update')>Update</option>
                    <option value="delete" @selected($filters['action'] == 'delete')>Delete</option>
                    <option value="page_access" @selected($filters['action'] == 'page_access')>Page Access</option>
                    <option value="login" @selected($filters['action'] == 'login')>Login</option>
                    <option value="logout" @selected($filters['action'] == 'logout')>Logout</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                <select name="result" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Results</option>
                    <option value="success" @selected($filters['result'] == 'success')>Success</option>
                    <option value="failed" @selected($filters['result'] == 'failed')>Failed</option>
                    <option value="denied" @selected($filters['result'] == 'denied')>Denied</option>
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

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Entity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $log->created_at }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                            @if($log->user_id)
                                {{ $log->user_id }}
                                @if($log->email)
                                    <div class="text-xs text-gray-600">{{ $log->email }}</div>
                                @endif
                            @else
                                <span class="text-gray-500">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $actionColors = [
                                    'create' => 'green',
                                    'update' => 'blue',
                                    'delete' => 'red',
                                    'page_access' => 'gray',
                                    'login' => 'green',
                                    'logout' => 'gray',
                                ];
                                $color = $actionColors[$log->action] ?? 'gray';
                            @endphp
                            <span class="inline-block px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 rounded text-xs font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($log->entity_type)
                                {{ $log->entity_type }}
                                @if($log->entity_id)
                                    <div class="text-xs text-gray-600">#{{ $log->entity_id }}</div>
                                @endif
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            @if($log->result === 'success')
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">✓ Success</span>
                            @elseif($log->result === 'failed')
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">✗ Failed</span>
                            @elseif($log->result === 'denied')
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-bold">🚫 Denied</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">{{ ucfirst($log->result) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($log->details)
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 hover:underline">View Details</summary>
                                    <pre class="mt-2 p-3 bg-gray-50 rounded text-xs overflow-auto max-h-40">{{ $log->details }}</pre>
                                </details>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No audit logs found</td>
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
