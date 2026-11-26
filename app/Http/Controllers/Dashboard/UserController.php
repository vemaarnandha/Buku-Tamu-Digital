<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Users/Index', [
            'users' => User::orderBy('name')->get(),
            'roles' => array_map(fn ($r) => $r->value, Role::cases()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/Users/Create', [
            'roles' => array_map(fn ($r) => $r->value, Role::cases()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_map(fn ($r) => $r->value, Role::cases()))],
            'aktif' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'aktif' => $validated['aktif'] ?? true,
        ]);

        return redirect()->route('dashboard.users.index')->with('success', 'User disimpan.');
    }

    public function show(User $user): Response
    {
        return Inertia::render('Dashboard/Users/Show', ['user' => $user]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Dashboard/Users/Edit', [
            'user' => $user,
            'roles' => array_map(fn ($r) => $r->value, Role::cases()),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_map(fn ($r) => $r->value, Role::cases()))],
            'aktif' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'aktif' => $validated['aktif'] ?? $user->aktif,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        return redirect()->route('dashboard.users.index')->with('success', 'User diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('dashboard.users.index')->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('dashboard.users.index')->with('success', 'User dihapus.');
    }
}
