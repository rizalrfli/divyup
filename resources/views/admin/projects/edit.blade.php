@extends('layouts.app')

@section('title', 'Ubah Proyek')
@section('heading', 'Ubah proyek')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}">Dasbor</a>
    <a class="rail-link" href="{{ route('admin.projects.index') }}" aria-current="page">Proyek</a>
@endsection

@section('content')
    @include('admin.projects.form', [
        'action' => route('admin.projects.update', $project),
        'method' => 'PATCH',
        'project' => $project,
    ])
@endsection
