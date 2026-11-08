<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController
{
    public function index(): View
    {
        return view('admin.logs', [
            'logs' => ActivityLog::with('project', 'actor')->latest()->limit(100)->get(),
        ]);
    }
}
