<?php

namespace App\Services;

use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class PenugasanService
{
    /**
     * Pilih pegawai dengan beban aktif paling sedikit, penugasan terakhir
     * paling lama, lalu id paling kecil. Transaksi dan row lock mencegah
     * dua kunjungan mendapat pegawai yang sama secara bersamaan.
     */
    public function tugaskan(Kunjungan $kunjungan, Unit $unit): ?Pegawai
    {
        return DB::transaction(function () use ($kunjungan, $unit): ?Pegawai {
            $pegawai = Pegawai::query()
                ->where('unit_id', $unit->id)
                ->where('aktif', true)
                ->where('bisa_menerima_tamu', true)
                ->withCount([
                    'kunjungan as beban_aktif' => fn ($query) => $query->whereIn('status', [
                        StatusKunjungan::Menunggu,
                        StatusKunjungan::Dilayani,
                    ]),
                ])
                ->orderBy('beban_aktif')
                ->orderByRaw('terakhir_ditugaskan_pada ASC NULLS FIRST')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($pegawai === null) {
                return null;
            }

            $pegawai->forceFill(['terakhir_ditugaskan_pada' => now()])->save();

            $kunjungan->forceFill(['pegawai_id' => $pegawai->id])->save();

            return $pegawai;
        });
    }
}
