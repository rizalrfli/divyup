<?php

namespace App\Models;

use App\Enums\Stage;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'stage', 'status', 'note', 'completed_at'])]
class Task extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'stage' => Stage::class,
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
