@extends('layouts.app')

@section('title', 'Security & Audit Monitoring')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Security & Audit Monitoring</h1>
        <p class="text-gray-600">Comprehensive security monitoring dashboard dengan audit trail lengkap</p>
    </div>

    <!-- Date Range Filter -->
    <div class="mb-6 flex gap-4">
        <a href="?days=7" @class(['px-4 py-2 rounded-lg font-medium transition', 'bg-blue-600 text-white' => $selectedDays == 7, 'bg-gray-200 text-gray-700 hover:bg-gray-300' => $selectedDays != 7])>7 Days</a>
        <a href="?days=30" @class(['px-4 py-2 rounded-lg font-medium transition', 'bg-blue-600 text-white' => $selectedDays == 30, 'bg-gray-200 text-gray-700 hover:bg-gray-300' => $selectedDays != 30])>30 Days</a>
        <a href="?days=60" @class(['px-4 py-2 rounded-lg font-medium transition', 'bg-blue-600 text-white' => $selectedDays == 60, 'bg-gray-200 text-gray-700 hover:bg-gray-300' => $selectedDays != 60])>60 Days</a>
        <a href="?days=90" @class(['px-4 py-2 rounded-lg font-medium transition', 'bg-blue-600 text-white' => $selectedDays == 90, 'bg-gray-200 text-gray-700 hover:bg-gray-300' => $selectedDays != 90])>90 Days</a>
    </div>

    @if(!empty($anomalies))
    <!-- Alerts / Anomalies -->
    <div class="mb-8 bg-red-50 border border-red-200 rounded-lg p-6">
        <h2 class="text-xl font-bold text-red-900 mb-4">🚨 Security Anomalies Detected</h2>
        <div class="space-y-3">
            @foreach($anomalies as $anomaly)
            <div class="flex items-start gap-3 p-3 bg-white rounded border-l-4 border-red-500">
                <span class="font-semibold text-red-600 text-sm uppercase">{{ $anomaly['severity'] }}</span>
                <div class="flex-1">
                    <p class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $anomaly['type'])) }}</p>
                    <p class="text-gray-600 text-sm">{{ $anomaly['message'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Requests -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Total Requests</div>
            <div class="text-4xl font-bold text-blue-600">{{ number_format($stats['page_access']['total_requests'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">
                ✓ {{ number_format($stats['page_access']['successful'] ?? 0) }} successful
                | ✗ {{ number_format($stats['page_access']['failed'] ?? 0) }} failed
            </div>
        </div>

        <!-- Security Events -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Security Events</div>
            <div class="text-4xl font-bold text-orange-600">{{ number_format($stats['security_events']['total'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">
                <span class="text-red-600 font-bold">{{ $stats['security_events']['critical'] ?? 0 }}</span> critical
                | <span class="text-orange-600 font-bold">{{ $stats['security_events']['high'] ?? 0 }}</span> high
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Active Users</div>
            <div class="text-4xl font-bold text-green-600">{{ number_format($stats['users']['total_active'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">
                {{ number_format($stats['users']['with_anomalies'] ?? 0) }} with anomalies
            </div>
        </div>

        <!-- Unique IPs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-600 text-sm font-medium mb-2">Unique IP Addresses</div>
            <div class="text-4xl font-bold text-purple-600">{{ number_format($stats['ips']['unique_ips'] ?? 0) }}</div>
            <div class="text-xs text-gray-500 mt-2">
                Audit logs: {{ number_format($stats['audit']['total_logs'] ?? 0) }}
            </div>
        </div>
    </div>

    <!-- Denied Access vs Total -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Access Status Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Access Status Breakdown</h3>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Successful (2xx-3xx)</span>
                        <span class="font-bold">{{ number_format($stats['page_access']['successful'] ?? 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['page_access']['total_requests'] > 0 ? round(($stats['page_access']['successful'] / $stats['page_access']['total_requests']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Denied (403)</span>
                        <span class="font-bold text-red-600">{{ number_format($stats['page_access']['denied'] ?? 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $stats['page_access']['total_requests'] > 0 ? round(($stats['page_access']['denied'] / $stats['page_access']['total_requests']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Client Errors (4xx)</span>
                        <span class="font-bold text-orange-600">{{ number_format($stats['page_access']['failed'] ?? 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $stats['page_access']['total_requests'] > 0 ? round(($stats['page_access']['failed'] / $stats['page_access']['total_requests']) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Severity Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Event Severity Distribution</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase px-3 py-1 bg-red-100 text-red-700 rounded">Critical</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-red-600 h-2 rounded-full" style="width: {{ isset($stats['security_events']['by_severity']['critical']) ? round(($stats['security_events']['by_severity']['critical'] / max(array_sum($stats['security_events']['by_severity'] ?? []), 1)) * 100) : 0 }}%"></div>
                    </div>
                    <span class="font-bold text-sm">{{ $stats['security_events']['by_severity']['critical'] ?? 0 }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase px-3 py-1 bg-orange-100 text-orange-700 rounded">High</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" style="width: {{ isset($stats['security_events']['by_severity']['high']) ? round(($stats['security_events']['by_severity']['high'] / max(array_sum($stats['security_events']['by_severity'] ?? []), 1)) * 100) : 0 }}%"></div>
                    </div>
                    <span class="font-bold text-sm">{{ $stats['security_events']['by_severity']['high'] ?? 0 }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase px-3 py-1 bg-yellow-100 text-yellow-700 rounded">Medium</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ isset($stats['security_events']['by_severity']['medium']) ? round(($stats['security_events']['by_severity']['medium'] / max(array_sum($stats['security_events']['by_severity'] ?? []), 1)) * 100) : 0 }}%"></div>
                    </div>
                    <span class="font-bold text-sm">{{ $stats['security_events']['by_severity']['medium'] ?? 0 }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase px-3 py-1 bg-blue-100 text-blue-700 rounded">Low</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ isset($stats['security_events']['by_severity']['low']) ? round(($stats['security_events']['by_severity']['low'] / max(array_sum($stats['security_events']['by_severity'] ?? []), 1)) * 100) : 0 }}%"></div>
                    </div>
                    <span class="font-bold text-sm">{{ $stats['security_events']['by_severity']['low'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Failed Paths & Top IPs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Failed Paths -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Failed Access Paths</h3>
            @if(!empty($stats['page_access']['top_failed_paths']))
            <div class="space-y-2">
                @foreach($stats['page_access']['top_failed_paths'] as $item)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100">
                    <span class="text-sm font-medium text-gray-700 truncate">{{ $item->path }}</span>
                    <span class="ml-2 inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">{{ $item->attempts }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No failed access attempts in this period</p>
            @endif
        </div>

        <!-- Top Accessing IPs -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Accessing IP Addresses</h3>
            @if(!empty($stats['page_access']['top_ips']))
            <div class="space-y-2">
                @foreach($stats['page_access']['top_ips'] as $item)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100">
                    <span class="text-sm font-medium text-gray-700 font-mono">{{ $item->ip_address }}</span>
                    <span class="ml-2 inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">{{ $item->requests }} requests</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No activity in this period</p>
            @endif
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('security.page-access') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="text-2xl mb-2">📄</div>
            <h4 class="font-bold text-gray-900">Page Access Logs</h4>
            <p class="text-sm text-gray-600 mt-1">View all page access attempts</p>
        </a>

        <a href="{{ route('security.events') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="text-2xl mb-2">⚠️</div>
            <h4 class="font-bold text-gray-900">Security Events</h4>
            <p class="text-sm text-gray-600 mt-1">View all security events</p>
        </a>

        <a href="{{ route('security.audit') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="text-2xl mb-2">📋</div>
            <h4 class="font-bold text-gray-900">Audit Logs</h4>
            <p class="text-sm text-gray-600 mt-1">View audit trail & actions</p>
        </a>

        <a href="{{ route('security.export-logs') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="text-2xl mb-2">📥</div>
            <h4 class="font-bold text-gray-900">Export Logs</h4>
            <p class="text-sm text-gray-600 mt-1">Download logs as CSV</p>
        </a>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .container { padding-left: 1rem; padding-right: 1rem; }
    }
</style>
@endsection
    