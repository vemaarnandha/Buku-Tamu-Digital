<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        $target = match ($role) {
            'admin', 'resepsionis' => 'dashboard.kunjungan.index',
            'pegawai' => 'dashboard.tugas.index',
            'pimpinan' => 'dashboard.rekap.index',
            default => null,
        };

        if ($target && Route::has($target)) {
            return redirect()->route($target);
        }

        return Inertia::render('Dashboard', [
            'role' => $role,
        ]);
    }
}
