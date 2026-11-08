<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Notifications\Notification;

class AssetApproved extends Notification
{
    public function __construct(
        public int $projectId,
        public string $assetName,
        public string $projectName,
    ) {}

    public static function from(Asset $asset): self
    {
        return new self($asset->project_id, $asset->name, $asset->project->name);
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
            'title' => 'Aset disetujui klien',
            'body' => $this->projectName.': '.$this->assetName.' disetujui.',
            'url' => '/tim/projects/'.$this->projectId,
            'project_id' => $this->projectId,
        ];
    }
}
