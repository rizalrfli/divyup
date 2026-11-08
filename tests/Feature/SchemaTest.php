<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_progress_counts_done_and_skipped_tasks(): void
    {
        $project = $this->projectWithStages([
            Stage::Briefing->value => TaskStatus::Selesai,
            Stage::Desain->value => TaskStatus::Selesai,
            Stage::Review->value => TaskStatus::Proses,
            Stage::Revisi->value => TaskStatus::Belum,
        ]);

        $this->assertSame(50, $project->progress());
    }

    public function test_project_progress_is_zero_without_tasks(): void
    {
        $this->assertSame(0, $this->projectWithStages([])->progress());
    }

    public function test_seeder_creates_three_roles(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', Role::Admin->value)->count());
        $this->assertSame(4, User::where('role', Role::Tim->value)->count());
        $this->assertSame(2, User::where('role', Role::Klien->value)->count());
    }

    public function test_seeded_project_has_five_stage_tasks_and_three_pending_assets(): void
    {
        $this->seed();

        $project = Project::where('name', 'like', '%Februari 2026%')->firstOrFail();

        $this->assertCount(5, $project->tasks);
        $this->assertCount(3, $project->assets);
        $this->assertSame(AssetStatus::Menunggu, $project->assets->first()->status);
    }

    public function test_default_asset_status_is_pending(): void
    {
        $project = $this->projectWithStages([Stage::Briefing->value => TaskStatus::Proses]);
        $uploader = User::factory()->create(['role' => Role::Tim->value]);

        $asset = $project->assets()->create([
            'uploaded_by' => $uploader->id,
            'name' => 'feed-04.png',
            'path' => 'assets/1/feed-04.png',
            'type' => 'image/png',
            'size_bytes' => 1024,
        ]);

        $this->assertSame(AssetStatus::Menunggu, $asset->status);
        $this->assertSame('1 KB', $asset->sizeLabel());
    }

    private function projectWithStages(array $statuses): Project
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);
        $project = Project::create([
            'name' => 'Proyek Uji',
            'client_id' => $client->id,
            'start_date' => '2026-02-01',
            'due_date' => '2026-02-28',
        ]);

        foreach ($statuses as $stage => $status) {
            $project->tasks()->create(['stage' => $stage, 'status' => $status->value]);
        }

        return $project;
    }
}
