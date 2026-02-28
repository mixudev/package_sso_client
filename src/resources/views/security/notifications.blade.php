@extends('mixu-sso-auth::layouts.app')

@section('title', 'Security Notifications')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* ── Variables ── */
:root {
    --bg:          #0f1117;
    --surface:     #181c27;
    --surface2:    #1e2333;
    --border:      rgba(255,255,255,.07);
    --border-soft: rgba(255,255,255,.04);
    --accent:      #4f8ef7;
    --accent-glow: rgba(79,142,247,.18);
    --text:        #e8eaf0;
    --text-muted:  #6b7280;
    --text-dim:    #9ca3af;

    --critical: #ef4444; --critical-bg: rgba(239,68,68,.1);
    --high:     #f97316; --high-bg:     rgba(249,115,22,.1);
    --medium:   #eab308; --medium-bg:   rgba(234,179,8,.1);
    --low:      #6b7280; --low-bg:      rgba(107,114,128,.1);

    --radius: 10px;
    --shadow: 0 20px 60px rgba(0,0,0,.5);
    --font-sans: 'DM Sans', sans-serif;
    --font-mono: 'JetBrains Mono', monospace;
}
.light-mode {
    --bg:          #f4f6fb;
    --surface:     #ffffff;
    --surface2:    #f0f2f8;
    --border:      rgba(0,0,0,.08);
    --border-soft: rgba(0,0,0,.04);
    --text:        #111827;
    --text-muted:  #6b7280;
    --text-dim:    #9ca3af;
    --accent-glow: rgba(79,142,247,.12);
    --shadow: 0 20px 60px rgba(0,0,0,.12);
}

/* ── Reset / Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Page wrapper ── */
.notif-page {
    font-family: var(--font-sans);
    color: var(--text);
    padding: 1.5rem;
    max-width: 1100px;
    margin: 0 auto;
}

/* ── Header ── */
.notif-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}
.notif-header-left h1 {
    font-size: 1.35rem;
    font-weight: 600;
    letter-spacing: -.02em;
    display: flex;
    align-items: center;
    gap: .55rem;
}
.notif-header-left h1 svg { color: var(--accent); flex-shrink: 0; }
.notif-header-left p {
    font-size: .8rem;
    color: var(--text-muted);
    margin-top: .2rem;
    font-weight: 300;
}

.btn-mark-all {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .5rem 1rem;
    font-size: .8rem;
    font-weight: 500;
    font-family: var(--font-sans);
    background: var(--surface2);
    color: var(--text-dim);
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    white-space: nowrap;
}
.btn-mark-all:hover {
    background: var(--accent-glow);
    color: var(--accent);
    border-color: rgba(79,142,247,.35);
}
.btn-mark-all svg { width: 14px; height: 14px; }

/* ── Stats row ── */
.notif-stats {
    display: flex;
    gap: .75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.stat-chip {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .75rem;
    border-radius: 6px;
    font-size: .75rem;
    font-weight: 500;
    border: 1px solid;
    background: var(--low-bg);
    border-color: rgba(107,114,128,.2);
    color: var(--text-dim);
    transition: transform .15s;
}
.stat-chip:hover { transform: translateY(-1px); }
.stat-chip.critical { background: var(--critical-bg); border-color: rgba(239,68,68,.25); color: var(--critical); }
.stat-chip.high     { background: var(--high-bg);     border-color: rgba(249,115,22,.25); color: var(--high); }
.stat-chip.medium   { background: var(--medium-bg);   border-color: rgba(234,179,8,.25);  color: var(--medium); }
.stat-chip .dot     { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

/* ── Table container ── */
.notif-table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

/* ── Table ── */
.notif-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .825rem;
}
.notif-table thead tr {
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
}
.notif-table th {
    padding: .7rem 1rem;
    text-align: left;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-muted);
    white-space: nowrap;
}
.notif-table td {
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--border-soft);
    vertical-align: middle;
}
.notif-table tbody tr:last-child td { border-bottom: none; }
.notif-table tbody tr {
    transition: background .12s;
}
.notif-table tbody tr:hover { background: var(--surface2); }
.notif-table tbody tr.unread td:first-child {
    border-left: 3px solid var(--accent);
}
.notif-table tbody tr.read td:first-child {
    border-left: 3px solid transparent;
}

