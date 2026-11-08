@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('heading', 'Log aktivitas')
@section('subheading', 'Riwayat perubahan tahap dan unggahan aset lintas proyek.')

@section('nav')
    @include('partials.nav-admin')
@endsection

@section('content')
    <div class="panel">
        @if ($logs->isEmpty())
            <p class="empty">Belum ada aktivitas.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Proyek</th>
                        <th scope="col">Pelaku</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $log->project?->name ?? '-' }}</td>
                            <td>{{ $log->actor?->name ?? 'Sistem' }}</td>
                            <td>{{ $log->action === 'stage.updated' ? 'Perubahan tahap' : $log->action }}</td>
                            <td>{{ $log->detail ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
