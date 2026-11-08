@extends('layouts.auth')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <div class="auth-card">
        <div class="wordmark">DIVYUP</div>
        <h1 class="auth-title">Halaman tidak ditemukan</h1>
        <p class="auth-sub">
            Alamat yang Anda buka tidak ada, atau proyek yang dicari sudah tidak tersedia.
        </p>
        <a class="btn btn-primary btn-block" href="{{ url('/') }}">Kembali ke halaman awal</a>
    </div>
@endsection
