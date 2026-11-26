<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PegawaiController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Pegawai/Index', [
            'pegawais' => Pegawai::with(['unit', 'user'])->orderBy('nama')->get(),
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/Pegawai/Create', [
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:pegawai,user_id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_wa' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'aktif' => ['boolean'],
            'bisa_menerima_tamu' => ['boolean'],
        ]);

        Pegawai::create([
            'user_id' => $validated['user_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'] ?? null,
            'no_wa' => $validated['no_wa'] ?? null,
            'email' => $validated['email'] ?? null,
            'aktif' => $validated['aktif'] ?? true,
            'bisa_menerima_tamu' => $validated['bisa_menerima_tamu'] ?? true,
        ]);

        return redirect()->route('dashboard.pegawai.index')->with('success', 'Pegawai disimpan.');
    }

    public function show(Pegawai $pegawai): Response
    {
        $pegawai->load(['unit', 'user']);
        return Inertia::render('Dashboard/Pegawai/Show', ['pegawai' => $pegawai]);
    }

    public function edit(Pegawai $pegawai): Response
    {
        return Inertia::render('Dashboard/Pegawai/Edit', [
            'pegawai' => $pegawai,
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('pegawai', 'user_id')->ignore($pegawai->id)],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_wa' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'aktif' => ['boolean'],
            'bisa_menerima_tamu' => ['boolean'],
        ]);

        $pegawai->update([
            'user_id' => $validated['user_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'] ?? null,
            'no_wa' => $validated['no_wa'] ?? null,
            'email' => $validated['email'] ?? null,
            'aktif' => $validated['aktif'] ?? $pegawai->aktif,
            'bisa_menerima_tamu' => $validated['bisa_menerima_tamu'] ?? $pegawai->bisa_menerima_tamu,
        ]);

        return redirect()->route('dashboard.pegawai.index')->with('success', 'Pegawai diperbarui.');
    }

    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        if ($pegawai->kunjungan()->exists()) {
            $pegawai->update(['aktif' => false]);
        } else {
            $pegawai->delete();
        }

        return redirect()->route('dashboard.pegawai.index')->with('success', 'Pegawai dihapus/dinonaktifkan.');
    }
}
