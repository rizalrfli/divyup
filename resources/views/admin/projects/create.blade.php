@extends('layouts.app')

@section('title', 'Proyek Baru')
@section('heading', 'Proyek baru')

@section('nav')
    @include('partials.nav-admin')
@endsection

@section('content')
    @include('admin.projects.form', [
        'action' => route('admin.projects.store'),
        'method' => 'POST',
        'project' => null,
    ])
@endsection
