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
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                            </td>
                            <td>{{ $project->client->name }}</td>
                            <td>{{ $project->team?->name ?? 'Belum ditugaskan' }}</td>
                            <td>{{ $project->currentTask()?->stage->label() ?? 'Belum ada tahap' }}</td>
                            <td class="nowrap">{{ $project->due_date->format('d M Y') }}</td>
                            <td class="nowrap">{{ $project->progress() }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
