<style>
/* ═══════════════════════════════════════════════
   NOTIFICATION SYSTEM — Professional UI
   ═══════════════════════════════════════════════ */

/* Badge counter */
.notif-badge-counter {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 99px;
    background: #ef4444;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--badge-border, #fff);
    line-height: 1;
    transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s;
    animation: badgeIn 0.35s cubic-bezier(0.34,1.56,0.64,1) both;
}

@keyframes badgeIn {
    0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
    70%  { transform: scale(1.2) rotate(4deg); opacity: 1; }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
}

.notif-badge-counter.bump {
    animation: bump 0.25s cubic-bezier(0.34,1.56,0.64,1);
}

@keyframes bump {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.4); }
    100% { transform: scale(1); }
}

/* Bell button active ring */
.notif-bell-btn {
    position: relative;
    transition: all 0.2s;
}
.notif-bell-btn:hover svg {
    animation: bellShake 0.4s ease;
}
@keyframes bellShake {
    0%,100% { transform: rotate(0deg); }
    20%      { transform: rotate(-15deg); }
    40%      { transform: rotate(12deg); }
    60%      { transform: rotate(-8deg); }
    80%      { transform: rotate(6deg); }
}

/* Panel slide-in */
.notif-panel {
    transform-origin: top right;
    animation: panelIn 0.22s cubic-bezier(0.16,1,0.3,1) both;
}
@keyframes panelIn {
    from { opacity: 0; transform: scale(0.92) translateY(-8px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

/* Individual notification item */
.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 10px 10px 14px;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
    cursor: default;
    transition: background 0.15s;
    animation: itemIn 0.3s cubic-bezier(0.16,1,0.3,1) both;
    margin-bottom: 4px;
}
.notif-item:last-child { margin-bottom: 0; }

@keyframes itemIn {
    from { opacity: 0; transform: translateX(12px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* Stagger children */
.notif-item:nth-child(1) { animation-delay: 0.03s; }
.notif-item:nth-child(2) { animation-delay: 0.07s; }
.notif-item:nth-child(3) { animation-delay: 0.11s; }
.notif-item:nth-child(4) { animation-delay: 0.15s; }
.notif-item:nth-child(5) { animation-delay: 0.19s; }

.dark .notif-item:hover { background: rgba(255,255,255,0.035); }
.notif-item:hover       { background: rgba(0,0,0,0.03); }

/* Severity accent bar */
.notif-severity-bar {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 3px 0 0 3px;
    opacity: 0.85;
}

/* Remove animation */
.notif-item-removing {
    animation: itemOut 0.3s cubic-bezier(0.4,0,1,1) forwards !important;
}
@keyframes itemOut {
    0%   { opacity: 1; transform: translateX(0) scaleY(1); max-height: 120px; margin-bottom: 4px; }
    40%  { opacity: 0; transform: translateX(20px) scaleY(0.8); }
    100% { opacity: 0; transform: translateX(20px) scaleY(0); max-height: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
}

/* Mark read button */
.notif-mark-btn {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.15s, background 0.15s, transform 0.15s;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
}
.notif-item:hover .notif-mark-btn { opacity: 1; }
.dark .notif-mark-btn:hover {
    background: rgba(255,255,255,0.08);
    color: #38bdf8;
    transform: scale(1.1);
}
.notif-mark-btn:hover {
    background: rgba(0,0,0,0.06);
    color: #0ea5e9;
    transform: scale(1.1);
}

/* Mark All button */
.mark-all-btn {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    color: #3b82f6;
    background: rgba(59,130,246,0.1);
    letter-spacing: 0.01em;
}
.mark-all-btn:hover:not(:disabled) {
    background: rgba(59,130,246,0.2);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59,130,246,0.2);
}
.mark-all-btn:active { transform: translateY(0); }
.mark-all-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Badges */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 7px;
    border-radius: 99px;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.badge-danger  { background: rgba(248,113,113,0.15); color: #f87171; }
.badge-orange  { background: rgba(251,146,60,0.15);  color: #fb923c; }
.badge-warning { background: rgba(251,191,36,0.15);  color: #d97706; }
.badge-gray    { background: rgba(148,163,184,0.15); color: #94a3b8; }
.badge-blue    { background: rgba(59,130,246,0.12);  color: #60a5fa; }

/* Empty state */
.notif-empty {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
    gap: 10px;
    animation: fadeUp 0.3s ease both;
}
.notif-empty p {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin: 0;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Scrollbar */
#notif-list::-webkit-scrollbar { width: 4px; }
#notif-list::-webkit-scrollbar-track { background: transparent; }
#notif-list::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.2); border-radius: 4px; }

/* Loading pulse overlay on button */
.mark-all-btn:disabled svg { animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Dark mode badge border */
.dark .notif-badge-counter { --badge-border: #0f172a; }

/* Pulse for live dot */
@keyframes pulse-slow {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.4; }
}
.animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
</style>

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

            $sevColor = [
                'critical' => ['bar' => '#f87171', 'badge' => 'badge-danger'],
                'high'     => ['bar' => '#fb923c', 'badge' => 'badge-orange'],
                'medium'   => ['bar' => '#fbbf24', 'badge' => 'badge-warning'],
                'low'      => ['bar' => '#94a3b8', 'badge' => 'badge-gray'],
            ];
        @endphp

        {{-- ── Notification Bell ── --}}
        <div x-data="{
            open: false,
            count: {{ $unreadCount }},
            loading: false,

            markOne(id) {
                const item = document.getElementById('notif-item-' + id);
                if (!item) return;
                this.loading = true;
                fetch('{{ url('/security/notifications') }}/' + id + '/mark-read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(() => {
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
                    setTimeout(() => { this.count = 0; this.refreshBadge(); }, delay + 100);
                })
                .catch(() => {})
                .finally(() => { this.loading = false; });
            },

            refreshBadge() {
                const badge = document.getElementById('notif-badge');
                if (!badge) return;
                if (this.count <= 0) {
                    badge.style.animation = 'none';
                    badge.style.opacity = '0';
                    badge.style.transform = 'scale(0)';
                    setTimeout(() => { badge.style.display = 'none'; }, 200);
                } else {
                    badge.style.display = 'flex';
                    badge.style.opacity = '1';
                    badge.style.transform = 'scale(1)';
                    badge.textContent = this.count;
                    badge.classList.add('bump');
                    setTimeout(() => badge.classList.remove('bump'), 250);
                }
                const list  = document.getElementById('notif-list');
                const empty = document.getElementById('notif-empty');
                if (list && empty && list.querySelectorAll('[id^=notif-item-]').length === 0) {
                    list.style.display  = 'none';
                    empty.style.display = 'flex';
                }
            }
        }" class="relative">

            {{-- Bell Button --}}
            <button @click="open = !open"
                class="notif-bell-btn relative w-9 h-9 rounded-lg dark:bg-slate-800 bg-slate-100 flex items-center justify-center dark:text-slate-400 text-slate-500 dark:hover:bg-slate-700 hover:bg-slate-200 transition-all flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                </svg>
                <span id="notif-badge" class="notif-badge-counter"
                    style="{{ $unreadCount === 0 ? 'display:none;opacity:0;transform:scale(0)' : '' }}">{{ $unreadCount }}</span>
            </button>

            {{-- Notification Panel --}}
            <div x-show="open" x-cloak @click.outside="open = false"
                class="notif-panel absolute right-0 mt-2 z-50" style="width:380px; top:100%;">

                <div class="dark:bg-slate-900 bg-white rounded-xl border dark:border-slate-800 border-slate-200 shadow-2xl shadow-black/30 overflow-hidden">

                    {{-- Panel Header --}}
                    <div class="flex items-center justify-between px-4 py-3 dark:border-slate-800 border-slate-200 border-b">
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="dark:text-slate-500 text-slate-400">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span class="text-sm font-semibold dark:text-slate-200 text-slate-700">Notifications</span>
                            <span x-show="count > 0"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-75"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-75"
                                class="badge badge-blue"
                                x-text="count + ' unread'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($unreadCount > 0)
                                <button @click="markAll()" :disabled="loading" class="mark-all-btn"
                                    x-bind:class="loading ? 'opacity-50 cursor-not-allowed' : ''">
                                    <span x-show="!loading">Mark all read</span>
                                    <span x-show="loading" class="flex items-center gap-1">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="animate-spin">
                                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                                        </svg>
                                        Processing…
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Notification List --}}
                    <div class="p-3">

                        @if ($latestNotifs->isEmpty())
                            <div class="notif-empty" style="display:flex;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" class="dark:text-slate-600 text-slate-400">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    <line x1="2" y1="2" x2="22" y2="22" stroke-width="1.5" />
                                </svg>
                                <p>You're all caught up!</p>
                            </div>
                        @else
                            <div id="notif-list" class="flex flex-col overflow-y-auto" style="max-height:320px;">
                                @foreach ($latestNotifs as $n)
                                    @php $sev = $sevColor[$n->severity] ?? $sevColor['low']; @endphp

                                    <div id="notif-item-{{ $n->id }}"
                                        data-notif-id="{{ $n->id }}"
                                        class="notif-item group">

                                        {{-- Severity bar --}}
                                        <div class="notif-severity-bar" style="background:{{ $sev['bar'] }};"></div>

                                        {{-- Severity Icon --}}
                                        <div class="flex-shrink-0 mt-0.5">
                                            @if ($n->severity === 'critical')
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                </svg>
                                            @elseif($n->severity === 'high')
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fb923c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                                </svg>
                                            @elseif($n->severity === 'medium')
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                </svg>
                                            @else
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="badge {{ $sev['badge'] }}">{{ ucfirst($n->severity) }}</span>
                                            </div>
                                            <div class="text-xs font-semibold dark:text-slate-200 text-slate-700 leading-snug truncate">
                                                {{ $n->title }}
                                            </div>
                                            <div class="text-xs dark:text-slate-500 text-slate-500 mt-0.5 leading-relaxed"
                                                style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                {{ \Illuminate\Support\Str::limit($n->message ?? ($n->details ?? ''), 110) }}
                                            </div>
                                            <div class="flex items-center gap-1 mt-1.5">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dark:text-slate-600 text-slate-400 flex-shrink-0">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                <span class="font-mono dark:text-slate-600 text-slate-400" style="font-size:0.65rem;">
                                                    {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Mark Read Button --}}
                                        <button @click="markOne({{ $n->id }})" :disabled="loading"
                                            title="Mark as read" class="notif-mark-btn">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </button>

                                    </div>
                                @endforeach
                            </div>

                            {{-- Empty state (revealed after all cleared) --}}
                            <div id="notif-empty" class="notif-empty" style="display:none;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" class="dark:text-slate-600 text-slate-400">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    <line x1="2" y1="2" x2="22" y2="22" stroke-width="1.5"/>
                                </svg>
                                <p>You're all caught up!</p>
                            </div>
                        @endif

                    </div>

                    {{-- Panel Footer --}}
                    <div class="px-4 py-2.5 dark:border-slate-800 border-slate-200 border-t dark:bg-slate-950/40 bg-slate-50 flex items-center justify-between">
                        <span class="dark:text-slate-600 text-slate-400" style="font-size:0.68rem;">Last 5 unread shown</span>
                        <a href="{{ route('security.notifications') }}"
                            class="text-xs font-medium text-blue-500 hover:text-blue-400 transition-colors flex items-center gap-1 group">
                            All notifications
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                class="transition-transform group-hover:translate-x-0.5">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </div>
        {{-- ── End Notification Bell ── --}}

        <div class="flex items-center gap-1.5 px-2 py-1 rounded-md dark:bg-emerald-500/10 bg-emerald-50 border dark:border-emerald-500/20 border-emerald-200">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse-slow"></div>
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Live</span>
        </div>
        <div id="topbar-clock"
            class="hidden md:block font-mono text-xs dark:text-slate-600 text-slate-400 tabular-nums">—</div>
    </div>
</header>