<?php

namespace Tests\Feature\Client;

use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_sees_only_own_projects(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);
        Project::factory()->create(['name' => 'Proyek Milik Saya', 'client_id' => $client->id]);
        Project::factory()->create(['name' => 'Proyek Klien Lain']);

        $this->actingAs($client)->get('/portal')
            ->assertOk()
            ->assertSee('Proyek Milik Saya')
            ->assertDontSee('Proyek Klien Lain');
    }

    public function test_portal_shows_empty_state_without_projects(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($client)->get('/portal')
            ->assertOk()
            ->assertSee('Belum ada proyek');
    }

    public function test_timeline_marks_completed_stage(): void
    {
        $project = Project::factory()->create();
        $client = $project->client;

        Task::create([
            'project_id' => $project->id,
            'stage' => Stage::Briefing->value,
            'status' => TaskStatus::Selesai->value,
        ]);
        Task::create([
            'project_id' => $project->id,
            'stage' => Stage::Desain->value,
            'status' => TaskStatus::Proses->value,
        ]);

        $this->actingAs($client)->get("/portal/projects/{$project->id}")
            ->assertOk()
            ->assertSeeInOrder(['Briefing', 'Selesai', 'Pengerjaan Desain', 'Sedang dikerjakan']);
    }

    public function test_project_without_assets_shows_empty_state(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($project->client)->get("/portal/projects/{$project->id}")
            ->assertOk()
            ->assertSee('Belum ada aset');
    }

    public function test_client_can_download_own_asset(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $asset = $this->assetFor($project, 'feed-01.png');

        $this->actingAs($project->client)
            ->get("/assets/{$asset->id}/download")
            ->assertDownload('feed-01.png');
    }

    public function test_client_cannot_download_other_clients_asset(): void
    {
        Storage::fake('public');
        $asset = $this->assetFor(Project::factory()->create(), 'rahasia.png');
        $outsider = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($outsider)
            ->get("/assets/{$asset->id}/download")
            ->assertForbidden();
    }

    public function test_missing_file_returns_not_found(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $asset = $this->assetFor($project, 'hilang.png');
        Storage::disk('public')->delete($asset->path);

        $this->actingAs($project->client)
            ->get("/assets/{$asset->id}/download")
            ->assertNotFound();
    }

    private function assetFor(Project $project, string $name): Asset
    {
        $path = "assets/{$project->id}/{$name}";
        Storage::disk('public')->put($path, 'isi-berkas');

        return Asset::create([
            'project_id' => $project->id,
            'uploaded_by' => $project->team_id ?? $project->client_id,
            'name' => $name,
            'path' => $path,
            'type' => 'image/png',
            'size_bytes' => 11,
        ]);
    }
}
