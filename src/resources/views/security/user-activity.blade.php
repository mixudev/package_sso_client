@extends('layouts.app')

@section('title', 'User Activity Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">User Activity Details</h1>
        <p class="text-gray-600">Comprehensive activity monitoring for User ID: {{ $activity['user_id'] }}</p>
    </div>

    <!-- Anomalies Alert -->
    @if(!empty($anomalies))
    <div class="mb-8 bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="font-bold text-red-900 mb-4">🚨 Detected Anomalies</h3>
        <div class="space-y-2">
            @foreach($anomalies as $anomaly)
            <div class="p-3 bg-white rounded border-l-4 border-red-500">
                <p class="text-sm"><span class="font-bold uppercase text-red-600">{{ $anomaly['severity'] }}</span> - {{ $anomaly['message'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Activity Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Total Requests</div>
            <div class="text-4xl font-bold text-blue-600">{{ number_format($activity['total_requests'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">In last 7 days</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Unique IPs</div>
            <div class="text-4xl font-bold text-green-600">{{ $activity['unique_ips'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-2">Different IP addresses</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Failed Requests</div>
            <div class="text-4xl font-bold text-orange-600">{{ number_format($activity['failed_requests'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">4xx & 5xx responses</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Security Flags</div>
            <div class="text-4xl font-bold text-red-600">{{ $activity['security_flags'] ?? 0 }}</div>
            <div class="text-xs text-gray-500 mt-2">Events triggered</div>
        </div>
    </div>

    <!-- Top Accessed Paths -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">Top Accessed Paths</h3>
            @if(!empty($activity['top_paths']))
            <div class="space-y-2">
                @foreach($activity['top_paths'] as $path)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100">
                    <span class="text-sm font-medium text-gray-700 truncate">{{ $path->path }}</span>
                    <span class="ml-2 inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">{{ $path->count }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No activity data available</p>
            @endif
        </div>

        <!-- IP Address History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-900 mb-4">IP Address History</h3>
            @if(!empty($activity['ip_history']))
            <div class="space-y-2">
                @foreach($activity['ip_history'] as $ip)
                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-mono text-gray-900">{{ $ip->ip_address }}</span>
                        <span class="text-xs text-gray-600">{{ $ip->last_used }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No IP history available</p>
            @endif
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-bold text-gray-900">Recent Activity (Last 20 requests)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Path</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pageAccessLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $log->created_at }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $log->ip_address }}</td>
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No recent activity</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $pageAccessLogs->links() }}
        </div>
    </div>

    <!-- Back Link -->
    <div class="mt-8">
        <a href="{{ route('security.dashboard') }}" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
</div>
@endsection
