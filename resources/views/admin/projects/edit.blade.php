@extends('layouts.app')

@section('title', 'Ubah Proyek')
@section('heading', 'Ubah proyek')

@section('nav')
    @include('partials.nav-admin')
@endsection

@section('content')
    @include('admin.projects.form', [
        'action' => route('admin.projects.update', $project),
        'method' => 'PATCH',
        'project' => $project,
    ])
@endsection
