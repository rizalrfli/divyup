<?php

namespace App\Http\Controllers\Team;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController
{
    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('team.project', [
            'project' => $project->load('client', 'tasks', 'assets'),
        ]);
    }
}
