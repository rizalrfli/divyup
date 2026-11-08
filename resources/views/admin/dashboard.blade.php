@extends('layouts.app')

@section('title', 'Dasbor')
@section('heading', 'Dasbor')
@section('subheading', 'Ringkasan seluruh proyek klien.')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}" aria-current="page">Dasbor</a>
    <a class="rail-link" href="{{ route('admin.projects.index') }}">Proyek</a>
    <a class="rail-link" href="{{ route('admin.users') }}">Pengguna</a>
    <a class="rail-link" href="{{ route('admin.report') }}">Rekap</a>
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ route('admin.projects.create') }}">Proyek baru</a>
@endsection

@section('content')
    <section class="stat-row" aria-label="Ringkasan angka">
        @foreach ($stats as $label => $value)
            <div class="stat">
                <p class="stat-label">{{ $label }}</p>
                <p class="stat-value">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <div class="panel">
        <h2 class="panel-title">Seluruh proyek</h2>

        @if ($projects->isEmpty())
            <p class="empty">Belum ada proyek. Mulai dengan tombol Proyek baru.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Proyek</th>
                        <th scope="col">Klien</th>
                        <th scope="col">Tim</th>
                        <th scope="col">Tahap</th>
                        <th scope="col">Tenggat</th>
                        <th scope="col">Progres</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></td>
                            <td>{{ $project->client->name }}</td>
                            <td>{{ $project->team?->name ?? 'Belum ditugaskan' }}</td>
                            <td>{{ $project->currentTask()?->stage->label() ?? 'Belum ada tahap' }}</td>
                            <td class="nowrap">{{ $project->due_date->format('d M Y') }}</td>
                            <td class="nowrap">{{ $project->progress() }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel">
        <h2 class="panel-title">Aset dalam revisi terakhir</h2>

        @if ($revisi->isEmpty())
            <p class="empty">Tidak ada aset yang sedang direvisi.</p>
        @else
            <ul class="asset-list">
                @foreach ($revisi as $asset)
                    <li class="asset-row">
                        <div class="asset-main">
                            <p class="asset-name">{{ $asset->name }}</p>
                            <p class="asset-meta">{{ $asset->project->name }} · {{ $asset->project->client->name }}</p>
                            @if ($asset->revision_note)
                                <p class="asset-note">{{ $asset->revision_note }}</p>
                            @endif
                        </div>
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.projects.show', $asset->project) }}">Buka proyek</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
