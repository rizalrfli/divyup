<?php

namespace App\Http\Controllers\Team;

use App\Models\Project;
use Illuminate\View\View;

class DashboardController
{
    public function index(): View
    {
        $projects = Project::with('client', 'tasks')
            ->where('team_id', auth()->id())
            ->orderBy('due_date')
            ->get();

        return view('team.dashboard', ['projects' => $projects]);
    }
}
