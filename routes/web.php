<?php

use App\Http\Controllers\AuthController;
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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => 'admin')->name('dashboard');
});

Route::middleware(['auth', 'role:tim'])->prefix('tim')->name('tim.')->group(function () {
    Route::get('/', fn () => 'tim')->name('dashboard');
});

Route::middleware(['auth', 'role:klien'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', fn () => 'portal')->name('dashboard');
});
