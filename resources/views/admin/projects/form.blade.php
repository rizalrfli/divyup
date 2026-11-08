@php
    $isEdit = $project !== null;
@endphp

<div class="panel">
    <form method="POST" action="{{ $action }}" class="form" novalidate>
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="field">
            <label for="name">Nama proyek</label>
            <input id="name" name="name" type="text" value="{{ old('name', $project?->name) }}"
                   placeholder="Social Media Management - Maret 2026"
                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')<p class="field-error" id="name-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="client_id">Klien</label>
            <select id="client_id" name="client_id"
                    @error('client_id') aria-invalid="true" aria-describedby="client-error" @enderror>
                <option value="">Pilih klien</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((int) old('client_id', $project?->client_id) === $client->id)>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
            @error('client_id')<p class="field-error" id="client-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="team_id">Penanggung jawab</label>
            <select id="team_id" name="team_id"
                    @error('team_id') aria-invalid="true" aria-describedby="team-error" @enderror>
                <option value="">Belum ditugaskan</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((int) old('team_id', $project?->team_id) === $team->id)>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
            @error('team_id')<p class="field-error" id="team-error">{{ $message }}</p>@enderror
        </div>

        <div class="field-row">
            <div class="field">
                <label for="start_date">Tanggal mulai</label>
                <input id="start_date" name="start_date" type="date"
                       value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}"
                       @error('start_date') aria-invalid="true" aria-describedby="start-error" @enderror>
                @error('start_date')<p class="field-error" id="start-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="due_date">Tanggal target</label>
                <input id="due_date" name="due_date" type="date"
                       value="{{ old('due_date', $project?->due_date?->format('Y-m-d')) }}"
                       @error('due_date') aria-invalid="true" aria-describedby="due-error" @enderror>
                @error('due_date')<p class="field-error" id="due-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan proyek' }}</button>
            <a class="btn btn-ghost" href="{{ route('admin.projects.index') }}">Batal</a>
        </div>
    </form>
</div>
