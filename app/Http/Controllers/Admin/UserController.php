<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController
{
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['required', Password::min(8)],
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'role.required' => 'Peran wajib dipilih',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
        ]);

        User::create($data);

        return back()->with('status', 'Akun '.$data['name'].' dibuat.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Akun Anda sendiri tidak dapat dinonaktifkan.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active
            ? 'Akun '.$user->name.' diaktifkan.'
            : 'Akun '.$user->name.' dinonaktifkan.');
    }
}
