<?php

namespace App\Http\Controllers;

use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\Rating;
use App\Models\Tamu;
use App\Services\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RatingController extends Controller
{
    public function index(Request $request): Response
    {
        $noHpRaw = $request->query('no_hp');
        $noHp = null;
        $kunjungan = null;
        $message = null;

        if (is_string($noHpRaw) && trim($noHpRaw) !== '') {
            try {
                $noHp = PhoneNormalizer::normalize($noHpRaw);
                $tamu = Tamu::where('no_hp', $noHp)->first();

                if ($tamu) {
                    $kunjungan = Kunjungan::query()
                        ->where('tamu_id', $tamu->id)
                        ->where('status', 'selesai')
                        ->where(function ($q) {
                            $q->whereDate('waktu_selesai', today())
                                ->orWhere(function ($q2) {
                                    $q2->whereNull('waktu_selesai')->whereDate('waktu_datang', today());
                                });
                        })
                        ->whereDoesntHave('rating')
                        ->latest('waktu_selesai')
                        ->first();
                }

                if ($kunjungan === null) {
                    $message = 'Tidak ada kunjungan selesai hari ini yang bisa dinilai.';
                }
            } catch (\InvalidArgumentException $e) {
                $noHp = null;
                $message = 'Nomor HP tidak valid.';
            }
        }

        return Inertia::render('Rating/Index', [
            'no_hp' => $noHp,
            'kunjungan' => $kunjungan ? [
                'id' => $kunjungan->id,
                'kode_publik' => $kunjungan->kode_publik,
                'unit' => $kunjungan->unit?->nama,
            ] : null,
            'message' => $message,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kunjungan_id' => ['nullable', 'integer', 'exists:kunjungan,id'],
            'no_hp' => ['nullable', 'string'],
            'nilai' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);

        $kunjungan = null;

        if (! empty($validated['kunjungan_id'])) {
            $kunjungan = Kunjungan::find($validated['kunjungan_id']);
        } elseif (! empty($validated['no_hp'])) {
            try {
                $noHp = PhoneNormalizer::normalize($validated['no_hp']);
                $tamu = Tamu::where('no_hp', $noHp)->first();
                if ($tamu) {
                    $kunjungan = Kunjungan::query()
                        ->where('tamu_id', $tamu->id)
                        ->where('status', 'selesai')
                        ->where(function ($q) {
                            $q->whereDate('waktu_selesai', today())
                                ->orWhere(function ($q2) {
                                    $q2->whereNull('waktu_selesai')->whereDate('waktu_datang', today());
                                });
                        })
                        ->whereDoesntHave('rating')
                        ->latest('waktu_selesai')
                        ->first();
                }
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->withErrors(['no_hp' => 'Nomor HP tidak valid.']);
            }
        }

        if ($kunjungan === null) {
            return redirect()->back()->withErrors(['kunjungan_id' => 'Kunjungan selesai tidak ditemukan.']);
        }

        if ($kunjungan->status !== StatusKunjungan::Selesai) {
            return redirect()->back()->withErrors(['kunjungan_id' => 'Kunjungan belum selesai.']);
        }

        if ($kunjungan->rating()->exists()) {
            return redirect()->back()->withErrors(['kunjungan_id' => 'Kunjungan sudah diberi rating.']);
        }

        Rating::create([
            'kunjungan_id' => $kunjungan->id,
            'nilai' => $validated['nilai'],
            'komentar' => $validated['komentar'] ?? null,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Terima kasih atas rating Anda.');
    }
}
