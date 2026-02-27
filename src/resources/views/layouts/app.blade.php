<!DOCTYPE html>
<html lang="en" class="dark"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'light' ? false : true,
          sidebarOpen: false
      }"
      x-init="
          $watch('darkMode', v => { localStorage.setItem('theme', v ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', v); });
          document.documentElement.classList.toggle('dark', darkMode);
      "
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Security Monitoring') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans','sans-serif'], mono: ['JetBrains Mono','monospace'] },
                    colors: { slate: { 950: '#020617' } },
                    animation: { 'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display:none !important; }
        body { font-family:'Plus Jakarta Sans',sans-serif; }
        ::-webkit-scrollbar { width:4px; height:4px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#334155; border-radius:4px; }
        ::-webkit-scrollbar-thumb:hover { background:#475569; }

        .nav-active { background:linear-gradient(90deg,rgba(59,130,246,0.15),rgba(59,130,246,0.04)); border-left:2px solid #3b82f6; }

        @keyframes statusPulse {
            0%,100% { box-shadow:0 0 0 0 rgba(34,197,94,0.4); }
            70% { box-shadow:0 0 0 5px rgba(34,197,94,0); }
        }
        .status-pulse { animation:statusPulse 2.5s ease-in-out infinite; }

        .badge { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;font-size:0.7rem;font-weight:600;letter-spacing:0.03em;white-space:nowrap; }
        .badge-success { background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.2); }
        .badge-danger  { background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2); }
        .badge-warning { background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.2); }
        .badge-blue    { background:rgba(59,130,246,0.12);color:#60a5fa;border:1px solid rgba(59,130,246,0.2); }
        .badge-cyan    { background:rgba(6,182,212,0.12);color:#22d3ee;border:1px solid rgba(6,182,212,0.2); }
        .badge-purple  { background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.2); }
        .badge-orange  { background:rgba(249,115,22,0.12);color:#fb923c;border:1px solid rgba(249,115,22,0.2); }
        .badge-gray    { background:rgba(100,116,139,0.12);color:#94a3b8;border:1px solid rgba(100,116,139,0.2); }
        html:not(.dark) .badge-success { background:rgba(16,185,129,0.1);color:#059669; }
        html:not(.dark) .badge-danger  { background:rgba(239,68,68,0.1);color:#dc2626; }
        html:not(.dark) .badge-blue    { background:rgba(59,130,246,0.1);color:#2563eb; }
        html:not(.dark) .badge-cyan    { background:rgba(6,182,212,0.1);color:#0891b2; }
        html:not(.dark) .badge-purple  { background:rgba(139,92,246,0.1);color:#7c3aed; }
        html:not(.dark) .badge-orange  { background:rgba(249,115,22,0.1);color:#ea580c; }
        html:not(.dark) .badge-warning { background:rgba(245,158,11,0.1);color:#d97706; }
        html:not(.dark) .badge-gray    { background:rgba(100,116,139,0.1);color:#64748b; }

        .data-table { width:100%;border-collapse:collapse;font-size:0.79rem; }
        .data-table th { padding:10px 14px;text-align:left;font-size:0.63rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase; }
        .data-table td { padding:10px 14px;border-top:1px solid; }
        .dark .data-table th { color:#64748b;border-bottom:1px solid #1e293b;background:rgba(15,23,42,0.5); }
        .dark .data-table td { color:#cbd5e1;border-color:#1e293b; }
        .dark .data-table tr:hover td { background:rgba(255,255,255,0.02); }
        html:not(.dark) .data-table th { color:#64748b;border-bottom:1px solid #e2e8f0;background:#f8fafc; }
        html:not(.dark) .data-table td { color:#334155;border-color:#e2e8f0; }
        html:not(.dark) .data-table tr:hover td { background:#f8fafc; }

        details summary { cursor:pointer;display:inline-flex;align-items:center;gap:5px;color:#60a5fa;font-size:0.75rem;font-weight:500;list-style:none; }
        details summary::-webkit-details-marker { display:none; }
        details summary:hover { text-decoration:underline; }
        details pre { margin-top:8px;padding:10px 12px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:0.67rem;overflow:auto;max-height:160px;white-space:pre-wrap;word-break:break-all; }
        .dark details pre { background:#0f172a;border:1px solid #1e293b;color:#94a3b8; }
        html:not(.dark) details pre { background:#f1f5f9;border:1px solid #e2e8f0;color:#475569; }

        nav[aria-label="Pagination"] { display:flex;gap:3px;align-items:center;flex-wrap:wrap; }
        nav[aria-label="Pagination"] a,
        nav[aria-label="Pagination"] span { border-radius:6px !important;font-size:0.72rem !important;padding:5px 9px !important;display:inline-block;font-family:'JetBrains Mono',monospace;transition:all 0.15s; }
        .dark nav[aria-label="Pagination"] a,
        .dark nav[aria-label="Pagination"] span { background:#0f172a !important;border:1px solid #1e293b !important;color:#64748b !important; }
        .dark nav[aria-label="Pagination"] span[aria-current] { background:rgba(59,130,246,0.15) !important;color:#60a5fa !important;border-color:rgba(59,130,246,0.3) !important; }
        .dark nav[aria-label="Pagination"] a:hover { background:#1e293b !important;color:#cbd5e1 !important; }
        html:not(.dark) nav[aria-label="Pagination"] a,
        html:not(.dark) nav[aria-label="Pagination"] span { background:white !important;border:1px solid #e2e8f0 !important;color:#64748b !important; }
        html:not(.dark) nav[aria-label="Pagination"] span[aria-current] { background:rgba(59,130,246,0.1) !important;color:#2563eb !important;border-color:rgba(59,130,246,0.3) !important; }
        html:not(.dark) nav[aria-label="Pagination"] a:hover { background:#f1f5f9 !important; }

        .progress-fill { transition:width 1s cubic-bezier(0.4,0,0.2,1); }

        @keyframes fadeIn {
            from { opacity:0;transform:translateY(6px); }
            to   { opacity:1;transform:translateY(0); }
        }
        .animate-fade-in { animation:fadeIn 0.35s ease-out; }
        .truncate-path { overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px; }
        @media(min-width:768px) { .truncate-path { max-width:280px; } }
        @media(min-width:1280px) { .truncate-path { max-width:360px; } }
    </style>
    @stack('head')
</head>

<body class="dark:bg-slate-950 bg-slate-50 dark:text-slate-200 text-slate-800 min-h-screen flex transition-colors duration-200">

<!-- Mobile Sidebar Backdrop -->
<div x-show="sidebarOpen"
     x-cloak
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/60 z-40 lg:hidden">
</div>

<!-- Sidebar -->
<aside class="fixed lg:sticky top-0 left-0 h-screen w-60 lg:w-56 z-50 flex flex-col
              dark:bg-slate-900 bg-white dark:border-slate-800 border-slate-200 border-r
              transition-transform duration-250 ease-in-out
              -translate-x-full lg:translate-x-0 flex-shrink-0"
       :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
       style="transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);">

    <!-- Brand -->
    <a href="{{ route('security.dashboard') }}"
       @click="sidebarOpen = false"
       class="flex items-center gap-3 px-5 py-4 dark:border-slate-800 border-slate-200 border-b no-underline group flex-shrink-0">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-blue-600 to-blue-800 shadow-lg shadow-blue-900/30">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm font-bold dark:text-slate-100 text-slate-800 leading-tight tracking-tight truncate">{{ config('app.name') }}</div>
            <div class="dark:text-slate-500 text-slate-400 uppercase mt-0.5" style="font-size:0.58rem;letter-spacing:0.12em;">Security Platform</div>
        </div>
    </a>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-0.5">
        <div class="px-2 pb-2 dark:text-slate-600 text-slate-400 uppercase font-semibold" style="font-size:0.6rem;letter-spacing:0.14em;">Monitoring</div>

        @php
            $navItems = [
                ['route'=>'security.dashboard',   'label'=>'Dashboard',       'icon'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>'],
                ['route'=>'security.page-access', 'label'=>'Access Logs',     'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
                ['route'=>'security.events',      'label'=>'Security Events', 'icon'=>'<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
                ['route'=>'security.audit',       'label'=>'Audit Trail',     'icon'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
            ];
        @endphp

        @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}"
           @click="sidebarOpen = false"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 no-underline
                  {{ request()->routeIs($item['route']) ? 'nav-active dark:text-blue-400 text-blue-600' : 'dark:text-slate-400 text-slate-500 dark:hover:text-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 hover:bg-slate-100' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="{{ request()->routeIs($item['route']) ? 'dark:text-blue-400 text-blue-600' : 'dark:text-slate-600 text-slate-400' }} flex-shrink-0">
                {!! $item['icon'] !!}
            </svg>
            {{ $item['label'] }}
        </a>
        @endforeach

        <div class="px-2 pt-4 pb-2 dark:text-slate-600 text-slate-400 uppercase font-semibold" style="font-size:0.6rem;letter-spacing:0.14em;">Reports</div>

        <a href="{{ route('security.export-logs') }}"
           @click="sidebarOpen = false"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 no-underline
                  {{ request()->routeIs('security.export-logs') ? 'nav-active dark:text-blue-400 text-blue-600' : 'dark:text-slate-400 text-slate-500 dark:hover:text-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 hover:bg-slate-100' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="{{ request()->routeIs('security.export-logs') ? 'dark:text-blue-400 text-blue-600' : 'dark:text-slate-600 text-slate-400' }} flex-shrink-0">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
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
                <svg x-show="darkMode" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg x-show="!darkMode" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </div>
</aside>

<!-- Main Area -->
<div class="flex-1 flex flex-col min-w-0">

    <!-- Topbar -->
    <header class="h-14 dark:bg-slate-950/90 bg-white/90 backdrop-blur-md dark:border-slate-800 border-slate-200 border-b flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30 flex-shrink-0 transition-colors duration-200">
        <div class="flex items-center gap-3">
            <!-- Hamburger -->
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden w-9 h-9 rounded-lg dark:bg-slate-800 bg-slate-100 flex items-center justify-center dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="flex items-center gap-1.5 sm:gap-2 text-sm min-w-0">
                <a href="{{ route('security.dashboard') }}" class="hidden sm:block dark:text-slate-500 text-slate-400 dark:hover:text-slate-300 hover:text-slate-600 no-underline transition-colors whitespace-nowrap">{{ config('app.name') }}</a>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden sm:block dark:text-slate-700 text-slate-300 flex-shrink-0"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="dark:text-slate-300 text-slate-600 font-medium truncate">@yield('title', 'Dashboard')</span>
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md dark:bg-emerald-500/10 bg-emerald-50 border dark:border-emerald-500/20 border-emerald-200">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse-slow"></div>
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Live</span>
            </div>
            <div id="topbar-clock" class="hidden md:block font-mono text-xs dark:text-slate-600 text-slate-400 tabular-nums">—</div>
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
        if (el) el.textContent = new Date().toISOString().replace('T',' ').substring(0,19) + ' UTC';
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@stack('scripts')
</body>
</html>