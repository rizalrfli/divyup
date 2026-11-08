<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Tim => $project->team_id === $user->id,
            Role::Klien => $project->client_id === $user->id,
        };
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /** Tim hanya boleh mengubah tahap pada proyek yang ditugaskan. */
    public function contribute(User $user, Project $project): bool
    {
        return $user->isTim() && $project->team_id === $user->id;
    }
}
