<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Social Media Management - '.fake()->monthName(),
            'client_id' => User::factory()->state(['role' => Role::Klien->value]),
            'team_id' => User::factory()->state(['role' => Role::Tim->value]),
            'start_date' => '2026-02-01',
            'due_date' => '2026-02-28',
            'status' => 'aktif',
        ];
    }
}
