<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetDownloadController
{
    public function __invoke(Asset $asset): StreamedResponse
    {
        Gate::authorize('view', $asset->project);

        abort_unless(Storage::disk('public')->exists($asset->path), 404, 'Berkas tidak ditemukan di server.');

        return Storage::disk('public')->download($asset->path, $asset->name);
    }
}
