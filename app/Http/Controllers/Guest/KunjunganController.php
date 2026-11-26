<?php

namespace App\Http\Controllers\Guest;

use App\Enums\Hari;
use App\Enums\KlasifikasiStatus;
use App\Enums\StatusKunjungan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreKunjunganRequest;
use App\Jobs\KlasifikasiKunjungan;
use App\Models\JamLayanan;
use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Services\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KunjunganController extends Controller
{
    public function create(Request $request): Response
    {
        $noHpRaw = $request->query('no_hp');
        $noHp = null;
        $tamu = null;
        $kunjunganSelesai = 0;

        if (is_string($noHpRaw) && trim($noHpRaw) !== '') {
            try {
                $noHp = PhoneNormalizer::normalize($noHpRaw);
                $tamu = Tamu::where('no_hp', $noHp)->first();
                if ($tamu) {
                    $kunjunganSelesai = $tamu->kunjungan()
                        ->where('status', StatusKunjungan::Selesai)
                        ->count();
                }
            } catch (\InvalidArgumentException $e) {
                $noHp = null;
            }
        }

        [$jamLayananHariIni, $diLuarJamLayanan] = $this->jamLayananHariIni();

        return Inertia::render('Guest/Index', [
            'tamu' => $tamu ? [
                'id' => $tamu->id,
                'nama' => $tamu->nama,
                'kategori' => $tamu->kategori?->value,
                'instansi' => $tamu->instansi,
                'alamat' => $tamu->alamat,
                'no_hp' => $tamu->no_hp,
            ] : null,
            'no_hp' => $noHp,
            'kunjunganSelesai' => $kunjunganSelesai,
            'jamLayananHariIni' => $jamLayananHariIni,
            'diLuarJamLayanan' => $diLuarJamLayanan,
        ]);
    }

    public function store(StoreKunjunganRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $noHp = PhoneNormalizer::normalize($validated['no_hp']);

        $tamu = Tamu::where('no_hp', $noHp)->first();

        $data = [
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'instansi' => $validated['instansi'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'no_hp' => $noHp,
        ];

        if ($tamu) {
            $tamu->update($data);
        } else {
            $tamu = Tamu::create($data);
        }

        $kodePublik = $this->generateKodePublik();

        $kunjungan = Kunjungan::create([
            'kode_publik' => $kodePublik,
            'tamu_id' => $tamu->id,
            'keperluan' => $validated['keperluan'],
            'status' => StatusKunjungan::Menunggu,
            'klasifikasi_status' => KlasifikasiStatus::Diproses,
            'waktu_datang' => now(),
        ]);

        KlasifikasiKunjungan::dispatch($kunjungan);

        return redirect()->route('kunjungan.status', ['kode_publik' => $kunjungan->kode_publik]);
    }

    public function status(string $kodePublik): Response
    {
        $kunjungan = Kunjungan::where('kode_publik', $kodePublik)
            ->with(['tamu', 'unit', 'pegawai'])
            ->firstOrFail();

        return Inertia::render('Guest/Status', [
            'kunjungan' => [
                'kode_publik' => $kunjungan->kode_publik,
                'keperluan' => $kunjungan->keperluan,
                'kategori' => $kunjungan->kategori,
                'ringkasan_ai' => $kunjungan->ringkasan_ai,
                'status' => $kunjungan->status->value,
                'klasifikasi_status' => $kunjungan->klasifikasi_status->value,
                'unit' => $kunjungan->unit ? [
                    'nama' => $kunjungan->unit->nama,
                    'lokasi_ruang' => $kunjungan->unit->lokasi_ruang,
                ] : null,
                'pegawai' => $kunjungan->pegawai ? [
                    'nama' => $kunjungan->pegawai->nama,
                    'jabatan' => $kunjungan->pegawai->jabatan,
                ] : null,
                'waktu_datang' => optional($kunjungan->waktu_datang)->toIso8601String(),
                'notifikasi_terkirim_pada' => optional($kunjungan->notifikasi_terkirim_pada)->toIso8601String(),
                'alasan_tidak_terlayani' => $kunjungan->alasan_tidak_terlayani,
            ],
        ]);
    }

    protected function generateKodePublik(): string
    {
        do {
            $kode = Str::lower(Str::random(12));
        } while (Kunjungan::where('kode_publik', $kode)->exists());

        return $kode;
    }

    /**
     * @return array{0: array<int, string>, 1: bool}
     */
    protected function jamLayananHariIni(): array
    {
        $map = [
            'Monday' => Hari::Senin,
            'Tuesday' => Hari::Selasa,
            'Wednesday' => Hari::Rabu,
            'Thursday' => Hari::Kamis,
            'Friday' => Hari::Jumat,
            'Saturday' => Hari::Sabtu,
            'Sunday' => Hari::Minggu,
        ];

        $hari = $map[now()->format('l')] ?? null;

        if (! $hari) {
            return [[], false];
        }

        $rows = JamLayanan::where('hari', $hari->value)
            ->where('aktif', true)
            ->orderBy('jam_mulai')
            ->get();

        $strings = $rows->map(fn ($j) => substr((string) $j->jam_mulai, 0, 5).'-'.substr((string) $j->jam_selesai, 0, 5))
            ->unique()
            ->values()
            ->all();

        $now = now()->format('H:i:s');
        $diLuar = true;
        foreach ($rows as $j) {
            if ($now >= (string) $j->jam_mulai && $now <= (string) $j->jam_selesai) {
                $diLuar = false;
                break;
            }
        }

        if ($rows->isEmpty()) {
            $diLuar = true;
        }

        return [$strings, $diLuar];
    }
}
