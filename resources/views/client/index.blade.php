@extends('layouts.app')

@section('title', 'Portal Klien')
@section('heading', 'Proyek Saya')
@section('subheading', 'Progres layanan digital marketing Anda.')

@section('nav')
    <a class="rail-link" href="{{ route('portal.dashboard') }}" aria-current="page">Proyek Saya</a>
@endsection

@section('content')
    @if ($projects->isEmpty())
        <div class="panel">
            <p class="empty">Belum ada proyek. Hubungi Account Manager DivyUp bila proyek Anda belum muncul.</p>
        </div>
    @else
        <div class="card-grid">
            @foreach ($projects as $project)
                <article class="card">
                    <p class="card-kicker">Tahap saat ini</p>
                    <h2 class="card-title">
                        <a href="{{ route('portal.projects.show', $project) }}">{{ $project->name }}</a>
                    </h2>
                    <dl class="meta-list">
                        <div>
                            <dt>Progres</dt>
                            <dd>{{ $project->progress() }}%</dd>
                        </div>
                        <div>
                            <dt>Target selesai</dt>
                            <dd>{{ $project->due_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt>Menunggu persetujuan</dt>
                            <dd>{{ $project->assets->where('status', \App\Enums\AssetStatus::Menunggu)->count() }} aset</dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>
    @endif
@endsection
