@extends('layouts.app')

@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')
@section('subheading', 'Pembaruan proyek yang berkaitan dengan akun Anda.')

@section('nav')
    @include('partials.nav-' . auth()->user()->role->value)
@endsection

@section('content')
    <div class="panel">
        @if ($notifications->isEmpty())
            <p class="empty">Belum ada notifikasi.</p>
        @else
            <ul class="notif-list">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = $notification->read_at === null;
                    @endphp
                    <li class="notif-item {{ $isUnread ? 'is-unread' : '' }}">
                        <div class="notif-body">
                            <p class="notif-title">{{ $data['title'] ?? 'Pembaruan' }}</p>
                            <p class="notif-text">{{ $data['body'] ?? '' }}</p>
                            <p class="notif-meta">{{ $notification->created_at->format('d M Y H:i') }}</p>
                        </div>

                        <div class="notif-actions">
                            @if (! empty($data['url']))
                                <a class="btn btn-ghost btn-sm" href="{{ $data['url'] }}">Buka</a>
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-sm">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
