<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->limit(50)->get(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $row = $request->user()->notifications()->whereKey($notification)->first();

        abort_if($row === null, 403);

        $row->markAsRead();

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }
}
