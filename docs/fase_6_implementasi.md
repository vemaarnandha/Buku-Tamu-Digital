# Fase 6 — Rating dan Asisten FAQ

Dokumen ini mencatat implementasi Fase 6 pada proyek Buku Tamu Digital.

## Tujuan

Tamu dapat menilai kunjungan selesai dan bertanya melalui asisten FAQ.

## Perubahan yang Dilakukan

### 1. Rating

- `App\Http\Controllers\RatingController`:
  - `GET /rating`: cari kunjungan `selesai` hari itu berdasarkan `no_hp` yang belum punya rating (ambil terbaru).
  - `POST /rating`: simpan nilai 1–5 dan komentar opsional; cegah rating ganda.
- Halaman `Rating/Index`.

### 2. Asisten FAQ

- `App\Services\FaqService`: pilih maksimal 8 FAQ aktif (global + unit) berdasarkan skor kata kunci, kirim ke AI dengan instruksi hanya menjawab dari FAQ, kembalikan jawaban + ID sumber.
- `App\Http\Controllers\FaqController::ask`: endpoint `POST /faq/tanya`.
- Halaman `Faq/Index`.

### 3. Rute dan Rate Limit

- `GET /rating`, `POST /rating`, `POST /faq/tanya` dengan throttle.

## Validasi yang Dijalankan

- `php artisan test`: 76 tests lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --format agent`: format diperbaiki.

## Catatan

- Nomor HP tidak disimpan di tabel rating; hanya dipakai mencari kunjungan.
- Jika AI gagal, fallback ke jawaban FAQ pertama.
