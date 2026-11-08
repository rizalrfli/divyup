<?php

namespace Tests\Feature\Client;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AssetApproved;
use App\Notifications\RevisionRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Driver file, bukan array: pesan galat dikirim lewat flash lintas
     * request, dan driver array tidak menyimpannya antar request.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('session.driver', 'file');
    }

    public function test_client_approves_asset(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();

        $this->actingAs($project->client)
            ->post("/portal/assets/{$asset->id}/approve")
            ->assertRedirect();

        $this->assertSame(AssetStatus::Disetujui, $asset->fresh()->status);
        $this->assertDatabaseHas('approval_logs', [
            'asset_id' => $asset->id,
            'decision' => 'approved',
            'actor_id' => $project->client_id,
        ]);
        Notification::assertSentTo($project->team, AssetApproved::class);
    }

    public function test_approving_closes_the_review_stage(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset(reviewStatus: TaskStatus::Proses);

        $this->actingAs($project->client)->post("/portal/assets/{$asset->id}/approve");

        $review = $project->tasks()->where('stage', Stage::Review->value)->first();

        $this->assertSame(TaskStatus::Selesai, $review->status);
        $this->assertNotNull($review->completed_at);
    }

    public function test_revision_requires_a_note(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();

        $this->actingAs($project->client)
            ->post("/portal/assets/{$asset->id}/revision", ['note' => ''])
            ->assertSessionHasErrors(['note' => 'Catatan revisi wajib diisi']);

        $this->assertSame(AssetStatus::Menunggu, $asset->fresh()->status);
        $this->assertDatabaseCount('approval_logs', 0);
    }

    public function test_revision_stores_note_and_reopens_revision_stage(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();

        $this->actingAs($project->client)
            ->post("/portal/assets/{$asset->id}/revision", ['note' => 'Ganti warna latar jadi krem'])
            ->assertRedirect();

        $asset->refresh();
        $this->assertSame(AssetStatus::Revisi, $asset->status);
        $this->assertSame('Ganti warna latar jadi krem', $asset->revision_note);

        $revision = $project->tasks()->where('stage', Stage::Revisi->value)->first();
        $this->assertSame(TaskStatus::Proses, $revision->status);

        Notification::assertSentTo($project->team, RevisionRequested::class);
    }

    public function test_approving_twice_writes_one_log(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();
        $client = $project->client;

        $this->actingAs($client)->post("/portal/assets/{$asset->id}/approve");
        $this->actingAs($client)->post("/portal/assets/{$asset->id}/approve")
            ->assertSessionHasErrors('asset');

        $this->assertDatabaseCount('approval_logs', 1);
    }

    public function test_project_is_marked_done_when_last_pending_asset_is_approved(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset(TaskStatus::Selesai);

        foreach ($project->tasks as $task) {
            $task->update(['status' => TaskStatus::Selesai]);
        }

        $this->actingAs($project->client)->post("/portal/assets/{$asset->id}/approve");

        $this->assertSame('selesai', $project->fresh()->status);
        $this->assertSame('Selesai', $project->fresh()->stageLabel());
    }

    public function test_project_stays_active_while_another_asset_is_pending(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset(TaskStatus::Selesai);

        foreach ($project->tasks as $task) {
            $task->update(['status' => TaskStatus::Selesai]);
        }

        Asset::create([
            'project_id' => $project->id,
            'uploaded_by' => $project->team_id,
            'name' => 'feed-02.png',
            'path' => "assets/{$project->id}/feed-02.png",
            'type' => 'image/png',
            'size_bytes' => 2048,
            'status' => AssetStatus::Menunggu,
        ]);

        $this->actingAs($project->client)->post("/portal/assets/{$asset->id}/approve");

        $this->assertSame('aktif', $project->fresh()->status);
    }

    public function test_double_approve_error_is_shown_on_the_portal(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();
        $client = $project->client;

        $this->actingAs($client)->post("/portal/assets/{$asset->id}/approve");
        $this->actingAs($client)->from("/portal/projects/{$project->id}")
            ->post("/portal/assets/{$asset->id}/approve");

        $this->actingAs($client)->get("/portal/projects/{$project->id}")
            ->assertOk()
            ->assertSee('Aset ini sudah diproses sebelumnya');
    }

    public function test_other_clients_cannot_approve(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();
        $outsider = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($outsider)
            ->post("/portal/assets/{$asset->id}/approve")
            ->assertForbidden();

        $this->assertSame(AssetStatus::Menunggu, $asset->fresh()->status);
    }

    public function test_team_member_cannot_approve_own_asset(): void
    {
        Notification::fake();
        [$project, $asset] = $this->projectWithPendingAsset();

        $this->actingAs($project->team)
            ->post("/portal/assets/{$asset->id}/approve")
            ->assertForbidden();
    }

    /** @return array{0: Project, 1: Asset} */
    private function projectWithPendingAsset(TaskStatus $reviewStatus = TaskStatus::Belum): array
    {
        $project = Project::factory()->create();

        foreach (Stage::cases() as $stage) {
            Task::create([
                'project_id' => $project->id,
                'stage' => $stage->value,
                'status' => $stage === Stage::Review ? $reviewStatus : TaskStatus::Belum->value,
            ]);
        }

        $asset = Asset::create([
            'project_id' => $project->id,
            'uploaded_by' => $project->team_id,
            'name' => 'feed-01.png',
            'path' => "assets/{$project->id}/feed-01.png",
            'type' => 'image/png',
            'size_bytes' => 2048,
            'status' => AssetStatus::Menunggu,
        ]);

        return [$project, $asset];
    }
}
