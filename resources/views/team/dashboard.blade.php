@extends('layouts.app')

@section('title', 'Tugas Saya')
@section('heading', 'Tugas Saya')
@section('subheading', 'Proyek yang ditugaskan kepada Anda, urut tenggat.')

@section('nav')
    <a class="rail-link" href="{{ route('tim.dashboard') }}" aria-current="page">Tugas Saya</a>
@endsection

@section('content')
    @if ($projects->isEmpty())
        <div class="panel">
            <p class="empty">Belum ada tugas. Proyek baru akan muncul di sini setelah ditugaskan.</p>
        </div>
    @else
        <div class="card-grid">
            @foreach ($projects as $project)
                @php
                    $overdue = $project->progress() < 100 && $project->due_date->isPast();
                @endphp
                <article class="card">
                    <p class="card-kicker">{{ $project->client->name }}</p>
                    <h2 class="card-title">
                        <a href="{{ route('tim.projects.show', $project) }}">{{ $project->name }}</a>
                    </h2>
                    <dl class="meta-list">
                        <div>
                            <dt>Tahap</dt>
                            <dd>{{ $project->currentTask()?->stage->label() ?? 'Belum ada tahap' }}</dd>
                        </div>
                        <div>
                            <dt>Tenggat</dt>
                            <dd>
                                {{ $project->due_date->format('d M Y') }}
                                @if ($overdue)
                                    <span class="tag tag-warn">Lewat tenggat</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Progres</dt>
                            <dd>{{ $project->progress() }}%</dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>
    @endif
@endsection
