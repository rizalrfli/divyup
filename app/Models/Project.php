<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'client_id', 'team_id', 'start_date', 'due_date', 'status'])]
class Project extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class)->latest();
    }

    /** Persentase tahap yang sudah selesai atau dilewati. */
    public function progress(): int
    {
        $total = $this->tasks->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks
            ->whereIn('status', [TaskStatus::Selesai, TaskStatus::Dilewati])
            ->count();

        return (int) round($done / $total * 100);
    }

    /** Tahap yang sedang berjalan, atau tahap terakhir bila semuanya selesai. */
    public function currentTask(): ?Task
    {
        return $this->tasks->first(
            fn (Task $task) => ! in_array($task->status, [TaskStatus::Selesai, TaskStatus::Dilewati], true)
        ) ?? $this->tasks->last();
    }
}
