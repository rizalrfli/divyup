<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_come_from_database(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $project = Project::factory()->create();
        Project::factory()->create(['status' => 'selesai']);

        foreach ([AssetStatus::Menunggu, AssetStatus::Menunggu, AssetStatus::Menunggu, AssetStatus::Revisi, AssetStatus::Disetujui] as $status) {
            Asset::create([
                'project_id' => $project->id,
                'uploaded_by' => $project->team_id,
                'name' => 'aset.png',
                'path' => 'assets/1/aset.png',
                'type' => 'image/png',
                'size_bytes' => 1000,
                'status' => $status,
            ]);
        }

        User::factory()->create(['role' => Role::Klien->value, 'is_active' => false]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSeeTextInOrder(['Proyek aktif', '1'])
            ->assertSeeTextInOrder(['Menunggu persetujuan', '3'])
            ->assertSeeTextInOrder(['Dalam revisi', '1'])
            ->assertSeeTextInOrder(['Total klien', '2']);
    }

    public function test_report_streams_csv_with_header_row(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $project = Project::factory()->create(['name' => 'SMM Mei 2026']);

        $response = $this->actingAs($admin)->get('/admin/laporan/unduh?bulan=2026-02');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $body = $response->streamedContent();

        $this->assertStringContainsString('proyek,klien,tim,tahap,progres,due_date', $body);
        $this->assertStringContainsString('SMM Mei 2026', $body);
        $this->assertStringContainsString($project->client->name, $body);
    }

    public function test_report_excludes_projects_outside_the_month(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        Project::factory()->create(['name' => 'Proyek Bulan Lain', 'due_date' => '2026-05-31']);

        $body = $this->actingAs($admin)->get('/admin/laporan/unduh?bulan=2026-02')->streamedContent();

        $this->assertStringNotContainsString('Proyek Bulan Lain', $body);
    }

    public function test_admin_creates_user(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $this->actingAs($admin)->post('/admin/pengguna', [
            'name' => 'Nimas Anggraini',
            'email' => 'nimas@divyup.test',
            'role' => 'tim',
            'password' => 'rahasia123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'nimas@divyup.test', 'role' => 'tim']);
    }

    public function test_user_creation_requires_unique_email(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $existing = User::factory()->create(['role' => Role::Klien->value]);

        $this->actingAs($admin)->post('/admin/pengguna', [
            'name' => 'Duplikat',
            'email' => $existing->email,
            'role' => 'klien',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_deactivates_user(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);
        $team = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($admin)->patch("/admin/pengguna/{$team->id}/status")->assertRedirect();

        $this->assertFalse($team->fresh()->is_active);
    }

    public function test_non_admin_cannot_open_user_management(): void
    {
        $team = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($team)->get('/admin/pengguna')->assertForbidden();
    }
}
