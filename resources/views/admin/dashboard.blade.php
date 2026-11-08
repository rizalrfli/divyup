@extends('layouts.app')

@section('title', 'Dasbor')
@section('heading', 'Dasbor')
@section('subheading', 'Ringkasan seluruh proyek klien.')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}" aria-current="page">Dasbor</a>
@endsection

@section('content')
    <div class="panel">
        <p class="empty">Belum ada data untuk ditampilkan.</p>
    </div>
@endsection
