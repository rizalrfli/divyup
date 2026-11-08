@extends('layouts.app')

@section('title', 'Rekap Bulanan')
@section('heading', 'Rekap bulanan')
@section('subheading', 'Daftar proyek yang tenggatnya jatuh pada bulan terpilih.')

@section('nav')
    @include('partials.nav-admin')
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('admin.report') }}" class="form filter-row">
            <div class="field">
                <label for="bulan">Bulan</label>
                <input id="bulan" name="bulan" type="month" value="{{ $bulan }}">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-ghost">Tampilkan</button>
                <a class="btn btn-primary" href="{{ route('admin.report.download', ['bulan' => $bulan]) }}">
                    Unduh CSV
                </a>
            </div>
        </form>
    </div>

    <div class="panel">
        <h2 class="panel-title">Proyek pada {{ $bulan }}</h2>

        @if ($projects->isEmpty())
            <p class="empty">Tidak ada proyek dengan tenggat pada bulan ini.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Proyek</th>
                        <th scope="col">Klien</th>
                        <th scope="col">Tim</th>
                        <th scope="col">Tahap</th>
                        <th scope="col">Progres</th>
                        <th scope="col">Tenggat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->client->name }}</td>
                            <td>{{ $project->team?->name ?? '-' }}</td>
                            <td>{{ $project->stageLabel() }}</td>
                            <td class="nowrap">{{ $project->progress() }}%</td>
                            <td class="nowrap">{{ $project->due_date->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