/* Time */
.td-time {
    font-family: var(--font-mono);
    font-size: .72rem;
    color: var(--text-muted);
    white-space: nowrap;
}
.td-time .date { display: block; color: var(--text-dim); font-size: .68rem; }

/* Badge */
.badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .25rem .6rem;
    border-radius: 5px;
    font-size: .68rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
    border: 1px solid;
}
.badge .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink:0; }
.badge-critical { background: var(--critical-bg); color: var(--critical); border-color: rgba(239,68,68,.3); }
.badge-high     { background: var(--high-bg);     color: var(--high);     border-color: rgba(249,115,22,.3); }
.badge-medium   { background: var(--medium-bg);   color: var(--medium);   border-color: rgba(234,179,8,.3); }
.badge-low      { background: var(--low-bg);       color: var(--low);      border-color: rgba(107,114,128,.3); }

/* Title */
.td-title { font-weight: 500; color: var(--text); }
.td-title .event-type {
    font-size: .72rem;
    color: var(--text-muted);
    font-family: var(--font-mono);
    margin-top: .15rem;
    display: block;
}

/* View detail btn */
.btn-view {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .7rem;
    font-size: .75rem;
    font-weight: 500;
    font-family: var(--font-sans);
    background: transparent;
    color: var(--accent);
    border: 1px solid rgba(79,142,247,.35);
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
}
.btn-view:hover { background: var(--accent-glow); border-color: var(--accent); }
.btn-view svg { width: 13px; height: 13px; }

/* Mark read btn */
.btn-mark-read {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .32rem .65rem;
    font-size: .72rem;
    font-weight: 500;
    font-family: var(--font-sans);
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    white-space: nowrap;
}
.btn-mark-read:hover {
    background: rgba(16,185,129,.08);
    color: #10b981;
    border-color: rgba(16,185,129,.3);
}
.btn-mark-read svg { width: 12px; height: 12px; }

.td-status-read {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .72rem;
    color: var(--text-muted);
}
.td-status-read svg { width: 13px; height: 13px; color: #10b981; }

/* Actions cell */
.td-actions { display: flex; align-items: center; gap: .5rem; }

/* Empty */
.notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3.5rem 1rem;
    color: var(--text-muted);
    gap: .75rem;
}
.notif-empty svg { opacity: .3; }
.notif-empty p { font-size: .85rem; }

