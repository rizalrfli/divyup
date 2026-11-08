@extends('layouts.app')

@section('title', 'Portal Klien')
@section('heading', 'Portal Klien')
@section('subheading', 'Progres layanan digital marketing Anda.')

@section('nav')
    <a class="rail-link" href="{{ route('portal.dashboard') }}" aria-current="page">Proyek Saya</a>
@endsection

@section('content')
    <div class="panel">
        <p class="empty">Belum ada proyek.</p>
    </div>
@endsection
