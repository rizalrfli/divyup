<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\Stage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_project_with_five_stage_tasks(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $client = User::factory()->create(['role' => Role::Klien->value]);
        $team = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($admin)->post('/admin/projects', [
            'name' => 'Social Media Management - Maret 2026',
            'client_id' => $client->id,
            'team_id' => $team->id,
            'start_date' => '2026-03-01',
            'due_date' => '2026-03-31',
        ])->assertRedirect('/admin/projects');

        $project = Project::where('name', 'Social Media Management - Maret 2026')->firstOrFail();

        $this->assertCount(5, $project->tasks);
        $this->assertSame(Stage::Briefing, $project->tasks->first()->stage);
        $this->assertSame(Stage::SiapPublikasi, $project->tasks->last()->stage);
        $this->assertSame($team->id, $project->team_id);
    }

    public function test_due_date_before_start_date_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($admin)->post('/admin/projects', [
            'name' => 'Proyek Salah Tanggal',
            'client_id' => $client->id,
            'start_date' => '2026-03-10',
            'due_date' => '2026-03-01',
        ])->assertSessionHasErrors('due_date');

        $this->assertDatabaseMissing('projects', ['name' => 'Proyek Salah Tanggal']);
    }

    public function test_client_cannot_create_project(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($client)->post('/admin/projects', [])->assertForbidden();
    }

    public function test_project_index_lists_projects_and_shows_empty_state(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $this->actingAs($admin)->get('/admin/projects')->assertOk()->assertSee('Belum ada proyek');

        Project::factory()->create(['name' => 'SMM April 2026']);

        $this->actingAs($admin)->get('/admin/projects')->assertOk()->assertSee('SMM April 2026');
    }

    public function test_project_detail_links_to_the_edit_form(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $project = Project::factory()->create();

        $this->actingAs($admin)->get("/admin/projects/{$project->id}")
            ->assertOk()
            ->assertSee(route('admin.projects.edit', $project));
    }

    public function test_admin_updates_project_team(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $project = Project::factory()->create();
        $newTeam = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($admin)->patch("/admin/projects/{$project->id}", [
            'name' => $project->name,
            'client_id' => $project->client_id,
            'team_id' => $newTeam->id,
            'start_date' => '2026-02-01',
            'due_date' => '2026-02-28',
        ])->assertRedirect("/admin/projects/{$project->id}");

        $this->assertSame($newTeam->id, $project->fresh()->team_id);
    }

    public function test_team_assignment_must_be_a_team_member(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $client = User::factory()->create(['role' => Role::Klien->value]);
        $otherClient = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($admin)->post('/admin/projects', [
            'name' => 'Proyek Salah Tim',
            'client_id' => $client->id,
            'team_id' => $otherClient->id,
            'start_date' => '2026-03-01',
            'due_date' => '2026-03-31',
        ])->assertSessionHasErrors('team_id');
    }
}
