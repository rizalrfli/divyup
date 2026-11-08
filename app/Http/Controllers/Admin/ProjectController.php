<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController
{
    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('admin.projects.show', [
            'project' => $project->load('client', 'team', 'tasks', 'assets'),
        ]);
    }
}
