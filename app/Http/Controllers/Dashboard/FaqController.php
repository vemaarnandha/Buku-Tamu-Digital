<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Faq/Index', [
            'faqs' => Faq::with('unit')->orderBy('id')->get(),
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/Faq/Create', [
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'pertanyaan' => ['required', 'string', 'max:500'],
            'jawaban' => ['required', 'string', 'max:5000'],
            'aktif' => ['boolean'],
        ]);

        Faq::create([
            'unit_id' => $validated['unit_id'] ?? null,
            'pertanyaan' => $validated['pertanyaan'],
            'jawaban' => $validated['jawaban'],
            'aktif' => $validated['aktif'] ?? true,
        ]);

        return redirect()->route('dashboard.faq.index')->with('success', 'FAQ disimpan.');
    }

    public function show(Faq $faq): Response
    {
        $faq->load('unit');
        return Inertia::render('Dashboard/Faq/Show', ['faq' => $faq]);
    }

    public function edit(Faq $faq): Response
    {
        return Inertia::render('Dashboard/Faq/Edit', [
            'faq' => $faq,
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'pertanyaan' => ['required', 'string', 'max:500'],
            'jawaban' => ['required', 'string', 'max:5000'],
            'aktif' => ['boolean'],
        ]);

        $faq->update([
            'unit_id' => $validated['unit_id'] ?? null,
            'pertanyaan' => $validated['pertanyaan'],
            'jawaban' => $validated['jawaban'],
            'aktif' => $validated['aktif'] ?? $faq->aktif,
        ]);

        return redirect()->route('dashboard.faq.index')->with('success', 'FAQ diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('dashboard.faq.index')->with('success', 'FAQ dihapus.');
    }
}
