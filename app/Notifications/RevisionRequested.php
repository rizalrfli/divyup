<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Notifications\Notification;

class RevisionRequested extends Notification
{
    public function __construct(
        public int $projectId,
        public string $assetName,
        public string $projectName,
        public string $note,
    ) {}

    public static function from(Asset $asset): self
    {
        return new self(
            $asset->project_id,
            $asset->name,
            $asset->project->name,
            (string) $asset->revision_note,
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
            'title' => 'Klien meminta revisi',
            'body' => $this->projectName.': '.$this->assetName.' perlu revisi. '.$this->note,
            'url' => '/tim/projects/'.$this->projectId,
            'project_id' => $this->projectId,
        ];
    }
}
