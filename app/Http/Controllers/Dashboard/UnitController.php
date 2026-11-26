<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Units/Index', [
            'units' => Unit::orderBy('nama')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/Units/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:units,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'lokasi_ruang' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'aktif' => ['boolean'],
        ]);

        Unit::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'lokasi_ruang' => $validated['lokasi_ruang'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'aktif' => $validated['aktif'] ?? true,
        ]);

        return redirect()->route('dashboard.units.index')->with('success', 'Unit disimpan.');
    }

    public function show(Unit $unit): Response
    {
        return Inertia::render('Dashboard/Units/Show', ['unit' => $unit]);
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Dashboard/Units/Edit', ['unit' => $unit]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('units', 'kode')->ignore($unit->id)],
            'nama' => ['required', 'string', 'max:255'],
            'lokasi_ruang' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'aktif' => ['boolean'],
        ]);

        $unit->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'lokasi_ruang' => $validated['lokasi_ruang'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'aktif' => $validated['aktif'] ?? $unit->aktif,
        ]);

        return redirect()->route('dashboard.units.index')->with('success', 'Unit diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->kunjungan()->exists() || $unit->pegawai()->exists() || $unit->faq()->exists() || $unit->jamLayanan()->exists()) {
            $unit->update(['aktif' => false]);
        } else {
            $unit->delete();
        }

        return redirect()->route('dashboard.units.index')->with('success', 'Unit dihapus/dinonaktifkan.');
    }
}