/* Pagination */
.notif-pagination {
    padding: 1rem 1rem;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

/* ── MODAL ── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
}
.modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 560px;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: translateY(16px) scale(.97);
    transition: transform .22s cubic-bezier(.34,1.56,.64,1);
}
.modal-overlay.active .modal {
    transform: translateY(0) scale(1);
}
.modal-header {
    padding: 1.25rem 1.4rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}
.modal-header-info { flex: 1; min-width: 0; }
.modal-header-info h3 {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.35;
    color: var(--text);
    word-break: break-word;
}
.modal-header-info .modal-meta {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-top: .45rem;
    flex-wrap: wrap;
}
.modal-meta-time {
    font-family: var(--font-mono);
    font-size: .72rem;
    color: var(--text-muted);
}
.modal-close {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.modal-close:hover { background: var(--critical-bg); color: var(--critical); }

.modal-body {
    padding: 1.25rem 1.4rem;
    overflow-y: auto;
    flex: 1;
}
.modal-section-label {
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-muted);
    margin-bottom: .6rem;
}
.modal-message {
    font-size: .85rem;
    color: var(--text-dim);
    line-height: 1.6;
    margin-bottom: 1.25rem;
}
.modal-details-pre {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    font-family: var(--font-mono);
    font-size: .75rem;
    color: var(--text-dim);
    white-space: pre-wrap;
    word-break: break-all;
    line-height: 1.6;
    max-height: 260px;
    overflow-y: auto;
}

.modal-footer {
    padding: 1rem 1.4rem;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: .65rem;
}
.modal-btn-close {
    padding: .5rem 1.1rem;
    font-size: .82rem;
    font-weight: 500;
    font-family: var(--font-sans);
    background: var(--surface2);
    color: var(--text-dim);
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: background .15s;
}
.modal-btn-close:hover { background: var(--surface); }
.modal-btn-markread {
    padding: .5rem 1.1rem;
    font-size: .82rem;
    font-weight: 500;
    font-family: var(--font-sans);
    background: var(--accent-glow);
    color: var(--accent);
    border: 1px solid rgba(79,142,247,.35);
    border-radius: 8px;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.modal-btn-markread:hover { background: rgba(79,142,247,.25); border-color: var(--accent); }

/* ── Responsive ── */
@media (max-width: 768px) {
    .notif-page { padding: 1rem; }

    /* Hide less-important columns on mobile */
    .col-message, .col-eventtype { display: none; }

    .notif-table th, .notif-table td {
        padding: .7rem .75rem;
    }
    .td-time .date { display: none; }
    .notif-stats { gap: .5rem; }

    .modal { max-height: 92vh; border-radius: 14px 14px 0 0; align-self: flex-end; max-width: 100%; }
    .modal-overlay { align-items: flex-end; padding: 0; }

    .btn-mark-all span { display: none; }
}

/* Scrollbar */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
</style>
@endpush

@section('content')
<div class="notif-page">

    {{-- Header --}}
    <div class="notif-header">
        <div class="notif-header-left">
            <h1>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Security Notifications
            </h1>
            <p>Monitor and manage your account security alerts</p>
        </div>
        <button id="mark-all" class="btn-mark-all">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Mark all as read</span>
        </button>
    </div>

    {{-- Stats chips --}}
    @php
        $counts = $notifications->getCollection()->groupBy('severity')->map->count();
    @endphp
    <div class="notif-stats">
        @if(($counts['critical'] ?? 0) > 0)
        <div class="stat-chip critical"><span class="dot"></span> {{ $counts['critical'] }} Critical</div>
        @endif
        @if(($counts['high'] ?? 0) > 0)
        <div class="stat-chip high"><span class="dot"></span> {{ $counts['high'] }} High</div>
        @endif
        @if(($counts['medium'] ?? 0) > 0)
        <div class="stat-chip medium"><span class="dot"></span> {{ $counts['medium'] }} Medium</div>
        @endif
        <div class="stat-chip"><span class="dot"></span> {{ $notifications->total() }} Total</div>
    </div>

    {{-- Table --}}
    <div class="notif-table-wrap">
        <div class="overflow-x-auto">
            <table class="notif-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Severity</th>
                        <th>Title</th>
                        <th class="col-message">Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $n)
                    <tr class="{{ $n->is_read ? 'read' : 'unread' }}" data-id="{{ $n->id }}">
                        <td class="td-time">
                            {{ \Carbon\Carbon::parse($n->created_at)->format('H:i:s') }}
                            <span class="date">
                                {{ \Carbon\Carbon::parse($n->created_at)->format('d M Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $n->severity === 'critical' ? 'critical' : ($n->severity === 'high' ? 'high' : ($n->severity === 'medium' ? 'medium' : 'low')) }}">
                                <span class="dot"></span>
                                {{ ucfirst($n->severity) }}
                            </span>
                        </td>
                        <td class="td-title">
                            {{ $n->title ?? ucfirst(str_replace('_', ' ', $n->event_type)) }}
                            <span class="event-type col-eventtype">{{ $n->event_type }}</span>
                        </td>
                        <td class="col-message" style="color:var(--text-muted);font-size:.78rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ Str::limit($n->message ?? $n->details, 60) }}
                        </td>
                        <td>
                            <div class="td-actions">
                                <button class="btn-view"
                                    data-id="{{ $n->id }}"
                                    data-title="{{ addslashes($n->title ?? ucfirst(str_replace('_',' ',$n->event_type))) }}"
                                    data-severity="{{ $n->severity }}"
                                    data-time="{{ \Carbon\Carbon::parse($n->created_at)->format('d M Y, H:i:s') }}"
                                    data-event="{{ $n->event_type }}"
                                    data-message="{{ addslashes($n->message ?? '') }}"
                                    data-details="{{ addslashes($n->details ?? '') }}"
                                    data-read="{{ $n->is_read ? '1' : '0' }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Detail
                                </button>
                                @if(!$n->is_read)
                                <button class="btn-mark-read" data-id="{{ $n->id }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Read
                                </button>
                                @else
                                <span class="td-status-read">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Viewed
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="notif-empty">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                <p>No security notifications at this time.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
        <div class="notif-pagination">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Detail Modal ── --}}
<div class="modal-overlay" id="notif-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h3 id="modal-title">Notification Detail</h3>
                <div class="modal-meta">
                    <span id="modal-badge" class="badge"></span>
                    <span id="modal-time" class="modal-meta-time"></span>
                </div>
            </div>
            <button class="modal-close" id="modal-close-btn" aria-label="Close">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <div id="modal-message-wrap" style="margin-bottom:1rem;">
                <div class="modal-section-label">Message</div>
                <p class="modal-message" id="modal-message"></p>
            </div>
            <div id="modal-details-wrap">
                <div class="modal-section-label">Details</div>
                <pre class="modal-details-pre" id="modal-details"></pre>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn-close" id="modal-close-btn2">Close</button>
            <button class="modal-btn-markread" id="modal-mark-read-btn" style="display:none;">
                Mark as Read
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const overlay   = document.getElementById('notif-modal');
    const modalTitle= document.getElementById('modal-title');
    const modalBadge= document.getElementById('modal-badge');
    const modalTime = document.getElementById('modal-time');
    const modalMsg  = document.getElementById('modal-message');
    const modalMsgW = document.getElementById('modal-message-wrap');
    const modalDet  = document.getElementById('modal-details');
    const modalDetW = document.getElementById('modal-details-wrap');
    const modalMRBtn= document.getElementById('modal-mark-read-btn');
    let   activeId  = null;

    const BADGE_MAP = {
        critical: 'badge-critical',
        high:     'badge-high',
        medium:   'badge-medium',
        low:      'badge-low',
    };

    function openModal(btn) {
        const sev     = btn.dataset.severity || 'low';
        const isRead  = btn.dataset.read === '1';
        activeId      = btn.dataset.id;

        modalTitle.textContent = btn.dataset.title;
        modalTime.textContent  = btn.dataset.time;

        modalBadge.className   = 'badge ' + (BADGE_MAP[sev] || 'badge-low');
        modalBadge.innerHTML   = '<span class="dot"></span>' + sev.charAt(0).toUpperCase() + sev.slice(1);

        const msg = btn.dataset.message.trim();
        const det = btn.dataset.details.trim();

        modalMsgW.style.display = msg ? '' : 'none';
        modalMsg.textContent    = msg;

        modalDetW.style.display = det ? '' : 'none';
        modalDet.textContent    = det;

        modalMRBtn.style.display = isRead ? 'none' : '';

        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        activeId = null;
    }

    function markRead(id, rowEl, fromModal = false) {
        fetch('{{ route('security.notifications.mark-read', ['id' => '__ID__']) }}'.replace('__ID__', id), {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(j => {
            if (!j.success) return;
            if (rowEl) {
                // Update row visually
                rowEl.classList.remove('unread');
                rowEl.classList.add('read');
                const actionsCell = rowEl.querySelector('.td-actions');
                if (actionsCell) {
                    const mrBtn = actionsCell.querySelector('.btn-mark-read');
                    if (mrBtn) {
                        const span = document.createElement('span');
                        span.className = 'td-status-read';
                        span.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg> Viewed';
                        mrBtn.replaceWith(span);
                    }
                }
                const viewBtn = rowEl.querySelector('.btn-view');
                if (viewBtn) viewBtn.dataset.read = '1';
            }
            if (fromModal) {
                modalMRBtn.style.display = 'none';
            }
        });
    }

    // Delegated events
    document.addEventListener('click', function(e) {
        // Open modal
        const viewBtn = e.target.closest('.btn-view');
        if (viewBtn) { openModal(viewBtn); return; }

        // Inline mark read
        const mrBtn = e.target.closest('.btn-mark-read');
        if (mrBtn) {
            const row = mrBtn.closest('tr');
            markRead(mrBtn.dataset.id, row);
            return;
        }

        // Modal mark read
        if (e.target === modalMRBtn || modalMRBtn.contains(e.target)) {
            if (!activeId) return;
            const row = document.querySelector('tr[data-id="' + activeId + '"]');
            markRead(activeId, row, true);
            return;
        }

        // Close modal
        if (e.target === overlay ||
            e.target.closest('#modal-close-btn') ||
            e.target.closest('#modal-close-btn2')) {
            closeModal();
            return;
        }

        // Mark all
        if (e.target.closest('#mark-all')) {
            fetch('{{ route('security.notifications.mark-all') }}', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => location.reload());
        }
    });

    // Keyboard close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>
@endpush