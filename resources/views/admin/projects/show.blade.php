@extends('layouts.app')

@section('title', $project->name)
@section('heading', $project->name)
@section('subheading', $project->client->name.' · '.$project->progress().'% selesai')

@section('nav')
    <a class="rail-link" href="{{ route('admin.dashboard') }}">Dasbor</a>
@endsection

@section('content')
    <div class="panel">
        <h2 class="panel-title">Tahapan produksi</h2>
        @include('partials.timeline', ['tasks' => $project->tasks])
    </div>
@endsection
