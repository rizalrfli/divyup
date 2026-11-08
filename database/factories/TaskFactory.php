<?php

namespace Database\Factories;

use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'stage' => Stage::Briefing->value,
            'status' => TaskStatus::Belum->value,
        ];
    }
}
