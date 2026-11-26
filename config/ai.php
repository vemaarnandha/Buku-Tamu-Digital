<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Gateway (Kilo Gateway / OpenAI-compatible)
    |--------------------------------------------------------------------------
    |
    | Model utama dicoba terlebih dahulu, lalu setiap model fallback secara
    | berurutan. Jika semua model gagal, job klasifikasi akan di-retry.
    |
    */

    'base_url' => env('AI_BASE_URL'),

    'api_key' => env('AI_API_KEY'),

    'model' => env('AI_MODEL'),

    'fallback_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_FALLBACK_MODELS', '')),
    ))),

    'timeout' => (int) env('AI_TIMEOUT', 30),

    'max_retries' => (int) env('AI_MAX_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | Validasi Hasil Klasifikasi
    |--------------------------------------------------------------------------
    */

    'min_confidence' => 0.70,

];
