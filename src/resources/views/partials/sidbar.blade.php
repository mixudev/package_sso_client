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
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
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
                <svg x-show="!darkMode" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
            </button>
        </div>
    </div>
</aside>
