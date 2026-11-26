# Fase 7 — Master Data dan User

Dokumen ini mencatat implementasi Fase 7 pada proyek Buku Tamu Digital.

## Tujuan

Resepsionis/admin dapat mengelola data master dan user dari dashboard.

## Perubahan yang Dilakukan

### Controller Resource

- `Dashboard\UnitController`
- `Dashboard\PegawaiController`
- `Dashboard\JamLayananController`
- `Dashboard\FaqController`
- `Dashboard\UserController`

Masing-masing menyediakan index/create/store/show/edit/update/destroy dengan validasi.

### Rute

Resource routes di bawah `/dashboard`:
- `/dashboard/units`
- `/dashboard/pegawai`
- `/dashboard/jam-layanan`
- `/dashboard/faq`
- `/dashboard/users` (admin)

### Frontend

Halaman minimal untuk setiap resource (Index/Create/Show/Edit).

## Validasi yang Dijalankan

- `php artisan test`: 76 tests lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --format agent`: format diperbaiki.

## Catatan

- Data yang sudah dipakai kunjungan dinonaktifkan (`aktif = false`) alih-alih dihapus.
- `pegawai.user_id` unik dan menghubungkan pegawai ke akun login.
