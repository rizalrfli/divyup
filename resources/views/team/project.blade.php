@extends('layouts.app')

@section('title', $project->name)
@section('heading', $project->name)
@section('subheading', $project->client->name.' · tenggat '.$project->due_date->format('d M Y'))

@section('nav')
    @include('partials.nav-tim')
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
        <h2 class="panel-title">Unggah aset kreatif</h2>

        <form method="POST" action="{{ route('tim.projects.assets.store', $project) }}"
              class="form" enctype="multipart/form-data">
            @csrf

            <div class="field">
                <label for="file">Berkas</label>
                <input id="file" name="file" type="file" accept=".png,.jpg,.jpeg,.pdf,.mp4"
                       @error('file') aria-invalid="true" aria-describedby="file-error" @enderror>
                <p class="field-hint">PNG, JPG, PDF, atau MP4. Maksimal 4 MB per berkas.</p>
                @error('file')<p class="field-error" id="file-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="asset-name">Nama tampilan (opsional)</label>
                <input id="asset-name" name="name" type="text" maxlength="120"
                       placeholder="Feed Instagram 01">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Unggah aset</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <h2 class="panel-title">Aset kreatif</h2>

        @if ($project->assets->isEmpty())
            <p class="empty">Belum ada aset yang diunggah.</p>
        @else
            <ul class="asset-list">
                @foreach ($project->assets as $asset)
                    <li class="asset-row">
                        <div class="asset-main">
                            <p class="asset-name">{{ $asset->name }}</p>
                            <p class="asset-meta">
                                {{ $asset->sizeLabel() }} · diunggah {{ $asset->created_at->format('d M Y H:i') }}
                            </p>
                            @if ($asset->revision_note)
                                <p class="asset-note">Catatan klien: {{ $asset->revision_note }}</p>
                            @endif
                        </div>

                        <div class="asset-side">
                            <span class="tag {{ $asset->status === \App\Enums\AssetStatus::Revisi ? 'tag-warn' : '' }}">
                                {{ $asset->status->label() }}
                            </span>

                            <a class="btn btn-ghost btn-sm" href="{{ route('assets.download', $asset) }}">Unduh</a>

                            @if ($asset->status === \App\Enums\AssetStatus::Revisi)
                                <form method="POST" action="{{ route('tim.assets.resubmit', $asset) }}"
                                      class="inline-form" enctype="multipart/form-data">
                                    @csrf
                                    <label class="sr-only" for="resubmit-{{ $asset->id }}">Berkas pengganti</label>
                                    <input id="resubmit-{{ $asset->id }}" name="file" type="file" required
                                           accept=".png,.jpg,.jpeg,.pdf,.mp4">
                                    <button type="submit" class="btn btn-primary btn-sm">Ajukan ulang</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
