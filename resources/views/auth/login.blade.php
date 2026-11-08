@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="auth-card">
        <div class="wordmark">DIVYUP</div>
        <h1 class="auth-title">Masuk ke portal progres</h1>
        <p class="auth-sub">Pelaporan progres layanan digital marketing Agensi DivyUp.</p>

        <form method="POST" action="{{ route('login.store') }}" class="form" novalidate>
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       autocomplete="username" autofocus
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p class="field-error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                    <p class="field-error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Tetap masuk di perangkat ini</span>
            </label>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>

    <p class="auth-foot">Agensi DivyUp, unit usaha PT PMI</p>
@endsection
