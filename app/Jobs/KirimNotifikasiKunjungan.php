<?php

namespace App\Jobs;

use App\Models\Kunjungan;
use App\Services\NotifikasiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class KirimNotifikasiKunjungan implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Kunjungan $kunjungan) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(NotifikasiService $notifikasi): void
    {
        $kunjungan = $this->kunjungan->fresh(['tamu', 'unit', 'pegawai']);

        if ($kunjungan === null || $kunjungan->pegawai === null) {
            return;
        }

        if ($kunjungan->notifikasi_terkirim_pada !== null) {
            return;
        }

        $notifikasi->kirim($kunjungan);

        $kunjungan->forceFill(['notifikasi_terkirim_pada' => now()])->save();
    }

    public function failed(?Throwable $e): void
    {
        Log::error('Notifikasi kunjungan gagal dikirim setelah semua percobaan.', [
            'kunjungan_id' => $this->kunjungan->id,
            'error' => $e === null ? null : $e::class,
        ]);
    }
}
