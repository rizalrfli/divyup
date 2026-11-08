@extends('layouts.app')

@section('title', 'Pengguna')
@section('heading', 'Pengguna')
@section('subheading', 'Akun tim operasional dan klien. Akun tidak dihapus, hanya dinonaktifkan.')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}">Dasbor</a>
    <a class="rail-link" href="{{ route('admin.projects.index') }}">Proyek</a>
    <a class="rail-link" href="{{ route('admin.users') }}" aria-current="page">Pengguna</a>
    <a class="rail-link" href="{{ route('admin.report') }}">Rekap</a>
@endsection

@section('content')
    @error('user')
        <div class="flash flash-error" role="alert">{{ $message }}</div>
    @enderror

    <div class="panel">
        <h2 class="panel-title">Daftar akun</h2>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Email</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Tindakan</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role->label() }}</td>
                        <td>
                            <span class="tag {{ $user->is_active ? '' : 'tag-warn' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="nowrap">
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-sm">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2 class="panel-title">Tambah akun</h2>

        <form method="POST" action="{{ route('admin.users.store') }}" class="form" novalidate>
            @csrf

            <div class="field-row">
                <div class="field">
                    <label for="user-name">Nama</label>
                    <input id="user-name" name="name" type="text" value="{{ old('name') }}" maxlength="120"
                           @error('name') aria-invalid="true" aria-describedby="user-name-error" @enderror>
                    @error('name')<p class="field-error" id="user-name-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="user-email">Email</label>
                    <input id="user-email" name="email" type="email" value="{{ old('email') }}"
                           @error('email') aria-invalid="true" aria-describedby="user-email-error" @enderror>
                    @error('email')<p class="field-error" id="user-email-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="user-role">Peran</label>
                    <select id="user-role" name="role"
                            @error('role') aria-invalid="true" aria-describedby="user-role-error" @enderror>
                        @foreach (\App\Enums\Role::cases() as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="field-error" id="user-role-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="user-password">Password awal</label>
                    <input id="user-password" name="password" type="password" autocomplete="new-password"
                           @error('password') aria-invalid="true" aria-describedby="user-password-error" @enderror>
                    <p class="field-hint">Minimal 8 karakter. Sampaikan ke pemilik akun.</p>
                    @error('password')<p class="field-error" id="user-password-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Buat akun</button>
            </div>
        </form>
    </div>
@endsection
