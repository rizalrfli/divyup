@extends('layouts.app')

@section('title', $project->name)
@section('heading', $project->name)
@section('subheading', $project->client->name)

@section('nav')
    <a class="rail-link" href="{{ route('tim.dashboard') }}">Tugas Saya</a>
@endsection

@section('content')
    <div class="panel">
        <h2 class="panel-title">Tahapan produksi</h2>
        @include('partials.timeline', ['tasks' => $project->tasks])
    </div>
@endsection
