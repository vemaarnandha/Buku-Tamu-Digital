# Fase 2 — Auth, Peran, dan Otorisasi

Dokumen ini mencatat implementasi Fase 2 pada proyek Buku Tamu Digital.

## Tujuan

Memastikan setiap peran hanya bisa mengakses halaman dan aksi yang diizinkan, login memperhatikan status `aktif`, dan `/dashboard` diarahkan sesuai peran.

## Perubahan yang Dilakukan

### 1. Middleware Peran

- `App\Http\Middleware\EnsureRole` dengan alias `role`.
- Menerima satu atau beberapa peran (dipisah koma) dan mengembalikan 403 jika peran pengguna tidak termasuk.
- Pengguna yang belum login mendapat 401.

### 2. Login dan Status Aktif

- `LoginRequest::authenticate()` menambahkan kondisi `aktif = true` pada `Auth::attempt()`, sehingga pengguna nonaktif tidak bisa login.
- Registrasi publik tetap nonaktif (route `register` tidak terdaftar).

### 3. Redirect Dashboard per Peran

- `App\Http\Controllers\DashboardController` (invokable) mengarahkan:
  - `admin`/`resepsionis` → `dashboard.kunjungan.index`,
  - `pegawai` → `dashboard.tugas.index`,
  - `pimpinan` → `dashboard.rekap.index`.
- Jika rute tujuan belum tersedia, fallback ke halaman `Dashboard` dengan prop `role`.

### 4. Policy dan Otorisasi

Policy terdaftar di `AppServiceProvider`:
- `KunjunganPolicy`
- `UnitPolicy`
- `PegawaiPolicy`
- `JamLayananPolicy`
- `FaqPolicy`
- `UserPolicy`

Matriks akses mengikuti §4.8 dokumen desain (resepsionis/admin untuk antrean & master data, pegawai untuk tugas sendiri, admin untuk pengguna, pimpinan read-only rekap).

### 5. Rute Terproteksi

`routes/web.php` menambahkan grup rute dashboard dengan middleware `role:` untuk area:
kunjungan, tugas, rekap, ringkasan, units, pegawai, jam-layanan, faq, dan users.

### 6. Pengujian

- `tests/Feature/Auth/RoleAuthorizationTest.php`:
  - pengguna nonaktif tidak bisa login,
  - redirect `/dashboard` sesuai peran,
  - middleware `role` mengembalikan 403/200,
  - tamu (guest) diarahkan ke login.

## Validasi yang Dijalankan

- `php artisan test`: 35 tests, 72 assertions, semua lulus.
- `vendor/bin/pint --dirty --format agent`: format kode diperbaiki.

## Catatan

- Rute dashboard masih berupa placeholder yang merender halaman `Dashboard`; implementasi penuh dilakukan pada fase berikutnya.
- Policy untuk rekap/ringkasan ditangani lewat middleware peran; gate khusus dapat ditambahkan saat Fase 8.
