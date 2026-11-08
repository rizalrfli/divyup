<?php

namespace Tests\Feature\Access;

use App\Enums\Role;
use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_view_other_clients_project(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);
        $other = Project::factory()->create();

        $this->actingAs($client)
            ->get("/portal/projects/{$other->id}")
            ->assertForbidden();
    }

    public function test_unassigned_team_member_gets_forbidden(): void
    {
        $outsider = User::factory()->create(['role' => Role::Tim->value]);
        $project = Project::factory()->create();

        $this->actingAs($outsider)
            ->get("/tim/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_every_project(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $project = Project::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/projects/{$project->id}")
            ->assertOk();
    }

    public function test_client_can_view_own_project(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);
        $project = Project::factory()->create(['client_id' => $client->id]);

        $this->actingAs($client)
            ->get("/portal/projects/{$project->id}")
            ->assertOk();
    }

    public function test_deactivated_user_loses_access_on_next_request(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($client);
        $client->update(['is_active' => false]);

        $this->get('/portal')->assertForbidden();
    }

    public function test_deactivated_user_cannot_download_assets(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $path = "assets/{$project->id}/feed.png";
        Storage::disk('public')->put($path, 'isi');

        $asset = Asset::create([
            'project_id' => $project->id,
            'uploaded_by' => $project->team_id,
            'name' => 'feed.png',
            'path' => $path,
            'type' => 'image/png',
            'size_bytes' => 3,
        ]);

        $client = $project->client;
        $this->actingAs($client);
        $client->update(['is_active' => false]);

        $this->get("/assets/{$asset->id}/download")->assertForbidden();
    }

    public function test_deactivated_user_cannot_read_notifications(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($client);
        $client->update(['is_active' => false]);

        $this->get('/notifications')->assertForbidden();
    }

    public function test_forbidden_page_is_in_indonesian(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($client)->get('/admin')
            ->assertForbidden()
            ->assertSee('Tidak punya akses')
            ->assertDontSee('Forbidden');
    }
}
