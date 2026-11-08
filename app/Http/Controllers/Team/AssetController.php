<?php

namespace App\Http\Controllers\Team;

use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Notifications\AssetUploaded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AssetController
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('contribute', $project);

        $request->validate([
            'file' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf,mp4', 'max:51200'],
            'name' => ['nullable', 'string', 'max:120'],
        ], [
            'file.required' => 'Pilih berkas yang akan diunggah',
            'file.max' => 'Ukuran file melebihi batas maksimum 50 MB',
            'file.mimes' => 'Format file harus PNG, JPG, PDF, atau MP4',
            'file.file' => 'Berkas tidak terbaca, coba unggah ulang',
        ]);

        $file = $request->file('file');

        DB::transaction(function () use ($request, $project, $file) {
            $path = $file->store("assets/{$project->id}", 'public');

            $asset = Asset::create([
                'project_id' => $project->id,
                'task_id' => $project->tasks()->where('stage', Stage::Desain->value)->value('id'),
                'uploaded_by' => $request->user()->id,
                'name' => $request->filled('name') ? $request->string('name')->toString() : $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize(),
            ]);

            if ($project->client) {
                $project->client->notify(AssetUploaded::from($asset));
            }
        });

        return back()->with('status', 'Aset berhasil diunggah dan menunggu persetujuan klien.');
    }

    /** Proses permintaan revisi: unggah ulang aset yang sama sebagai berkas baru. */
    public function resubmit(Request $request, Asset $asset): RedirectResponse
    {
        $project = $asset->project;

        Gate::authorize('contribute', $project);

        $request->validate([
            'file' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf,mp4', 'max:51200'],
        ], [
            'file.required' => 'Pilih berkas pengganti',
            'file.max' => 'Ukuran file melebihi batas maksimum 50 MB',
            'file.mimes' => 'Format file harus PNG, JPG, PDF, atau MP4',
        ]);

        $file = $request->file('file');

        DB::transaction(function () use ($request, $project, $asset, $file) {
            $path = $file->store("assets/{$project->id}", 'public');

            $asset->update([
                'uploaded_by' => $request->user()->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'status' => 'menunggu',
            ]);

            $revisionTask = $project->tasks()->where('stage', Stage::Revisi->value)->first();

            if ($revisionTask && $revisionTask->status === TaskStatus::Proses) {
                $revisionTask->update(['status' => TaskStatus::Selesai, 'completed_at' => now()]);
            }

            $project->client->notify(AssetUploaded::from($asset));
        });

        return back()->with('status', 'Aset revisi diunggah ulang dan menunggu persetujuan.');
    }
}
