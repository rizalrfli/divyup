<?php

namespace App\Http\Controllers\Team;

use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Notifications\StageChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StageController
{
    public function update(Request $request, Project $project, string $stage): RedirectResponse
    {
        Gate::authorize('contribute', $project);

        $task = $project->tasks()->where('stage', $stage)->firstOrFail();

        $data = $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status tahap wajib dipilih',
            'status.enum' => 'Status tahap tidak dikenal',
        ]);

        DB::transaction(function () use ($task, $data, $project, $request, $stage) {
            $status = TaskStatus::from($data['status']);

            $task->update([
                'status' => $status,
                'note' => $data['note'] ?? $task->note,
                'completed_at' => $status === TaskStatus::Selesai ? now() : null,
            ]);

            ActivityLog::create([
                'project_id' => $project->id,
                'actor_id' => $request->user()->id,
                'action' => 'stage.updated',
                'detail' => Stage::from($stage)->label().' menjadi '.$status->label(),
            ]);

            $project->client->notify(StageChanged::from($project, $task));

            $project->syncStatus();
        });

        return back()->with('status', 'Tahap '.$task->stage->label().' diperbarui.');
    }
}
