<?php

namespace Tests\Feature\Team;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Enums\Stage;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AssetUploaded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_uploads_two_megabyte_png(): void
    {
        Notification::fake();
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $file = UploadedFile::fake()->image('feed.png')->size(2048);

        $this->actingAs($team)
            ->post("/tim/projects/{$project->id}/assets", ['file' => $file])
            ->assertRedirect();

        $asset = Asset::firstOrFail();

        $this->assertSame('feed.png', $asset->name);
        $this->assertSame(AssetStatus::Menunggu, $asset->status);
        $this->assertSame(2048 * 1024, $asset->size_bytes);
        $this->assertSame($team->id, $asset->uploaded_by);
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_file_over_fifty_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $file = UploadedFile::fake()->create('video.mp4', 60 * 1024, 'video/mp4');

        $this->actingAs($team)
            ->post("/tim/projects/{$project->id}/assets", ['file' => $file])
            ->assertSessionHasErrors(['file' => 'Ukuran file melebihi batas maksimum 50 MB']);

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_disallowed_extension_is_rejected(): void
    {
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $file = UploadedFile::fake()->create('skrip.exe', 100, 'application/octet-stream');

        $this->actingAs($team)
            ->post("/tim/projects/{$project->id}/assets", ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_upload_without_file_is_rejected(): void
    {
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $this->actingAs($team)
            ->post("/tim/projects/{$project->id}/assets", [])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_client_is_notified_when_asset_uploaded(): void
    {
        Notification::fake();
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $this->actingAs($team)->post("/tim/projects/{$project->id}/assets", [
            'file' => UploadedFile::fake()->image('story.png')->size(512),
        ]);

        Notification::assertSentTo($project->client, AssetUploaded::class);
    }

    public function test_other_team_member_cannot_upload_to_project(): void
    {
        Storage::fake('public');
        [$project] = $this->projectForTeam();
        $outsider = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($outsider)
            ->post("/tim/projects/{$project->id}/assets", [
                'file' => UploadedFile::fake()->image('x.png')->size(100),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_display_name_with_a_slash_is_rejected(): void
    {
        Storage::fake('public');
        [$project, $team] = $this->projectForTeam();

        $this->actingAs($team)->post("/tim/projects/{$project->id}/assets", [
            'file' => UploadedFile::fake()->image('feed.png')->size(200),
            'name' => 'Feed 01/02',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_legacy_asset_name_with_a_slash_still_downloads(): void
    {
        Storage::fake('public');
        [$project] = $this->projectForTeam();
        $path = "assets/{$project->id}/lama.png";
        Storage::disk('public')->put($path, 'isi');

        $asset = Asset::create([
            'project_id' => $project->id,
            'uploaded_by' => $project->team_id,
            'name' => 'Feed 01/02.png',
            'path' => $path,
            'type' => 'image/png',
            'size_bytes' => 3,
        ]);

        $this->actingAs($project->client)
            ->get("/assets/{$asset->id}/download")
            ->assertOk()
            ->assertDownload('Feed 01-02.png');
    }

    /** @return array{0: Project, 1: User} */
    private function projectForTeam(): array
    {
        $project = Project::factory()->create();
        $team = $project->team;

        Task::create([
            'project_id' => $project->id,
            'stage' => Stage::Desain->value,
            'status' => 'proses',
        ]);

        return [$project, $team];
    }
}
