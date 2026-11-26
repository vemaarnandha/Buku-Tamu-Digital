<?php

namespace App\Services\Notifikasi;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FonnteWhatsAppGateway implements WhatsAppGateway
{
    public function kirim(string $noWhatsapp, string $pesan): void
    {
        $url = (string) config('notifikasi.whatsapp.url');
        $token = (string) config('notifikasi.whatsapp.token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('Konfigurasi WhatsApp (Fonnte) belum lengkap.');
        }

        $response = Http::withHeaders(['Authorization' => $token])
            ->asForm()
            ->timeout((int) config('notifikasi.whatsapp.timeout', 15))
            ->post($url, [
                'target' => $noWhatsapp,
                'message' => $pesan,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Pengiriman WhatsApp gagal dengan status '.$response->status().'.');
        }
    }
}
