# Fase 5 — Dashboard Staf

Dokumen ini mencatat implementasi Fase 5 pada proyek Buku Tamu Digital.

## Tujuan

Resepsionis, admin, dan pegawai dapat mengelola siklus kunjungan dari dashboard.

## Perubahan yang Dilakukan

### 1. Controller Dashboard

- `App\Http\Controllers\Dashboard\KunjunganController`:
  - `index`: antrean kunjungan dengan statistik dan filter (status, klasifikasi, perlu ditinjau, tanpa pegawai, menunggu > 15 menit, unit).
  - `show`: detail kunjungan.
  - `klasifikasi`: koreksi unit/kategori/ringkasan, set `klasifikasi_status = manual`, isi `diubah_oleh`; jika unit berubah, kosongkan `pegawai_id` dan tugaskan ulang + notifikasi.
  - `pegawai`: ganti pegawai + notifikasi ulang.
  - `status`: transisi status sesuai aturan, isi `waktu_dilayani`/`waktu_selesai`, admin override dengan `catatan` wajib.
- `App\Http\Controllers\Dashboard\TugasController`: tugas pegawai (hanya milik sendiri).

### 2. Rute

- `GET /dashboard/kunjungan`
- `GET /dashboard/kunjungan/{kunjungan}`
- `PATCH /dashboard/kunjungan/{kunjungan}/klasifikasi`
- `PATCH /dashboard/kunjungan/{kunjungan}/pegawai`
- `PATCH /dashboard/kunjungan/{kunjungan}/status`
- `GET /dashboard/tugas`

### 3. Frontend

- Halaman `Dashboard/Kunjungan/Index`, `Dashboard/Kunjungan/Show`, `Dashboard/Tugas/Index`.
- Base controller menambahkan trait `AuthorizesRequests` agar policy dapat dipakai.

### 4. Otorisasi

- Policy kunjungan dipakai untuk view/create/update/klasifikasi/assign/status.
- Pegawai hanya melihat dan mengubah tugas miliknya.

## Validasi yang Dijalankan

- `php artisan test`: 76 tests lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --format agent`: format diperbaiki.

## Catatan

- Queue worker tetap diperlukan untuk notifikasi setelah koreksi/penugasan.
- Admin override status non-standar wajib menyertakan `catatan`.
