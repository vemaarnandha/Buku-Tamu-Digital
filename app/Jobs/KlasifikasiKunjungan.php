<?php

namespace App\Jobs;

use App\Enums\KlasifikasiStatus;
use App\Models\Kunjungan;
use App\Models\Unit;
use App\Services\Ai\AiBelumDikonfigurasiException;
use App\Services\Ai\AiClassifier;
use App\Services\Ai\AiClassifierException;
use App\Services\PenugasanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class KlasifikasiKunjungan implements ShouldQueue
{
    use Queueable;

    public function __construct(public Kunjungan $kunjungan) {}

    public function tries(): int
    {
        return max(1, (int) config('ai.max_retries', 2) + 1);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(AiClassifier $classifier, PenugasanService $penugasan): void
    {
        $kunjungan = $this->kunjungan->fresh();

        if ($kunjungan === null || $kunjungan->klasifikasi_status !== KlasifikasiStatus::Diproses) {
            return;
        }

        try {
            $hasil = $classifier->klasifikasi($kunjungan->keperluan);
        } catch (AiBelumDikonfigurasiException $e) {
            Log::warning('Klasifikasi AI dilewati karena konfigurasi belum lengkap.', [
                'kunjungan_id' => $kunjungan->id,
            ]);

            $this->tandaiPerluDitinjau($kunjungan);

            return;
        } catch (AiClassifierException $e) {
            Log::warning('Klasifikasi AI gagal, job akan di-retry.', [
                'kunjungan_id' => $kunjungan->id,
            ]);

            throw $e;
        }

        $unit = Unit::query()
            ->where('kode', $hasil->unitKode)
            ->where('aktif', true)
            ->first();

        if ($unit === null || ! $hasil->memenuhiConfidence((float) config('ai.min_confidence', 0.70))) {
            $this->tandaiPerluDitinjau($kunjungan);

            return;
        }

        $kunjungan->forceFill([
            'unit_id' => $unit->id,
            'kategori' => $hasil->kategori,
            'ringkasan_ai' => $hasil->ringkasan,
        ])->save();

        $pegawai = $penugasan->tugaskan($kunjungan, $unit);

        if ($pegawai === null) {
            $kunjungan->forceFill([
                'klasifikasi_status' => KlasifikasiStatus::PerluDitinjau,
            ])->save();

            return;
        }

        $kunjungan->forceFill([
            'klasifikasi_status' => KlasifikasiStatus::Otomatis,
        ])->save();

        KirimNotifikasiKunjungan::dispatch($kunjungan->fresh());
    }

    public function failed(?Throwable $e): void
    {
        $kunjungan = $this->kunjungan->fresh();

        if ($kunjungan !== null && $kunjungan->klasifikasi_status === KlasifikasiStatus::Diproses) {
            $this->tandaiPerluDitinjau($kunjungan);
        }

        Log::error('Klasifikasi kunjungan gagal setelah semua percobaan.', [
            'kunjungan_id' => $this->kunjungan->id,
            'error' => $e === null ? null : $e::class,
        ]);
    }

    protected function tandaiPerluDitinjau(Kunjungan $kunjungan): void
    {
        $kunjungan->forceFill([
            'klasifikasi_status' => KlasifikasiStatus::PerluDitinjau,
            'unit_id' => null,
            'pegawai_id' => null,
        ])->save();
    }
}
