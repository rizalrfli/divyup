@extends('layouts.app')

@section('title', 'Proyek Baru')
@section('heading', 'Proyek baru')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}">Dasbor</a>
    <a class="rail-link" href="{{ route('admin.projects.index') }}" aria-current="page">Proyek</a>
@endsection

@section('content')
    @include('admin.projects.form', [
        'action' => route('admin.projects.store'),
        'method' => 'POST',
        'project' => null,
    ])
@endsection
