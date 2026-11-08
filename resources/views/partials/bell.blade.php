@php
    $unread = auth()->user()->unreadNotifications->count();
@endphp

<a class="bell" href="{{ route('notifications.index') }}"
   aria-label="Notifikasi{{ $unread > 0 ? ', '.$unread.' belum dibaca' : '' }}">
    <span class="bell-icon" aria-hidden="true">
        <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M10 3a4.5 4.5 0 0 0-4.5 4.5c0 3.2-1 4.2-1.5 4.8h12c-.5-.6-1.5-1.6-1.5-4.8A4.5 4.5 0 0 0 10 3Z"/>
            <path d="M8.2 15.2a1.9 1.9 0 0 0 3.6 0"/>
        </svg>
    </span>
    @if ($unread > 0)
        <span class="bell-count">{{ $unread }}</span>
    @endif
</a>
