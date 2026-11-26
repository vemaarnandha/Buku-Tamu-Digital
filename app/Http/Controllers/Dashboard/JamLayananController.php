<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Hari;
use App\Http\Controllers\Controller;
use App\Models\JamLayanan;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JamLayananController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/JamLayanan/Index', [
            'jamLayanans' => JamLayanan::with('unit')->orderBy('unit_id')->orderBy('hari')->get(),
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/JamLayanan/Create', [
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'hariOptions' => array_map(fn ($h) => $h->value, Hari::cases()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'hari' => ['required', Rule::in(array_map(fn ($h) => $h->value, Hari::cases()))],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'aktif' => ['boolean'],
        ]);

        JamLayanan::create([
            'unit_id' => $validated['unit_id'],
            'hari' => $validated['hari'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'aktif' => $validated['aktif'] ?? true,
        ]);

        return redirect()->route('dashboard.jam-layanan.index')->with('success', 'Jam layanan disimpan.');
    }

    public function show(JamLayanan $jamLayanan): Response
    {
        $jamLayanan->load('unit');
        return Inertia::render('Dashboard/JamLayanan/Show', ['jamLayanan' => $jamLayanan]);
    }

    public function edit(JamLayanan $jamLayanan): Response
    {
        return Inertia::render('Dashboard/JamLayanan/Edit', [
            'jamLayanan' => $jamLayanan,
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'hariOptions' => array_map(fn ($h) => $h->value, Hari::cases()),
        ]);
    }

    public function update(Request $request, JamLayanan $jamLayanan): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'hari' => ['required', Rule::in(array_map(fn ($h) => $h->value, Hari::cases()))],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'aktif' => ['boolean'],
        ]);

        $jamLayanan->update([
            'unit_id' => $validated['unit_id'],
            'hari' => $validated['hari'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'aktif' => $validated['aktif'] ?? $jamLayanan->aktif,
        ]);

        return redirect()->route('dashboard.jam-layanan.index')->with('success', 'Jam layanan diperbarui.');
    }

    public function destroy(JamLayanan $jamLayanan): RedirectResponse
    {
        $jamLayanan->delete();

        return redirect()->route('dashboard.jam-layanan.index')->with('success', 'Jam layanan dihapus.');
    }
}
