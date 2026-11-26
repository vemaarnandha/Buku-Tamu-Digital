<?php

namespace App\Services\Notifikasi;

use Illuminate\Support\Facades\Log;

class LogWhatsAppGateway implements WhatsAppGateway
{
    public function kirim(string $noWhatsapp, string $pesan): void
    {
        Log::info('Notifikasi WhatsApp (driver log).', [
            'tujuan' => $this->mask($noWhatsapp),
            'pesan' => $pesan,
        ]);
    }

    protected function mask(string $noWhatsapp): string
    {
        if (strlen($noWhatsapp) <= 7) {
            return '****';
        }

        return substr($noWhatsapp, 0, 4).'****'.substr($noWhatsapp, -3);
    }
}
