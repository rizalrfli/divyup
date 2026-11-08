<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AssetDownloadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Client\ApprovalController;
use App\Http\Controllers\Client\PortalController;
use App\Http\Controllers\Team;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    return redirect(app(AuthController::class)->redirectFor(auth()->user()));
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/assets/{asset}/download', AssetDownloadController::class)
    ->middleware('auth')
    ->name('assets.download');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [Admin\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [Admin\ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [Admin\ProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [Admin\ProjectController::class, 'update'])->name('projects.update');

    Route::get('/pengguna', [Admin\UserController::class, 'index'])->name('users');
    Route::post('/pengguna', [Admin\UserController::class, 'store'])->name('users.store');
    Route::patch('/pengguna/{user}/status', [Admin\UserController::class, 'updateStatus'])->name('users.status');

    Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('report');
    Route::get('/laporan/unduh', [Admin\ReportController::class, 'download'])->name('report.download');
    Route::get('/log', [Admin\ActivityLogController::class, 'index'])->name('log');
});

Route::middleware(['auth', 'role:tim'])->prefix('tim')->name('tim.')->group(function () {
    Route::get('/', [Team\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/projects/{project}', [Team\ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/projects/{project}/stages/{stage}', [Team\StageController::class, 'update'])
        ->name('projects.stages.update');
    Route::post('/projects/{project}/assets', [Team\AssetController::class, 'store'])
        ->name('projects.assets.store');
    Route::post('/assets/{asset}/resubmit', [Team\AssetController::class, 'resubmit'])
        ->name('assets.resubmit');
});

Route::middleware(['auth', 'role:klien'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('dashboard');
    Route::get('/projects/{project}', [PortalController::class, 'show'])->name('projects.show');
    Route::post('/assets/{asset}/approve', [ApprovalController::class, 'approve'])->name('assets.approve');
    Route::post('/assets/{asset}/revision', [ApprovalController::class, 'revision'])->name('assets.revision');
});
