@extends('layouts.app')

@section('title', $project->name)
@section('heading', $project->name)
@section('subheading', $project->client->name.' · tenggat '.$project->due_date->format('d M Y'))

@section('nav')
    <a class="rail-link" href="{{ route('tim.dashboard') }}" aria-current="page">Tugas Saya</a>
@endsection

@section('content')
    <div class="panel">
        <h2 class="panel-title">Perbarui tahapan</h2>

        <div class="stage-list">
            @foreach ($project->tasks as $task)
                <form method="POST" action="{{ route('tim.projects.stages.update', [$project, $task->stage->value]) }}"
                      class="stage-row">
                    @csrf
                    @method('PATCH')

                    <div class="stage-name">
                        <p class="stage-label">{{ $task->stage->label() }}</p>
                        <p class="stage-state">{{ $task->status->label() }}</p>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="status-{{ $task->id }}">Status {{ $task->stage->label() }}</label>
                        <select id="status-{{ $task->id }}" name="status">
                            @foreach (\App\Enums\TaskStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($task->status === $status)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label class="sr-only" for="note-{{ $task->id }}">Catatan {{ $task->stage->label() }}</label>
                        <input id="note-{{ $task->id }}" name="note" type="text" maxlength="1000"
                               value="{{ $task->note }}" placeholder="Catatan untuk klien (opsional)">
                    </div>

                    <button type="submit" class="btn btn-ghost btn-sm">Simpan</button>
                </form>
            @endforeach
        </div>
    </div>

    <div class="panel">
        <h2 class="panel-title">Aset kreatif</h2>
        <p class="empty">Belum ada aset yang diunggah.</p>
    </div>
@endsection
