<?php

namespace Tests\Feature\Team;

use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\StageChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StageUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_updates_stage_and_logs_activity(): void
    {
        Notification::fake();
        [$project, $team] = $this->projectWithStages();

        $this->actingAs($team)->patch("/tim/projects/{$project->id}/stages/desain", [
            'status' => 'selesai',
            'note' => 'Desain feed selesai',
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'stage' => Stage::Desain->value,
            'status' => TaskStatus::Selesai->value,
            'note' => 'Desain feed selesai',
        ]);

        $this->assertNotNull($project->tasks()->where('stage', 'desain')->first()->completed_at);

        $this->assertDatabaseHas('activity_logs', [
            'project_id' => $project->id,
            'actor_id' => $team->id,
            'action' => 'stage.updated',
        ]);
    }

    public function test_client_notified_when_stage_changes(): void
    {
        Notification::fake();
        [$project, $team] = $this->projectWithStages();

        $this->actingAs($team)->patch("/tim/projects/{$project->id}/stages/briefing", [
            'status' => 'selesai',
        ]);

        Notification::assertSentTo($project->client, StageChanged::class, function ($notification) use ($project) {
            return $notification->projectId === $project->id;
        });
    }

    public function test_invalid_status_is_rejected(): void
    {
        Notification::fake();
        [$project, $team] = $this->projectWithStages();

        $this->actingAs($team)->patch("/tim/projects/{$project->id}/stages/briefing", [
            'status' => 'entah-apa',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'stage' => 'briefing',
            'status' => TaskStatus::Belum->value,
        ]);
    }

    public function test_client_cannot_update_stage(): void
    {
        [$project] = $this->projectWithStages();
        $client = $project->client;

        $this->actingAs($client)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai'])
            ->assertForbidden();
    }

    public function test_other_team_member_cannot_update_stage(): void
    {
        [$project] = $this->projectWithStages();
        $outsider = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($outsider)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai'])
            ->assertForbidden();
    }

    public function test_team_dashboard_lists_assigned_projects_only(): void
    {
        [$project, $team] = $this->projectWithStages();
        Project::factory()->create(['name' => 'Proyek Tim Lain']);

        $this->actingAs($team)->get('/tim')
            ->assertOk()
            ->assertSee($project->client->name)
            ->assertDontSee('Proyek Tim Lain');
    }

    /** @return array{0: Project, 1: User} */
    private function projectWithStages(): array
    {
        $project = Project::factory()->create();
        $team = $project->team;

        foreach (Stage::cases() as $stage) {
            Task::create([
                'project_id' => $project->id,
                'stage' => $stage->value,
                'status' => TaskStatus::Belum->value,
            ]);
        }

        return [$project, $team];
    }
}
