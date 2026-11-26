# Fase 3 — Alur Tamu Publik

Dokumen ini mencatat implementasi Fase 3 pada proyek Buku Tamu Digital.

## Tujuan

Tamu bisa check-in dari QR/kiosk dan melihat status kunjungan, termasuk lookup tamu lama, submit kunjungan baru, dan polling status.

## Perubahan yang Dilakukan

### 1. Controller Publik

- `App\Http\Controllers\Guest\KunjunganController`:
  - `create(Request)`: render form kiosk, lookup `no_hp` (ternormalisasi), hitung "kunjungan selesai ke-N", tampilkan jam layanan hari ini dan peringatan di luar jam layanan.
  - `store(StoreKunjunganRequest)`: simpan/perbarui master `tamu`, buat `kunjungan` (`menunggu`, `diproses`) dengan `kode_publik` acak, redirect ke halaman status.
  - `status(string $kodePublik)`: render halaman status dengan data kunjungan, unit, dan pegawai.

### 2. Form Request

- `App\Http\Requests\Guest\StoreKunjunganRequest` untuk validasi input form publik.

### 3. Rute

- `GET /` → form kiosk (`home`).
- `GET /kunjungan/create` → form + lookup `no_hp` (`kunjungan.create`), throttle 20/menit.
- `POST /kunjungan` → simpan kunjungan (`kunjungan.store`), throttle 10/menit.
- `GET /kunjungan/{kode_publik}/status` → halaman status (`kunjungan.status`).

### 4. Halaman Inertia

- `resources/js/Pages/Guest/Index.tsx`: form kiosk (lookup nomor HP, identitas otomatis, peringatan jam layanan, submit).
- `resources/js/Pages/Guest/Status.tsx`: halaman status dengan polling setiap 3 detik selama `klasifikasi_status = diproses` dan tautan rating saat selesai.

### 5. Pengujian

- `tests/Feature/Guest/KunjunganFlowTest.php`:
  - render form,
  - lookup tamu lama + hitung kunjungan selesai,
  - submit tamu baru,
  - update master tamu lama,
  - akses status lewat `kode_publik`,
  - 404 untuk akses lewat ID berurutan,
  - validasi `keperluan` wajib.

## Validasi yang Dijalankan

- `php artisan test`: 42 tests, 118 assertions, semua lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --format agent`: format kode diperbaiki.

## Catatan

- AI, penugasan, dan notifikasi belum diimplementasikan (Fase 4); `klasifikasi_status` tetap `diproses` sampai job Fase 4 tersedia.
- `kode_publik` dibuat acak 12 karakter alfanumerik kecil.
- Rate limit: 20/menit untuk lookup dan 10/menit untuk submit.