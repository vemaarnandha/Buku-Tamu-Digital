# Fase 4 — AI, Penugasan, dan Notifikasi

Dokumen ini mencatat implementasi Fase 4 pada proyek Buku Tamu Digital.

## Tujuan

Kunjungan otomatis mendapat unit, kategori, ringkasan, pegawai, dan notifikasi melalui queue.

## Perubahan yang Dilakukan

### 1. Konfigurasi AI dan Notifikasi

- `config/ai.php`: membaca `AI_BASE_URL`, `AI_API_KEY`, `AI_MODEL`, `AI_FALLBACK_MODELS`, `AI_TIMEOUT`, `AI_MAX_RETRIES`, serta threshold confidence minimal `0.70`.
- `config/notifikasi.php`: driver WhatsApp (`log`/`fonnte`), URL/token/timeout, dan toggle email cadangan.
- `.env.example`: ditambah `WHATSAPP_DRIVER`, `WHATSAPP_TIMEOUT`, `NOTIFIKASI_EMAIL_ENABLED`.

### 2. Klasifikasi AI

- `App\Services\Ai\AiClassifier` memakai HTTP client Laravel dengan endpoint kompatibel OpenAI.
- `App\Services\Ai\HasilKlasifikasi` (DTO) untuk `unit_kode`, `kategori`, `ringkasan`, `confidence`, termasuk parsing JSON yang robust (code fence, JSON tersembunyi).
- Fallback model berurutan untuk timeout, HTTP 429, HTTP 5xx, dan JSON tidak valid.
- Validasi hasil dilakukan di job: unit harus ada dan aktif, confidence minimal `0.70`, kategori dan ringkasan tidak kosong.

### 3. Penugasan Pegawai

- `App\Services\PenugasanService` memilih satu pegawai berdasarkan unit, `aktif`, `bisa_menerima_tamu`, beban aktif paling sedikit, `terakhir_ditugaskan_pada` paling lama, lalu `id` paling kecil.
- Pemilihan dibungkus transaksi dan `lockForUpdate()`.

### 4. Job dan Notifikasi

- `App\Jobs\KlasifikasiKunjungan`: memanggil AI, validasi, menyimpan unit/kategori/ringkasan, menugaskan pegawai, lalu mendispatch job notifikasi. Jika AI gagal, job di-retry; setelah batas retry kunjungan ditandai `perlu_ditinjau`. Jika tidak ada pegawai, `pegawai_id = null` dan status `perlu_ditinjau`.
- `App\Jobs\KirimNotifikasiKunjungan`: mengirim notifikasi WhatsApp (driver `log`/Fonnte) dan email cadangan, lalu mengisi `notifikasi_terkirim_pada`.
- `App\Services\Notifikasi\WhatsAppGateway` (interface) + `LogWhatsAppGateway` + `FonnteWhatsAppGateway`.
- `App\Services\NotifikasiService` dan `App\Mail\NotifikasiKunjungan`.

### 5. Integrasi Form Publik

- `Guest\KunjunganController::store` mendispatch `KlasifikasiKunjungan` setelah kunjungan disimpan.
- Halaman status tetap polling selama `klasifikasi_status = diproses`.

## Validasi yang Dijalankan

- `php artisan test`: 76 tests, 185 assertions, semua lulus.
- `vendor/bin/pint --dirty --format agent`: format kode diperbaiki.
- `npm run build` tidak dijalankan karena tidak ada perubahan frontend (hanya view email).

## Catatan

- Kredensial AI kosong membuat job langsung menandai kunjungan `perlu_ditinjau` tanpa retry, sehingga aplikasi tetap bisa diuji tanpa gateway AI.
- Queue worker wajib dijalankan (`php artisan queue:work`) agar klasifikasi diproses.
- Kegagalan email tidak membatalkan notifikasi WhatsApp yang sudah sukses.
