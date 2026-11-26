<?php

namespace App\Services;

use App\Mail\NotifikasiKunjungan;
use App\Models\Kunjungan;
use App\Services\Notifikasi\WhatsAppGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class NotifikasiService
{
    public function __construct(private readonly WhatsAppGateway $whatsapp) {}

    public function kirim(Kunjungan $kunjungan): void
    {
        $kunjungan->loadMissing(['tamu', 'unit', 'pegawai']);

        $pegawai = $kunjungan->pegawai;

        if ($pegawai === null) {
            throw new RuntimeException('Kunjungan belum memiliki pegawai yang ditugaskan.');
        }

        $this->whatsapp->kirim($pegawai->no_wa, $this->pesan($kunjungan));

        if (config('notifikasi.email.enabled') && filled($pegawai->email)) {
            try {
                Mail::to($pegawai->email)->send(new NotifikasiKunjungan($kunjungan));
            } catch (Throwable $e) {
                Log::warning('Email notifikasi gagal dikirim.', [
                    'pegawai_id' => $pegawai->id,
                    'error' => $e::class,
                ]);
            }
        }
    }

    public function pesan(Kunjungan $kunjungan): string
    {
        $kunjungan->loadMissing(['tamu', 'unit', 'pegawai']);

        $keperluan = $kunjungan->ringkasan_ai ?: $kunjungan->keperluan;

        return sprintf(
            "Halo %s, ada tamu %s yang ingin bertemu Anda di %s.\nKeperluan: %s\nKode kunjungan: %s",
            $kunjungan->pegawai?->nama ?? 'Petugas',
            $kunjungan->tamu?->nama ?? 'tamu',
            $kunjungan->unit?->lokasi_ruang ?? 'ruang tamu',
            $keperluan,
            $kunjungan->kode_publik,
        );
    }
}
