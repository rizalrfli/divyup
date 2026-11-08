<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController
{
    public function index(): View
    {
        $projects = Project::with('client', 'team', 'tasks')
            ->orderByDesc('due_date')
            ->get();

        return view('admin.projects.index', [
            'projects' => $projects,
            'clients' => User::where('role', Role::Klien->value)->orderBy('name')->get(),
            'teams' => User::where('role', Role::Tim->value)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.create', [
            'clients' => User::where('role', Role::Klien->value)->where('is_active', true)->orderBy('name')->get(),
            'teams' => User::where('role', Role::Tim->value)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProject($request);

        DB::transaction(function () use ($data) {
            $project = Project::create($data);

            foreach (Stage::cases() as $stage) {
                Task::create([
                    'project_id' => $project->id,
                    'stage' => $stage->value,
                    'status' => TaskStatus::Belum->value,
                ]);
            }
        });

        return redirect('/admin/projects')->with('status', 'Proyek baru dibuat dengan 5 tahapan.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('admin.projects.show', [
            'project' => $project->load('client', 'team', 'tasks', 'assets'),
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('admin.projects.edit', [
            'project' => $project,
            'clients' => User::where('role', Role::Klien->value)->orderBy('name')->get(),
            'teams' => User::where('role', Role::Tim->value)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $project->update($this->validateProject($request));

        return redirect('/admin/projects/'.$project->id)->with('status', 'Proyek diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'client_id' => ['required', Rule::exists('users', 'id')->where('role', Role::Klien->value)],
            'team_id' => ['nullable', Rule::exists('users', 'id')->where('role', Role::Tim->value)],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
        ], [
            'name.required' => 'Nama proyek wajib diisi',
            'client_id.required' => 'Klien wajib dipilih',
            'client_id.exists' => 'Klien yang dipilih tidak valid',
            'team_id.exists' => 'Penanggung jawab harus anggota tim operasional',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'due_date.required' => 'Tanggal target wajib diisi',
            'due_date.after_or_equal' => 'Tanggal target tidak boleh sebelum tanggal mulai',
        ]);
    }
}
