<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Notifications\Notification;

class StageChanged extends Notification
{
    public function __construct(
        public int $projectId,
        public string $projectName,
        public string $stageLabel,
        public string $statusLabel,
    ) {}

    public static function from(Project $project, Task $task): self
    {
        return new self(
            $project->id,
            $project->name,
            $task->stage->label(),
            $task->status->label(),
        );
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pembaruan tahap proyek',
            'body' => $this->projectName.': '.$this->stageLabel.' kini '.$this->statusLabel.'.',
            'url' => '/portal/projects/'.$this->projectId,
            'project_id' => $this->projectId,
        ];
    }
}
