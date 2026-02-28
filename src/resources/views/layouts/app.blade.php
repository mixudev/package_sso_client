<!DOCTYPE html>
<html lang="en" class="dark" x-data="{
    darkMode: localStorage.getItem('theme') === 'light' ? false : true,
    sidebarOpen: false
}" x-init="$watch('darkMode', v => { localStorage.setItem('theme', v ? 'dark' : 'light');
    document.documentElement.classList.toggle('dark', v); });
document.documentElement.classList.toggle('dark', darkMode);" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Security Monitoring') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                    colors: {
                        slate: {
                            950: '#020617'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite'
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        .nav-active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.04));
            border-left: 2px solid #3b82f6;
        }

        @keyframes statusPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(34, 197, 94, 0);
            }
        }

        .status-pulse {
            animation: statusPulse 2.5s ease-in-out infinite;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-blue {
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-cyan {
            background: rgba(6, 182, 212, 0.12);
            color: #22d3ee;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .badge-purple {
            background: rgba(139, 92, 246, 0.12);
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        .badge-orange {
            background: rgba(249, 115, 22, 0.12);
            color: #fb923c;
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        .badge-gray {
            background: rgba(100, 116, 139, 0.12);
            color: #94a3b8;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        html:not(.dark) .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        html:not(.dark) .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        html:not(.dark) .badge-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        html:not(.dark) .badge-cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        html:not(.dark) .badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
        }

        html:not(.dark) .badge-orange {
            background: rgba(249, 115, 22, 0.1);
            color: #ea580c;
        }

        html:not(.dark) .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        html:not(.dark) .badge-gray {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.79rem;
        }

        .data-table th {
            padding: 10px 14px;
            text-align: left;
            font-size: 0.63rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 10px 14px;
            border-top: 1px solid;
        }

        .dark .data-table th {
            color: #64748b;
            border-bottom: 1px solid #1e293b;
            background: rgba(15, 23, 42, 0.5);
        }

        .dark .data-table td {
            color: #cbd5e1;
            border-color: #1e293b;
        }

        .dark .data-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        html:not(.dark) .data-table th {
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        html:not(.dark) .data-table td {
            color: #334155;
            border-color: #e2e8f0;
        }

        html:not(.dark) .data-table tr:hover td {
            background: #f8fafc;
        }

        details summary {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #60a5fa;
            font-size: 0.75rem;
            font-weight: 500;
            list-style: none;
        }

        details summary::-webkit-details-marker {
            display: none;
        }

        details summary:hover {
            text-decoration: underline;
        }

        details pre {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.67rem;
            overflow: auto;
            max-height: 160px;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .dark details pre {
            background: #0f172a;
            border: 1px solid #1e293b;
            color: #94a3b8;
        }

        html:not(.dark) details pre {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        nav[aria-label="Pagination"] {
            display: flex;
            gap: 3px;
            align-items: center;
            flex-wrap: wrap;
        }

        nav[aria-label="Pagination"] a,
        nav[aria-label="Pagination"] span {
            border-radius: 6px !important;
            font-size: 0.72rem !important;
            padding: 5px 9px !important;
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            transition: all 0.15s;
        }

        .dark nav[aria-label="Pagination"] a,
        .dark nav[aria-label="Pagination"] span {
            background: #0f172a !important;
            border: 1px solid #1e293b !important;
            color: #64748b !important;
        }

        .dark nav[aria-label="Pagination"] span[aria-current] {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }

        .dark nav[aria-label="Pagination"] a:hover {
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        html:not(.dark) nav[aria-label="Pagination"] a,
        html:not(.dark) nav[aria-label="Pagination"] span {
            background: white !important;
            border: 1px solid #e2e8f0 !important;
            color: #64748b !important;
        }

        html:not(.dark) nav[aria-label="Pagination"] span[aria-current] {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #2563eb !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }

        html:not(.dark) nav[aria-label="Pagination"] a:hover {
            background: #f1f5f9 !important;
        }

        .progress-fill {
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.35s ease-out;
        }

        .truncate-path {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 180px;
        }

        @media(min-width:768px) {
            .truncate-path {
                max-width: 280px;
            }
        }

        @media(min-width:1280px) {
            .truncate-path {
                max-width: 360px;
            }
        }

        /* ── Notification Panel ── */
        @keyframes notifSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .notif-panel {
            animation: notifSlideIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes notifItemOut {
            0% {
                opacity: 1;
                transform: translateX(0);
                max-height: 100px;
                margin-bottom: 6px;
            }

            60% {
                opacity: 0;
                transform: translateX(18px);
            }

            100% {
                opacity: 0;
                max-height: 0;
                margin-bottom: 0;
                padding: 0;
                overflow: hidden;
            }
        }

        .notif-item-removing {
            animation: notifItemOut 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .notif-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 8px;
            transition: background 0.15s;
            border: 1px solid transparent;
            margin-bottom: 6px;
            position: relative;
        }

        .dark .notif-item {
            border-color: #1e293b;
            background: rgba(15, 23, 42, 0.6);
        }

        .dark .notif-item:hover {
            background: rgba(30, 41, 59, 0.8);
            border-color: #334155;
        }

        html:not(.dark) .notif-item {
            border-color: #e2e8f0;
            background: #f8fafc;
        }

        html:not(.dark) .notif-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .notif-severity-bar {
            width: 3px;
            border-radius: 99px;
            flex-shrink: 0;
            align-self: stretch;
            min-height: 32px;
        }

        .notif-mark-btn {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            opacity: 0.5;
        }

        .notif-item:hover .notif-mark-btn {
            opacity: 1;
        }

        .dark .notif-mark-btn {
            background: #1e293b;
            color: #94a3b8;
        }

        .dark .notif-mark-btn:hover {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }

        html:not(.dark) .notif-mark-btn {
            background: #e2e8f0;
            color: #64748b;
        }

        html:not(.dark) .notif-mark-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .notif-badge-counter {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 99px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .dark .notif-badge-counter {
            border-color: #020617;
        }

        html:not(.dark) .notif-badge-counter {
            border-color: white;
        }

        .notif-badge-counter.bump {
            transform: scale(1.35);
        }

        .notif-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 32px 16px;
        }

        .notif-empty svg {
            opacity: 0.25;
        }

        .notif-empty p {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
        }

        /* Mark-all button */
        .mark-all-btn {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 5px;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.15s;
        }

        .dark .mark-all-btn {
            border-color: #1e293b;
            color: #64748b;
            background: transparent;
        }

        .dark .mark-all-btn:hover {
            border-color: #3b82f6;
            color: #60a5fa;
            background: rgba(59, 130, 246, 0.08);
        }

        html:not(.dark) .mark-all-btn {
            border-color: #e2e8f0;
            color: #64748b;
            background: transparent;
        }

        html:not(.dark) .mark-all-btn:hover {
            border-color: #3b82f6;
            color: #2563eb;
            background: rgba(59, 130, 246, 0.05);
        }

        /* Severity dot */
        .sev-dot {
            width: 7px;
            height: 7px;
            border-radius: 99px;
            flex-shrink: 0;
            margin-top: 5px;
        }
    </style>
    @stack('head')
</head>

<body
    class="dark:bg-slate-950 bg-slate-50 dark:text-slate-200 text-slate-800 min-h-screen flex transition-colors duration-200">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/60 z-40 lg:hidden">
    </div>

    <!-- Sidebar -->
    <aside
        class="fixed lg:sticky top-0 left-0 h-screen w-60 lg:w-56 z-50 flex flex-col
              dark:bg-slate-900 bg-white dark:border-slate-800 border-slate-200 border-r
              transition-transform duration-250 ease-in-out
              -translate-x-full lg:translate-x-0 flex-shrink-0"
        :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
        style="transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);">

        <!-- Brand -->
        <a href="{{ route('security.dashboard') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 px-5 py-4 dark:border-slate-800 border-slate-200 border-b no-underline group flex-shrink-0">
            <div
                class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-blue-600 to-blue-800 shadow-lg shadow-blue-900/30">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold dark:text-slate-100 text-slate-800 leading-tight tracking-tight truncate">
                    {{ config('app.name') }}</div>
                <div class="dark:text-slate-500 text-slate-400 uppercase mt-0.5"
                    style="font-size:0.58rem;letter-spacing:0.12em;">Security Platform</div>
            </div>
        </a>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-0.5">
            <div class="px-2 pb-2 dark:text-slate-600 text-slate-400 uppercase font-semibold"
                style="font-size:0.6rem;letter-spacing:0.14em;">Monitoring</div>

            @php
                $navItems = [
                    [
                        'route' => 'security.dashboard',
                        'label' => 'Dashboard',
                        'icon' =>
                            '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
                    ],
                    [
                        'route' => 'security.page-access',
                        'label' => 'Access Logs',
                        'icon' =>
                            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
                    ],
                    [
                        'route' => 'security.events',
                        'label' => 'Security Events',
                        'icon' =>
                            '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                    ],
                    [
                        'route' => 'security.audit',
                        'label' => 'Audit Trail',
                        'icon' =>
                            '<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
                    ],
                ];
            @endphp

            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 no-underline
                  {{ request()->routeIs($item['route']) ? 'nav-active dark:text-blue-400 text-blue-600' : 'dark:text-slate-400 text-slate-500 dark:hover:text-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 hover:bg-slate-100' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="{{ request()->routeIs($item['route']) ? 'dark:text-blue-400 text-blue-600' : 'dark:text-slate-600 text-slate-400' }} flex-shrink-0">
                        {!! $item['icon'] !!}
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="px-2 pt-4 pb-2 dark:text-slate-600 text-slate-400 uppercase font-semibold"
                style="font-size:0.6rem;letter-spacing:0.14em;">Reports</div>

            <a href="{{ route('security.export-logs') }}" @click="sidebarOpen = false"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 no-underline
                  {{ request()->routeIs('security.export-logs') ? 'nav-active dark:text-blue-400 text-blue-600' : 'dark:text-slate-400 text-slate-500 dark:hover:text-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 hover:bg-slate-100' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="{{ request()->routeIs('security.export-logs') ? 'dark:text-blue-400 text-blue-600' : 'dark:text-slate-600 text-slate-400' }} flex-shrink-0">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Export Logs
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-4 py-3 dark:border-slate-800 border-slate-200 border-t flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 status-pulse flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-emerald-500 truncate">All Systems Operational</div>
                    <div class="text-xs dark:text-slate-600 text-slate-400">Monitoring active</div>
                </div>
                <button @click="darkMode = !darkMode"
                    class="w-8 h-8 rounded-lg dark:bg-slate-800 bg-slate-100 flex items-center justify-center dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all flex-shrink-0">
                    <svg x-show="darkMode" width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <svg x-show="!darkMode" x-cloak width="13" height="13" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Topbar -->
        <header
            class="h-14 dark:bg-slate-950/90 bg-white/90 backdrop-blur-md dark:border-slate-800 border-slate-200 border-b flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30 flex-shrink-0 transition-colors duration-200">
            <div class="flex items-center gap-3">
                <!-- Hamburger -->
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden w-9 h-9 rounded-lg dark:bg-slate-800 bg-slate-100 flex items-center justify-center dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all flex-shrink-0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <div class="flex items-center gap-1.5 sm:gap-2 text-sm min-w-0">
                    <a href="{{ route('security.dashboard') }}"
                        class="hidden sm:block dark:text-slate-500 text-slate-400 dark:hover:text-slate-300 hover:text-slate-600 no-underline transition-colors whitespace-nowrap">{{ config('app.name') }}</a>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="hidden sm:block dark:text-slate-700 text-slate-300 flex-shrink-0">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                    <span class="dark:text-slate-300 text-slate-600 font-medium truncate">@yield('title', 'Dashboard')</span>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">

                @php
                    $unreadCount = \Illuminate\Support\Facades\DB::table('security_notifications')
                        ->where('is_read', false)
                        ->count();
                    $latestNotifs = \Illuminate\Support\Facades\DB::table('security_notifications')
                        ->where('is_read', false)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

                    /* map severity → colour tokens */
                    $sevColor = [
                        'critical' => ['bar' => '#f87171', 'badge' => 'badge-danger', 'dot' => 'background:#f87171'],
                        'high' => ['bar' => '#fb923c', 'badge' => 'badge-orange', 'dot' => 'background:#fb923c'],
                        'medium' => ['bar' => '#fbbf24', 'badge' => 'badge-warning', 'dot' => 'background:#fbbf24'],
                        'low' => ['bar' => '#94a3b8', 'badge' => 'badge-gray', 'dot' => 'background:#94a3b8'],
                    ];
                @endphp

                {{-- ── Notification Bell ── --}}
                <div x-data="{
                    open: false,
                    count: {{ $unreadCount }},
                    loading: false,
                
                    /* Remove one item by id */
                    markOne(id) {
                        const item = document.getElementById('notif-item-' + id);
                        if (!item) return;
                
                        this.loading = true;
                        fetch('{{ url('/security/notifications') }}/' + id + '/mark-read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.ok ? r.json() : Promise.reject())
                            .then(() => {
                                /* slide-out animation then remove */
                                item.classList.add('notif-item-removing');
                                item.addEventListener('animationend', () => {
                                    item.remove();
                                    this.count = Math.max(0, this.count - 1);
                                    this.refreshBadge();
                                }, { once: true });
                            })
                            .catch(() => {})
                            .finally(() => { this.loading = false; });
                    },
                
                    /* Mark all visible */
                    markAll() {
                        const items = document.querySelectorAll('[id^=notif-item-]');
                        if (!items.length) return;
                
                        const ids = [...items].map(el => el.dataset.notifId);
                        this.loading = true;
                
                        fetch('{{ url('/security/notifications/mark-all-read') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ ids })
                            })
                            .then(r => r.ok ? r.json() : Promise.reject())
                            .then(() => {
                                let delay = 0;
                                items.forEach(el => {
                                    setTimeout(() => {
                                        el.classList.add('notif-item-removing');
                                        el.addEventListener('animationend', () => el.remove(), { once: true });
                                    }, delay);
                                    delay += 60;
                                });
                                setTimeout(() => { this.count = 0;
                                    this.refreshBadge(); }, delay + 100);
                            })
                            .catch(() => {})
                            .finally(() => { this.loading = false; });
                    },
                
                    refreshBadge() {
                        const badge = document.getElementById('notif-badge');
                        if (!badge) return;
                        if (this.count <= 0) {
                            badge.style.display = 'none';
                        } else {
                            badge.textContent = this.count;
                            badge.style.display = 'flex';
                            /* micro bounce */
                            badge.classList.add('bump');
                            setTimeout(() => badge.classList.remove('bump'), 200);
                        }
                        /* show empty state if no items left */
                        const list = document.getElementById('notif-list');
                        const empty = document.getElementById('notif-empty');
                        if (list && empty && list.querySelectorAll('[id^=notif-item-]').length === 0) {
                            list.style.display = 'none';
                            empty.style.display = 'flex';
                        }
                    }
                }" class="relative">

                    {{-- Bell Button --}}
                    <button @click="open = !open"
                        class="relative w-9 h-9 rounded-lg dark:bg-slate-800 bg-slate-100 flex items-center justify-center dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                        </svg>
                        {{-- Badge --}}
                        <span id="notif-badge" class="notif-badge-counter"
                            style="{{ $unreadCount === 0 ? 'display:none' : '' }}">{{ $unreadCount }}</span>
                    </button>

                    {{-- Notification Panel --}}
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="notif-panel absolute right-0 mt-2 z-50" style="width:380px; top:100%;">

                        <div
                            class="dark:bg-slate-900 bg-white rounded-xl border dark:border-slate-800 border-slate-200 shadow-2xl shadow-black/30 overflow-hidden">

                            {{-- Panel Header --}}
                            <div
                                class="flex items-center justify-between px-4 py-3 dark:border-slate-800 border-slate-200 border-b">
                                <div class="flex items-center gap-2">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="dark:text-slate-500 text-slate-400">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                    <span
                                        class="text-sm font-semibold dark:text-slate-200 text-slate-700">Notifications</span>
                                    <span x-show="count > 0" class="badge badge-blue"
                                        x-text="count + ' unread'"></span>

                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($unreadCount > 0)
                                        <button @click="markAll()" :disabled="loading" class="mark-all-btn">
                                            Mark all read
                                        </button>
                                    @endif
                                    {{-- <a href="{{ route('security.notifications') }}"
                                   class="text-xs font-medium text-blue-500 hover:text-blue-400 transition-colors">
                                    View all
                                </a> --}}
                                </div>
                            </div>

                            {{-- Notification List --}}
                            <div class="p-3">

                                @if ($latestNotifs->isEmpty())
                                    {{-- Empty State --}}
                                    <div class="notif-empty" style="display:flex;">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="dark:text-slate-600 text-slate-400">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                            <line x1="2" y1="2" x2="22" y2="22"
                                                stroke-width="1.5" />
                                        </svg>
                                        <p>You're all caught up!</p>
                                    </div>
                                @else
                                    {{-- Items --}}
                                    <div id="notif-list" class="flex flex-col overflow-y-auto"
                                        style="max-height:320px;">
                                        @foreach ($latestNotifs as $n)
                                            @php
                                                $sev = $sevColor[$n->severity] ?? $sevColor['low'];
                                            @endphp
                                            <div id="notif-item-{{ $n->id }}"
                                                data-notif-id="{{ $n->id }}" class="notif-item">

                                                {{-- Severity bar --}}
                                                <div class="notif-severity-bar"
                                                    style="background:{{ $sev['bar'] }};"></div>

                                                {{-- Icon area --}}
                                                <div class="flex-shrink-0 mt-0.5">
                                                    @if ($n->severity === 'critical')
                                                        <svg width="13" height="13" viewBox="0 0 24 24"
                                                            fill="none" stroke="#f87171" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <polygon
                                                                points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2" />
                                                            <line x1="12" y1="8" x2="12"
                                                                y2="12" />
                                                            <line x1="12" y1="16" x2="12.01"
                                                                y2="16" />
                                                        </svg>
                                                    @elseif($n->severity === 'high')
                                                        <svg width="13" height="13" viewBox="0 0 24 24"
                                                            fill="none" stroke="#fb923c" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                            <line x1="12" y1="9" x2="12"
                                                                y2="13" />
                                                            <line x1="12" y1="17" x2="12.01"
                                                                y2="17" />
                                                        </svg>
                                                    @elseif($n->severity === 'medium')
                                                        <svg width="13" height="13" viewBox="0 0 24 24"
                                                            fill="none" stroke="#fbbf24" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="12" y1="8" x2="12"
                                                                y2="12" />
                                                            <line x1="12" y1="16" x2="12.01"
                                                                y2="16" />
                                                        </svg>
                                                    @else
                                                        <svg width="13" height="13" viewBox="0 0 24 24"
                                                            fill="none" stroke="#94a3b8" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="12" y1="16" x2="12"
                                                                y2="12" />
                                                            <line x1="12" y1="8" x2="12.01"
                                                                y2="8" />
                                                        </svg>
                                                    @endif
                                                </div>

                                                {{-- Content --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 mb-0.5">
                                                        <span
                                                            class="badge {{ $sev['badge'] }}">{{ ucfirst($n->severity) }}</span>
                                                    </div>
                                                    <div
                                                        class="text-xs font-semibold dark:text-slate-200 text-slate-700 leading-snug truncate">
                                                        {{ $n->title }}
                                                    </div>
                                                    <div class="text-xs dark:text-slate-500 text-slate-500 mt-0.5 leading-relaxed"
                                                        style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                        {{ \Illuminate\Support\Str::limit($n->message ?? ($n->details ?? ''), 110) }}
                                                    </div>
                                                    <div class="flex items-center gap-1 mt-1.5">
                                                        <svg width="10" height="10" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            class="dark:text-slate-600 text-slate-400 flex-shrink-0">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <polyline points="12 6 12 12 16 14" />
                                                        </svg>
                                                        <span class="font-mono dark:text-slate-600 text-slate-400"
                                                            style="font-size:0.65rem;">
                                                            {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Mark Read Button --}}
                                                <button @click="markOne({{ $n->id }})" :disabled="loading"
                                                    title="Mark as read" class="notif-mark-btn">
                                                    <svg width="12" height="12" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2.5"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12" />
                                                    </svg>
                                                </button>

                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Empty state (hidden until all marked) --}}
                                    <div id="notif-empty" class="notif-empty" style="display:none;">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="dark:text-slate-600 text-slate-400">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                            <line x1="2" y1="2" x2="22" y2="22"
                                                stroke-width="1.5" />
                                        </svg>
                                        <p>You're all caught up!</p>
                                    </div>
                                @endif

                            </div>

                            {{-- Panel Footer --}}
                            <div
                                class="px-4 py-2.5 dark:border-slate-800 border-slate-200 border-t dark:bg-slate-950/40 bg-slate-50 flex items-center justify-between">
                                <span class="dark:text-slate-600 text-slate-400" style="font-size:0.68rem;">Last 5
                                    unread shown</span>
                                <a href="{{ route('security.notifications') }}"
                                    class="text-xs font-medium text-blue-500 hover:text-blue-400 transition-colors flex items-center gap-1">
                                    All notifications
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5">
                                        <polyline points="9 18 15 12 9 6" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
                {{-- ── End Notification Bell ── --}}

                <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-md dark:bg-emerald-500/10 bg-emerald-50 border dark:border-emerald-500/20 border-emerald-200">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse-slow"></div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Live</span>
                </div>
                <div id="topbar-clock"
                    class="hidden md:block font-mono text-xs dark:text-slate-600 text-slate-400 tabular-nums">—</div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 sm:p-5 lg:p-6 animate-fade-in min-w-0">
            @yield('content')
        </main>
    </div>

    <script>
        function updateClock() {
            const el = document.getElementById('topbar-clock');
            if (el) {
                const now = new Date();
                el.textContent = now.toLocaleString('id-ID', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
    @stack('scripts')
</body>

</html>
