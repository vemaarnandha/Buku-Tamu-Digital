<?php

namespace App\Services\Notifikasi;

interface WhatsAppGateway
{
    public function kirim(string $noWhatsapp, string $pesan): void;
}
