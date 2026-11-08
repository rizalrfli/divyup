@extends('layouts.app')

@section('title', 'Proyek')
@section('heading', 'Proyek')
@section('subheading', 'Seluruh proyek klien beserta tahap berjalan.')

@section('nav')
    @include('partials.nav-admin')
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ route('admin.projects.create') }}">Proyek baru</a>
@endsection

@section('content')
    @if ($projects->isEmpty())
        <div class="panel">
            <p class="empty">Belum ada proyek. Mulai dengan tombol Proyek baru.</p>
        </div>
    @else
        <div class="panel panel-flush">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Proyek</th>
                        <th scope="col">Klien</th>
                        <th scope="col">Tim</th>
                        <th scope="col">Tahap</th>
                        <th scope="col">Tenggat</th>
                        <th scope="col">Progres</th>
                        <th scope="col"><span class="sr-only">Tindakan</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td data-label="Proyek" class="cell-title">
                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                            </td>
                            <td data-label="Klien">{{ $project->client->name }}</td>
                            <td data-label="Tim">{{ $project->team?->name ?? 'Belum ditugaskan' }}</td>
                            <td data-label="Tahap">{{ $project->stageLabel() }}</td>
                            <td data-label="Tenggat" class="nowrap">{{ $project->due_date->format('d M Y') }}</td>
                            <td data-label="Progres" class="nowrap">{{ $project->progress() }}%</td>
                            <td class="cell-action">
                                <a class="btn btn-ghost btn-sm" href="{{ route('admin.projects.edit', $project) }}">Ubah</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
