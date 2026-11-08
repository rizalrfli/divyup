<?php

namespace Tests\Feature\Notifications;

use App\Enums\Role;
use App\Enums\Stage;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_change_creates_unread_notification_for_client(): void
    {
        $project = Project::factory()->create();
        Task::create(['project_id' => $project->id, 'stage' => Stage::Briefing->value, 'status' => 'belum']);

        $this->actingAs($project->team)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai']);

        $this->assertSame(1, $project->client->fresh()->unreadNotifications->count());
    }

    public function test_client_sees_notification_text(): void
    {
        $project = Project::factory()->create();
        Task::create(['project_id' => $project->id, 'stage' => Stage::Briefing->value, 'status' => 'belum']);

        $this->actingAs($project->team)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai']);

        $this->actingAs($project->client)->get('/notifications')
            ->assertOk()
            ->assertSee('Pembaruan tahap proyek')
            ->assertSee('Briefing kini Selesai');
    }

    public function test_empty_notification_state(): void
    {
        $user = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('Belum ada notifikasi');
    }

    public function test_user_can_mark_own_notification_read(): void
    {
        $project = Project::factory()->create();
        Task::create(['project_id' => $project->id, 'stage' => Stage::Briefing->value, 'status' => 'belum']);

        $this->actingAs($project->team)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai']);

        $client = $project->client->fresh();
        $notification = $client->unreadNotifications->first();

        $this->actingAs($client)
            ->patch("/notifications/{$notification->id}/read")
            ->assertRedirect();

        $this->assertSame(0, $client->fresh()->unreadNotifications->count());
    }

    public function test_user_cannot_read_other_users_notification(): void
    {
        $project = Project::factory()->create();
        Task::create(['project_id' => $project->id, 'stage' => Stage::Briefing->value, 'status' => 'belum']);

        $this->actingAs($project->team)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai']);

        $notification = $project->client->fresh()->unreadNotifications->first();
        $outsider = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($outsider)
            ->patch("/notifications/{$notification->id}/read")
            ->assertForbidden();
    }

    public function test_admin_sees_activity_log(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $project = Project::factory()->create();
        Task::create(['project_id' => $project->id, 'stage' => Stage::Briefing->value, 'status' => 'belum']);

        $this->actingAs($project->team)
            ->patch("/tim/projects/{$project->id}/stages/briefing", ['status' => 'selesai']);

        $this->actingAs($admin)->get('/admin/log')
            ->assertOk()
            ->assertSee($project->name)
            ->assertSee('Briefing menjadi Selesai');
    }

    public function test_activity_log_empty_state(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $this->actingAs($admin)->get('/admin/log')
            ->assertOk()
            ->assertSee('Belum ada aktivitas');
    }
}
