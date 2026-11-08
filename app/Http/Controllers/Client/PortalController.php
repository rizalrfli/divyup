<?php

namespace App\Http\Controllers\Client;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortalController
{
    public function index(): View
    {
        $projects = Project::with('team', 'tasks', 'assets')
            ->where('client_id', auth()->id())
            ->orderByDesc('due_date')
            ->get();

        return view('client.index', ['projects' => $projects]);
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('client.show', [
            'project' => $project->load('team', 'tasks', 'assets.uploader'),
            'pending' => $project->assets->where('status', \App\Enums\AssetStatus::Menunggu),
        ]);
    }
}
