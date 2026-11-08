<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class DashboardController
{
    public function index(): View
    {
        $stats = [
            'Proyek aktif' => Project::where('status', 'aktif')->count(),
            'Menunggu persetujuan' => Asset::where('status', AssetStatus::Menunggu->value)->count(),
            'Dalam revisi' => Asset::where('status', AssetStatus::Revisi->value)->count(),
            'Total klien' => User::where('role', Role::Klien->value)->where('is_active', true)->count(),
        ];

        $projects = Project::with('client', 'team', 'tasks')
            ->orderByDesc('due_date')
            ->get();

        $revisi = Asset::with('project.client')
            ->where('status', AssetStatus::Revisi->value)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'projects' => $projects,
            'revisi' => $revisi,
        ]);
    }
}
