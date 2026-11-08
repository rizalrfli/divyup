<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $home = $this->redirectFor($user);
        $intended = $request->session()->pull('url.intended');
        $path = $intended ? (parse_url($intended, PHP_URL_PATH) ?? '') : '';

        // Tujuan lama hanya dipakai bila masih di area peran ini, supaya
        // sesi sebelumnya tidak membuang pengguna ke halaman 403. Yang
        // dipakai hanya path-nya, jadi host luar tidak ikut dialihkan.
        return redirect($path !== '' && str_starts_with($path, $home) ? $path : $home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function redirectFor(\App\Models\User $user): string
    {
        return match ($user->role) {
            \App\Enums\Role::Admin => '/admin',
            \App\Enums\Role::Tim => '/tim',
            \App\Enums\Role::Klien => '/portal',
        };
    }
}
