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
            border: 1px solid rgba(59, 131, 246, 0.42);
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
    @include('mixu-sso-auth::partials.sidbar')

    <!-- Main Area -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Topbar -->
        @include('mixu-sso-auth::partials.header')

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
