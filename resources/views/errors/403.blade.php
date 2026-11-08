@extends('layouts.auth')

@section('title', 'Tidak punya akses')

@section('content')
    <div class="auth-card">
        <div class="wordmark">DIVYUP</div>
        <h1 class="auth-title">Tidak punya akses</h1>
        <p class="auth-sub">
            Akun Anda tidak diizinkan membuka halaman ini, atau akses akun sudah dinonaktifkan.
            Hubungi pimpinan agensi bila menurut Anda ini keliru.
        </p>
        <a class="btn btn-primary btn-block" href="{{ url('/') }}">Kembali ke halaman awal</a>
    </div>
@endsection
