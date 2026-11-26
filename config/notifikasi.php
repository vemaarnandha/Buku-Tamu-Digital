<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kanal WhatsApp
    |--------------------------------------------------------------------------
    |
    | Driver "log" dipakai untuk MVP tanpa kredensial gateway. Isi driver
    | "fonnte" setelah WHATSAPP_API_URL dan WHATSAPP_API_TOKEN tersedia.
    |
    */

    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'url' => env('WHATSAPP_API_URL'),
        'token' => env('WHATSAPP_API_TOKEN'),
        'timeout' => (int) env('WHATSAPP_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Cadangan
    |--------------------------------------------------------------------------
    |
    | Email dikirim ke pegawai yang ditugaskan jika alamat email tersedia.
    | Kegagalan email tidak membatalkan notifikasi WhatsApp yang sudah sukses.
    |
    */

    'email' => [
        'enabled' => (bool) env('NOTIFIKASI_EMAIL_ENABLED', true),
    ],

];
