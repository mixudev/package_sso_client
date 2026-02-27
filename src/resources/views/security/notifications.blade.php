@extends('mixu-sso-auth::layouts.app')

@section('title', 'Security Notifications')

@section('content')
<div class="rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 p-4 sm:p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Security Notifications</h2>
        <button id="mark-all" class="btn btn-sm badge badge-gray">Mark all as read</button>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Severity</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                <tr>
                    <td class="font-mono">{{ $n->created_at }}</td>
                    <td>
                        <span class="badge {{ $n->severity === 'critical' ? 'badge-danger' : ($n->severity === 'high' ? 'badge-orange' : ($n->severity === 'medium' ? 'badge-warning' : 'badge-gray')) }}">{{ ucfirst($n->severity) }}</span>
                    </td>
                    <td>{{ $n->title ?? ucfirst(str_replace('_',' ',$n->event_type)) }}</td>
                    <td><details><summary>View</summary><pre>{{ $n->message ?? $n->details }}</pre></details></td>
                    <td class="font-mono">
                        @if(!$n->is_read)
                        <button class="mark-read" data-id="{{ $n->id }}">Mark as read</button>
                        @else
                        <span class="text-xs text-slate-500">Viewed</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-xs text-slate-500">No notifications</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>

@push('head')
<script>
document.addEventListener('click', function(e){
    if(e.target.matches('.mark-read')){
        const id = e.target.dataset.id;
        fetch('{{ route('security.notifications.mark-read', ['id' => '__ID__']) }}'.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept':'application/json' }
        }).then(r=>r.json()).then(j=>{ if(j.success) e.target.closest('tr').remove(); });
    }
    if(e.target.matches('#mark-all')){
        fetch('{{ route('security.notifications.mark-all') }}', { method:'POST', headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}' }})
            .then(()=> location.reload());
    }
});
</script>
@endpush

@endsection
