@extends('layouts.app')

@section('title', $project->name)
@section('heading', $project->name)
@section('subheading', 'Progres layanan '.$project->progress().'% · target '.$project->due_date->format('d M Y'))

@section('nav')
    @include('partials.nav-klien')
@endsection

@section('content')
    @error('asset')
        <div class="flash flash-error" role="alert">{{ $message }}</div>
    @enderror

    <div class="panel">
        <h2 class="panel-title">Tahapan produksi</h2>
        @include('partials.timeline', ['tasks' => $project->tasks])
    </div>

    <div class="panel">
        <h2 class="panel-title">Aset kreatif</h2>

        @if ($project->assets->isEmpty())
            <p class="empty">Belum ada aset. Aset akan muncul di sini setelah tim mengunggahnya.</p>
        @else
            <ul class="asset-grid">
                @foreach ($project->assets as $asset)
                    <li class="asset-card">
                        <div class="asset-preview">
                            @if ($asset->isImage() && \Illuminate\Support\Facades\Storage::disk('public')->exists($asset->path))
                                <img src="{{ asset('storage/'.$asset->path) }}" alt="{{ $asset->name }}" loading="lazy">
                            @elseif ($asset->isImage())
                                <span class="file-ext">PNG</span>
                            @else
                                <span class="file-ext">{{ strtoupper(pathinfo($asset->name, PATHINFO_EXTENSION)) }}</span>
                            @endif
                        </div>

                        <div class="asset-card-body">
                            <p class="asset-name">{{ $asset->name }}</p>
                            <p class="asset-meta">{{ $asset->sizeLabel() }} · {{ $asset->created_at->format('d M Y H:i') }}</p>

                            <span class="tag {{ $asset->status === \App\Enums\AssetStatus::Revisi ? 'tag-warn' : '' }}">
                                {{ $asset->status->label() }}
                            </span>

                            @if ($asset->status === \App\Enums\AssetStatus::Revisi && $asset->revision_note)
                                <p class="asset-note">Catatan Anda: {{ $asset->revision_note }}</p>
                            @endif

                            <div class="asset-actions">
                                <a class="btn btn-ghost btn-sm" href="{{ route('assets.download', $asset) }}">Unduh</a>

                                @if ($asset->status === \App\Enums\AssetStatus::Menunggu)
                                    <form method="POST" action="{{ route('portal.assets.approve', $asset) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Setujui</button>
                                    </form>
                                @endif
                            </div>

                            @if ($asset->status === \App\Enums\AssetStatus::Menunggu)
                                <form method="POST" action="{{ route('portal.assets.revision', $asset) }}" class="form revision-form">
                                    @csrf
                                    <div class="field">
                                        <label for="note-{{ $asset->id }}">Minta revisi</label>
                                        <textarea id="note-{{ $asset->id }}" name="note" maxlength="1000"
                                                  placeholder="Tuliskan bagian yang perlu diperbaiki"
                                                  @error('note') aria-invalid="true" aria-describedby="note-error-{{ $asset->id }}" @enderror></textarea>
                                        @error('note')
                                            <p class="field-error" id="note-error-{{ $asset->id }}">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm">Kirim permintaan revisi</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
