<?php

namespace App\Http\Controllers\Client;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortalController
{
    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('client.show', [
            'project' => $project->load('client', 'team', 'tasks', 'assets'),
        ]);
    }
}
