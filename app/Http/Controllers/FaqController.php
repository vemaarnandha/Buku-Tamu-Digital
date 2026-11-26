<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function ask(Request $request, FaqService $service): JsonResponse
    {
        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:500'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'unit_kode' => ['nullable', 'string', 'exists:units,kode'],
        ]);

        $unitId = $validated['unit_id'] ?? null;
        if ($unitId === null && ! empty($validated['unit_kode'])) {
            $unitId = Unit::where('kode', $validated['unit_kode'])->value('id');
        }

        $hasil = $service->jawab($validated['pertanyaan'], $unitId);

        return response()->json($hasil);
    }

    public function index(): Response
    {
        return Inertia::render('Faq/Index', []);
    }
}
