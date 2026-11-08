<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_reachable(): void
    {
        $this->get('/login')->assertOk()->assertSee('DIVYUP');
    }

    public function test_valid_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_valid_team_member_is_redirected_to_team_dashboard(): void
    {
        $team = User::factory()->create(['role' => Role::Tim->value]);

        $this->post('/login', ['email' => $team->email, 'password' => 'password'])
            ->assertRedirect('/tim');
    }

    public function test_valid_client_is_redirected_to_portal(): void
    {
        $client = User::factory()->create(['role' => Role::Klien->value]);

        $this->post('/login', ['email' => $client->email, 'password' => 'password'])
            ->assertRedirect('/portal');
    }

    public function test_wrong_password_shows_error_and_keeps_guest(): void
    {
        $user = User::factory()->create(['role' => Role::Admin->value]);

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-sekali'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah']);

        $this->assertGuest();
    }

    public function test_unknown_email_shows_the_same_error_as_wrong_password(): void
    {
        $this->post('/login', ['email' => 'tidak-ada@divyup.test', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah']);
    }

    public function test_empty_credentials_are_rejected_with_validation(): void
    {
        $this->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->create(['role' => Role::Admin->value, 'is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_stale_intended_url_from_another_role_is_ignored(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        // Sesi sebelumnya menyimpan /tim sebagai tujuan; admin tidak boleh dibuang ke sana.
        $this->withSession(['url.intended' => 'http://localhost/tim'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_intended_url_inside_own_area_is_kept(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin->value]);

        $this->withSession(['url.intended' => 'http://localhost/admin/projects'])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin/projects');
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create(['role' => Role::Tim->value]);

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
