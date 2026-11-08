<?php

namespace App\Http\Controllers\Client;

use App\Enums\AssetStatus;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Asset;
use App\Models\ApprovalLog;
use App\Notifications\AssetApproved;
use App\Notifications\RevisionRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovalController
{
    public function approve(Request $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('view', $asset->project);

        return $this->decide($request, $asset, 'approved', null);
    }

    public function revision(Request $request, Asset $asset): RedirectResponse
    {
        Gate::authorize('view', $asset->project);

        $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ], [
            'note.required' => 'Catatan revisi wajib diisi',
            'note.max' => 'Catatan revisi maksimal 1000 karakter',
        ]);

        return $this->decide($request, $asset, 'revision', $request->string('note')->toString());
    }

    /**
     * Satu keputusan per aset. Klik kedua ditolak, dibaca ulang di dalam
     * transaksi dengan lockForUpdate agar dua permintaan bersamaan tidak
     * menulis dua log.
     */
    private function decide(Request $request, Asset $asset, string $decision, ?string $note): RedirectResponse
    {
        $project = $asset->project;

        $result = DB::transaction(function () use ($asset, $request, $decision, $note, $project) {
            $locked = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== AssetStatus::Menunggu) {
                return false;
            }

            $locked->update([
                'status' => $decision === 'approved' ? AssetStatus::Disetujui : AssetStatus::Revisi,
                'revision_note' => $decision === 'revision' ? $note : $locked->revision_note,
            ]);

            ApprovalLog::create([
                'asset_id' => $locked->id,
                'actor_id' => $request->user()->id,
                'decision' => $decision,
                'note' => $note,
            ]);

            if ($decision === 'approved') {
                $this->closeReviewStage($project);
                $project->team?->notify(AssetApproved::from($locked));
            } else {
                $this->reopenRevisionStage($project);
                $project->team?->notify(RevisionRequested::from($locked));
            }

            $project->syncStatus();

            return true;
        });

        if (! $result) {
            return back()->withErrors(['asset' => 'Aset ini sudah diproses sebelumnya']);
        }

        return back()->with('status', $decision === 'approved'
            ? 'Aset disetujui.'
            : 'Permintaan revisi dikirim ke tim operasional.');
    }

    private function closeReviewStage($project): void
    {
        $task = $project->tasks()->where('stage', Stage::Review->value)->first();

        if ($task) {
            $task->update(['status' => TaskStatus::Selesai, 'completed_at' => now()]);
        }
    }

    private function reopenRevisionStage($project): void
    {
        $task = $project->tasks()->where('stage', Stage::Revisi->value)->first();

        if ($task) {
            $task->update(['status' => TaskStatus::Proses, 'completed_at' => null]);
        }
    }
}
