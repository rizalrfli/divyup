@extends('layouts.app')

@section('title', 'Tugas Saya')
@section('heading', 'Tugas Saya')
@section('subheading', 'Proyek yang ditugaskan kepada Anda.')

@section('nav')
    <a class="rail-link" href="{{ route('tim.dashboard') }}" aria-current="page">Tugas Saya</a>
@endsection

@section('content')
    <div class="panel">
        <p class="empty">Belum ada tugas.</p>
    </div>
@endsection
