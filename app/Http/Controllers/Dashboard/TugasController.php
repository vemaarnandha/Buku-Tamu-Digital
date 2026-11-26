<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TugasController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $pegawaiId = $user->pegawai?->id;

        $kunjungans = Kunjungan::query()
            ->with(['tamu', 'unit', 'pegawai'])
            ->when($pegawaiId, fn ($q) => $q->where('pegawai_id', $pegawaiId))
            ->latest('waktu_datang')
            ->limit(200)
            ->get()
            ->map(fn (Kunjungan $k) => [
                'id' => $k->id,
                'kode_publik' => $k->kode_publik,
                'keperluan' => $k->keperluan,
                'status' => $k->status->value,
                'klasifikasi_status' => $k->klasifikasi_status->value,
                'waktu_datang' => optional($k->waktu_datang)->toIso8601String(),
                'tamu' => $k->tamu ? ['nama' => $k->tamu->nama] : null,
                'unit' => $k->unit ? ['nama' => $k->unit->nama, 'lokasi_ruang' => $k->unit->lokasi_ruang] : null,
            ])
            ->all();

        return Inertia::render('Dashboard/Tugas/Index', [
            'kunjungans' => $kunjungans,
            'kunjungan' => $kunjungans,
            'items' => $kunjungans,
        ]);
    }
}
