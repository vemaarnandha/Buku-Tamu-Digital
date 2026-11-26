# Fase 1 — Database, Model, dan Seeder

Dokumen ini mencatat implementasi Fase 1 pada proyek Buku Tamu Digital.

## Tujuan

Menyediakan skema database dan domain model sesuai ERD: `users`, `units`, `jam_layanan`, `pegawai`, `tamu`, `kunjungan`, `faq`, dan `rating`.

## Perubahan yang Dilakukan

### 1. Migrasi

- `users`: ditambah kolom `role` (default `resepsionis`) dan `aktif` (default `true`).
- `units`: `kode` (unique), `nama`, `lokasi_ruang`, `deskripsi` (nullable), `aktif`, timestamps.
- `jam_layanan`: FK ke `units`, `hari`, `jam_mulai`, `jam_selesai`, `aktif`, timestamps, index `(unit_id, hari)`.
- `pegawai`: FK ke `users` (nullable, unique), FK ke `units`, `nama`, `jabatan`, `no_wa`, `email` (nullable), `aktif`, `bisa_menerima_tamu`, `terakhir_ditugaskan_pada` (nullable), timestamps.
- `tamu`: `nama`, `kategori`, `instansi` (nullable), `alamat` (nullable), `no_hp` (unique, ternormalisasi), timestamps.
- `kunjungan`: `kode_publik` (unique), FK `tamu_id`, FK `unit_id` (nullable), FK `pegawai_id` (nullable), `keperluan`, `kategori` (nullable), `ringkasan_ai` (nullable), `status` (default `menunggu`), `klasifikasi_status` (default `diproses`), FK `diubah_oleh` (nullable), `alasan_tidak_terlayani` (nullable), `catatan` (nullable), `waktu_datang`, `waktu_dilayani` (nullable), `waktu_selesai` (nullable), `notifikasi_terkirim_pada` (nullable), timestamps, index `(status, klasifikasi_status)` dan `waktu_datang`.
- `faq`: FK `unit_id` (nullable, global), `pertanyaan`, `jawaban`, `aktif`, timestamps.
- `rating`: FK `kunjungan_id` (unique), `nilai` (1-5), `komentar` (nullable), `created_at`.

Semua kolom waktu memakai `timestamptz`.

### 2. Enum PHP

- `App\Enums\Role`: `admin`, `resepsionis`, `pegawai`, `pimpinan`.
- `App\Enums\StatusKunjungan`: `menunggu`, `dilayani`, `selesai`, `tidak_terlayani`.
- `App\Enums\KlasifikasiStatus`: `diproses`, `otomatis`, `manual`, `perlu_ditinjau`.
- `App\Enums\KategoriTamu`: `perorangan`, `instansi`.
- `App\Enums\Hari`: `senin` sampai `minggu`.

### 3. Model Eloquent

- `Unit`, `JamLayanan`, `Pegawai`, `Tamu`, `Kunjungan`, `Faq`, `Rating`, dan pembaruan `User`.
- Relasi sesuai ERD: unit↔jam layanan, pegawai, FAQ, kunjungan; pegawai↔user dan unit; tamu↔kunjungan; kunjungan↔tamu, unit, pegawai, pengubah, rating.
- Cast enum dan timestamp.

### 4. Factory

- `UnitFactory`, `JamLayananFactory`, `PegawaiFactory`, `TamuFactory`, `KunjunganFactory`, `FaqFactory`, `RatingFactory`, dan pembaruan `UserFactory` (role/aktif default).

### 5. Seeder

- `UnitSeeder`: unit contoh `data`, `umum`, `kepegawaian`.
- `JamLayananSeeder`: jam layanan Senin-Jumat (08.00-12.00 dan 13.00-16.00) per unit.
- `UserSeeder`: akun admin, resepsionis, pimpinan.
- `PegawaiSeeder`: 2 pegawai per unit dengan akun user role pegawai.
- `FaqSeeder`: FAQ global dan per unit.
- `KunjunganSeeder`: contoh kunjungan selesai.
- `DatabaseSeeder` memanggil semua seeder di atas.

### 6. Utility

- `App\Services\PhoneNormalizer` (alias `App\Support\PhoneNormalizer` dan `App\Helpers\PhoneNormalizer`) untuk normalisasi nomor HP ke format `62...`.

### 7. Pengujian

- `tests/Unit/PhoneNormalizerTest.php`.
- `tests/Feature/DomainSchemaTest.php`: unique `no_hp`, unique `rating.kunjungan_id`, dan relasi kunjungan.

## Validasi yang Dijalankan

- `php artisan migrate` ke Neon: berhasil.
- `php artisan db:seed`: berhasil.
- `php artisan test`: 30 tests, 65 assertions, semua lulus.
- `vendor/bin/pint --dirty --format agent`: format kode diperbaiki.

## Catatan

- Tidak ada tabel `lokasi_qr`, `ringkasan_mingguan`, kolom `ttd_digital`, atau `kode_qr`, sesuai keputusan desain.
- Semua kolom waktu memakai `timestamptz` dan `APP_TIMEZONE=Asia/Jakarta`.
PHP
echo done