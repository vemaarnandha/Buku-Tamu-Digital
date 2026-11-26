# Rencana Implementasi Buku Tamu Digital

> **Status:** Fase 0, Fase 1, Fase 2, Fase 3, Fase 4, dan Fase 5 selesai dieksekusi (dashboard staf)  
> **Tanggal:** 26 November 2026  
> **Acuan utama:** [README](../README.md) dan [Desain Sistem Buku Tamu Digital](./Desain%20Sistem%20Buku%20Tamu%20Digital.md)  
> **Catatan:** gambar `rancangan_app` lama tidak dipakai sebagai acuan sampai diperbarui. Dokumen repo saat ini yang menjadi source of truth.

---

## 1. Ringkasan Kondisi Repo

Repo saat ini masih berupa skeleton Laravel:

- Laravel 13, PHP 8.3+, PHPUnit, Vite 8, Tailwind CSS 4.
- Inertia 3, React 19, TypeScript, dan shadcn/ui sudah terpasang.
- Belum ada tabel domain, model, policy, job, service AI, atau notifikasi.
- `routes/web.php` sudah memakai Inertia dan merender halaman `Welcome`.
- Auth masih auth dasar Laravel; belum ada UI login dan pembatasan peran.
- Konfigurasi PostgreSQL/Neon sudah disiapkan di `config/database.php` dan `.env.example`, tetapi `.env` masih memakai SQLite dan migrasi belum dijalankan.
- `phpunit.xml` masih memakai SQLite in-memory, sedangkan produksi memakai PostgreSQL/Neon.
- Dokumen desain sudah dipindahkan ke `docs/Desain Sistem Buku Tamu Digital.md`; tautan di `README.md` sudah disesuaikan.

### Target database

Delapan tabel:

1. `users`
2. `units`
3. `jam_layanan`
4. `pegawai`
5. `tamu`
6. `kunjungan`
7. `faq`
8. `rating`

Tidak ada tabel `lokasi_qr`, `ringkasan_mingguan`, kolom `ttd_digital`, atau kolom `kode_qr`.

---

## 2. Prinsip Implementasi

1. **Dokumen repo menjadi source of truth.** Implementasi mengikuti ERD, aturan bisnis, dan rute Inertia yang sudah disepakati.
2. **Vertical slice.** Bangun alur tamu submit → AI → penugasan → status terlebih dahulu, lalu dashboard staf.
3. **AI dan notifikasi lewat queue.** Request tamu tidak menunggu model AI.
4. **Semua halaman memakai Inertia.** Tidak ada REST API terpisah.
5. **Data master memakai status aktif.** Hindari hard delete untuk data yang sudah direferensikan kunjungan.
6. **Test integrasi memakai PostgreSQL.** SQLite hanya dipakai jika benar-benar diperlukan.
7. **Tanpa scheduler di MVP.** Ringkasan mingguan dihitung on-demand dan di-cache.

---

## 3. Keputusan yang Perlu Dikonfirmasi

| No | Keputusan | Rekomendasi | Dampak |
|---|---|---|---|
| 1 | Scaffolding auth | Laravel Breeze React + TypeScript, registrasi publik dinonaktifkan | Mempercepat login, middleware, dan layout auth |
| 2 | Bahasa frontend | TypeScript | Sesuai dokumen; butuh `tsconfig` dan tipe props Inertia |
| 3 | Database test | PostgreSQL lokal atau Neon branch khusus test | Lebih dekat ke produksi; SQLite berisiko beda perilaku |
| 4 | Notifikasi MVP | Driver `log` + email dulu, Fonnte/WhatsApp setelah kredensial tersedia | Alur tetap bisa diuji tanpa akun gateway |
| 5 | Ekspor rekap | CSV terlebih dahulu, lalu Excel/PDF | Mengurangi dependensi awal |
| 6 | Koreksi status admin | Admin boleh override dengan `catatan` wajib | Menangani salah input tanpa melanggar audit |
| 7 | Jam layanan di form | Tampilkan jam resepsionis/global di form; jam unit di halaman status | Unit baru diketahui setelah AI mengklasifikasi |
| 8 | Tombol “Selesai” di halaman publik | Hanya di dashboard staf; publik cukup status + rating | Menghindari tamu mengubah status sendiri |

---

## 4. Fase Implementasi

### Fase 0 — Fondasi dan Environment

**Tujuan:** aplikasi bisa boot, login, terhubung ke Neon, dan render halaman Inertia.

Pekerjaan:

- [x] Install Laravel Boost sesuai `AGENTS.md`, lalu baca ulang panduan yang dihasilkan.
- [x] Install Inertia.js server adapter.
- [x] Install React 19 + TypeScript + `@vitejs/plugin-react`.
- [x] Install dan konfigurasi shadcn/ui dengan Tailwind 4.
- [x] Install Laravel Breeze React + TypeScript untuk scaffolding auth.
- [x] Nonaktifkan route registrasi publik.
- [x] Buat layout auth dan layout dashboard dasar.
- [x] Isi `.env` Neon: `DB_URL`, `DB_SCHEMA=public`, `DB_SSLMODE=require`.
- [x] Pastikan ekstensi `pdo_pgsql` aktif.
- [x] Siapkan database test PostgreSQL dan sesuaikan `phpunit.xml`.
- [x] Perbarui tautan dokumen di `README.md` ke folder `docs/`.

**Deliverable:**

- Halaman login Inertia bisa dibuka.
- `php artisan migrate` berhasil ke Neon.
- `npm run build` berhasil.
- Health check `/up` berhasil.

**Validasi:**

- Login/logout berjalan.
- Halaman Inertia pertama tampil.
- Tidak ada kredensial yang masuk ke Git.

**Depends on:** keputusan auth dan test database.

---

### Fase 1 — Database, Model, dan Seeder

**Tujuan:** skema database dan domain model sesuai ERD.

Pekerjaan:

- [x] Ubah migrasi `users`: tambah `role`, `aktif`.
- [x] Buat migrasi `units`.
- [x] Buat migrasi `jam_layanan`.
- [x] Buat migrasi `pegawai`.
- [x] Buat migrasi `tamu`.
- [x] Buat migrasi `kunjungan`.
- [x] Buat migrasi `faq`.
- [x] Buat migrasi `rating`.
- [x] Buat enum PHP:
  - `Role`
  - `StatusKunjungan`
  - `KlasifikasiStatus`
  - `KategoriTamu`
  - `Hari`
- [x] Buat model Eloquent + relasi + cast enum.
- [x] Buat factory untuk unit, pegawai, tamu, kunjungan, FAQ, rating.
- [x] Buat seeder:
  - unit contoh,
  - jam layanan,
  - user per peran,
  - pegawai,
  - FAQ,
  - contoh kunjungan.
- [x] Buat utility normalisasi nomor HP (`08xx`, `+62xx`, `62xx` → `62xx`).
- [x] Tambahkan unique index dan index pencarian yang diperlukan.

**Aturan skema penting:**

- `tamu.no_hp` unik dan ternormalisasi.
- `kunjungan.kode_publik` unik dan acak.
- `rating.kunjungan_id` unik.
- `kunjungan.unit_id` dan `pegawai_id` nullable.
- `kunjungan.diubah_oleh` FK ke `users`.
- `kunjungan.status`: `menunggu`, `dilayani`, `selesai`, `tidak_terlayani`.
- `kunjungan.klasifikasi_status`: `diproses`, `otomatis`, `manual`, `perlu_ditinjau`.
- Gunakan `timestampTz` untuk kolom waktu.
- Tidak ada `ttd_digital`, `kode_qr`, `lokasi_qr_id`, atau tabel `ringkasan_mingguan`.

**Deliverable:**

- `php artisan migrate:fresh --seed` berhasil.
- Semua relasi model bisa diakses.
- Data demo cukup untuk menguji alur tamu.

**Validasi:**

- Test relasi model.
- Test normalisasi nomor HP.
- Test unique constraint rating dan nomor HP.

---

### Fase 2 — Auth, Peran, dan Otorisasi

**Tujuan:** setiap peran hanya bisa mengakses halaman dan aksi yang diizinkan.

Pekerjaan:

- [x] Middleware `role` atau policy per model.
- [x] Redirect `/dashboard` sesuai peran:
  - admin/resepsionis → antrean kunjungan,
  - pegawai → tugas sendiri,
  - pimpinan → rekap/ringkasan.
- [x] Policy untuk kunjungan, master data, user, dan laporan.
- [x] Nonaktifkan registrasi publik.
- [x] Seed akun demo lokal untuk setiap peran.
- [x] Pastikan user `aktif = false` tidak bisa login.

**Matriks akses mengikuti §4.8 dokumen desain:**

- Tamu: form, status, rating.
- Pegawai: tugas sendiri.
- Resepsionis/admin: antrean, status, koreksi, penugasan, master data.
- Admin: user dan peran.
- Pimpinan: rekap/ringkasan read-only.

**Deliverable:**

- Login per peran berjalan.
- Akses yang tidak diizinkan menghasilkan 403.
- Dashboard awal sesuai peran.

**Validasi:**

- Feature test matriks otorisasi.
- Test user nonaktif tidak bisa login.

---

### Fase 3 — Alur Tamu Publik

**Tujuan:** tamu bisa check-in dari QR/kiosk dan melihat status kunjungan.

Pekerjaan:

- [x] Halaman `GET /` untuk form kiosk/QR resepsionis.
- [x] Endpoint Inertia `GET /kunjungan/create` untuk lookup `no_hp` (partial reload).
- [x] `POST /kunjungan` untuk menyimpan tamu baru/lama dan kunjungan.
- [x] Halaman `GET /kunjungan/{kode_publik}/status`.
- [x] Polling status setiap ±3 detik selama `klasifikasi_status = diproses`.
- [x] Tampilkan kondisi:
  - sedang diproses,
  - berhasil diklasifikasi + arahan ruang tamu unit,
  - perlu ditinjau,
  - tidak terlayani,
  - selesai + tautan rating.
- [x] Rate limit form publik.
- [x] Tombol submit dinonaktifkan setelah diklik.
- [x] Validasi server untuk mencegah submit ganda.
- [x] Tampilkan peringatan di luar jam layanan.
- [x] QR statis di meja resepsionis cukup mengarah ke `/`; tidak perlu tabel lokasi QR.

**Perilaku tamu lama:**

- Nomor HP ditemukan → identitas terisi otomatis.
- Identitas boleh diperbarui; master `tamu` ikut diperbarui.
- `keperluan` selalu kosong untuk kunjungan baru.
- Tampilkan “kunjungan selesai ke-N”.

**Deliverable:**

- Tamu baru dan tamu lama bisa submit.
- Halaman status menampilkan `kode_publik`, bukan ID berurutan.

**Validasi:**

- Feature test tamu baru/lama.
- Test normalisasi nomor HP.
- Test rate limit.
- Test halaman status tidak bisa diakses dengan ID berurutan.

---

### Fase 4 — AI, Penugasan, dan Notifikasi

**Tujuan:** kunjungan otomatis mendapat unit, kategori, ringkasan, pegawai, dan notifikasi.

Pekerjaan:

- [x] Buat `config/ai.php` yang membaca:
  - `AI_BASE_URL`
  - `AI_API_KEY`
  - `AI_MODEL`
  - `AI_FALLBACK_MODELS`
  - `AI_TIMEOUT`
  - `AI_MAX_RETRIES`
- [x] Buat `AiClassifier` dengan HTTP client Laravel.
- [x] Buat DTO hasil klasifikasi:
  - `unit_kode`
  - `kategori`
  - `ringkasan`
  - `confidence`
- [x] Implementasikan fallback model:
  - timeout,
  - HTTP 429,
  - HTTP 5xx,
  - JSON tidak valid.
- [x] Validasi hasil:
  - unit dikenal,
  - confidence minimal 0.70,
  - kategori dan ringkasan tidak kosong.
- [x] Buat job `KlasifikasiKunjungan`.
- [x] Job mengisi unit/kategori/ringkasan dan `klasifikasi_status`.
- [x] Buat `PenugasanService`:
  - unit sama,
  - `aktif = true`,
  - `bisa_menerima_tamu = true`,
  - beban aktif paling sedikit,
  - `terakhir_ditugaskan_pada` paling lama,
  - `id` paling kecil.
- [x] Gunakan transaksi dan lock saat memilih pegawai.
- [x] Jika tidak ada pegawai: `pegawai_id = null` dan tampil di filter “belum ada pegawai/perlu ditinjau”.
- [x] Buat job `KirimNotifikasiKunjungan`.
- [x] MVP notifikasi: driver `log` + email.
- [x] Siapkan interface untuk Fonnte/WhatsApp Cloud API.
- [x] Isi `notifikasi_terkirim_pada` jika berhasil.
- [x] Retry/backoff dan catat kegagalan.
- [x] Halaman status polling sampai klasifikasi selesai.

**Deliverable:**

- Submit tamu → job AI → unit → pegawai → notifikasi.
- Status page menampilkan ruang tamu unit dan nama pegawai.
- AI gagal tidak membuat kunjungan hilang; status menjadi `perlu_ditinjau`.

**Validasi:**

- `Http::fake()` untuk AI sukses, JSON rusak, timeout, 429, dan fallback.
- Test urutan penugasan.
- Test tidak ada pegawai.
- Test job notifikasi dan retry.
- Test queue worker mati: kunjungan tetap `diproses`.

---

### Fase 5 — Dashboard Staf

**Tujuan:** resepsionis, admin, dan pegawai bisa mengelola siklus kunjungan.

Pekerjaan:

- [x] Layout dashboard dengan navigasi sesuai peran.
- [x] Dashboard resepsionis/admin:
  - kartu statistik: menunggu, dilayani, selesai, tidak terlayani,
  - filter: perlu ditinjau, belum ada pegawai, menunggu > 15 menit,
  - daftar kunjungan.
- [x] Halaman detail kunjungan.
- [x] Koreksi klasifikasi:
  - ubah unit/kategori/ringkasan,
  - set `klasifikasi_status = manual`,
  - isi `diubah_oleh`.
- [x] Jika unit diubah, kosongkan `pegawai_id` dan tugaskan ulang.
- [x] Ganti pegawai + kirim notifikasi ulang.
- [x] Ubah status sesuai aturan §4.1:
  - `menunggu` → `dilayani` (wajib ada pegawai),
  - `dilayani` → `selesai`,
  - `menunggu`/`dilayani` → `tidak_terlayani` (alasan wajib).
- [x] Isi `waktu_dilayani` dan `waktu_selesai` otomatis.
- [x] Dashboard pegawai: hanya tugas sendiri.
- [x] Tombol “Tandai dilayani” dan “Tandai selesai”.
- [x] Admin correction dengan `catatan` wajib.
- [x] Tampilkan status notifikasi dan alasan gagal.

**Deliverable:**

- Kunjungan bisa diproses dari `menunggu` sampai `selesai` atau `tidak_terlayani`.
- Pegawai hanya melihat tugasnya sendiri.
- Koreksi dan penggantian pegawai tercatat.

**Validasi:**

- Feature test transisi status.
- Test akses pegawai ke tugas orang lain.
- Test koreksi klasifikasi dan penggantian pegawai.
- Test admin correction dengan catatan.

---

### Fase 6 — Rating dan Asisten FAQ

**Tujuan:** tamu bisa menilai kunjungan selesai dan bertanya lewat FAQ.

Pekerjaan:

- [ ] Halaman `GET /rating`.
- [ ] `POST /rating` untuk menyimpan rating.
- [ ] Cari kunjungan `selesai` hari itu berdasarkan `no_hp`.
- [ ] Ambil kunjungan terbaru yang belum punya rating.
- [ ] Simpan nilai 1–5 dan komentar opsional.
- [ ] Unique constraint mencegah rating ganda.
- [ ] Tampilkan pesan jika tidak ada kunjungan selesai.
- [ ] Halaman/komponen asisten FAQ.
- [ ] `POST /faq/tanya`.
- [ ] Pilih maksimal 8 FAQ aktif (global + unit) berdasarkan skor kata kunci.
- [ ] Kirim ke AI dengan instruksi hanya menjawab dari FAQ.
- [ ] Kembalikan jawaban + ID FAQ sumber.
- [ ] Rate limit endpoint FAQ dan rating.

**Deliverable:**

- Rating hanya untuk kunjungan `selesai`.
- Satu kunjungan hanya punya satu rating.
- FAQ menjawab dari data FAQ, bukan mengarang.

**Validasi:**

- Test rating ganda.
- Test kunjungan belum selesai.
- Test nomor HP tidak ditemukan.
- Test FAQ tanpa hasil.
- Test rate limit.

---

### Fase 7 — Master Data dan User

**Tujuan:** resepsionis/admin bisa mengelola data tanpa seeder.

Pekerjaan:

- [ ] CRUD unit.
- [ ] CRUD pegawai.
- [ ] CRUD jam layanan.
- [ ] CRUD FAQ.
- [ ] CRUD user dan peran (admin).
- [ ] Validasi:
  - kode unit unik,
  - `user_id` pegawai unik,
  - jam mulai < jam selesai,
  - hari valid,
  - role valid.
- [ ] Gunakan `aktif = false` untuk menonaktifkan data yang sudah dipakai.
- [ ] Hubungkan `pegawai.user_id` agar pegawai bisa login.

**Deliverable:**

- Semua data master bisa dikelola dari dashboard.
- Data yang sudah dipakai kunjungan tidak hilang.

**Validasi:**

- Feature test CRUD.
- Test otorisasi.
- Test validasi dan unique constraint.

---

### Fase 8 — Rekap dan Ringkasan Mingguan

**Tujuan:** pimpinan bisa melihat rekap dan ringkasan.

Pekerjaan:

- [ ] Halaman rekap dengan filter periode, unit, status, kategori.
- [ ] Agregat:
  - total kunjungan,
  - per unit,
  - kategori terbanyak,
  - rata-rata rating,
  - jumlah tidak terlayani.
- [ ] Grafik Recharts.
- [ ] Ekspor CSV terlebih dahulu.
- [ ] Halaman ringkasan mingguan.
- [ ] Hitung agregat minggu sebelumnya.
- [ ] Kirim agregat ke AI untuk narasi.
- [ ] Cache hasil ±1 jam.
- [ ] Tombol “coba lagi” untuk membersihkan cache.
- [ ] Jika AI gagal, tetap tampilkan statistik angka.
- [ ] Tidak ada tabel `ringkasan_mingguan` dan tidak ada scheduler.

**Deliverable:**

- Pimpinan melihat rekap dan narasi mingguan.
- Ringkasan tidak memanggil AI setiap kali halaman dibuka.

**Validasi:**

- Test agregat.
- Test cache.
- Test AI gagal.
- Test akses read-only pimpinan.

---

### Fase 9 — Hardening, Testing, dan Deployment

**Tujuan:** MVP siap dipakai dan aman.

Pekerjaan:

- [ ] Rate limit form, rating, dan FAQ.
- [ ] Validasi input dan sanitasi teks keperluan sebelum masuk prompt AI.
- [ ] Log tanpa data pribadi berlebihan.
- [ ] Failed job dan retry/backoff.
- [ ] Queue worker berjalan.
- [ ] Test suite lengkap.
- [ ] `npm run build`.
- [ ] `php artisan migrate --force`.
- [ ] `config:cache`, `route:cache`.
- [ ] Supervisor untuk queue worker.
- [ ] HTTPS dan secure cookie.
- [ ] Backup Neon.
- [ ] Manual QA kiosk dan dashboard.

**Deliverable:**

- Aplikasi bisa di-deploy.
- Queue worker dan antrean berjalan stabil.
- Tidak ada kredensial di repository.

---

## 5. Pemetaan Modul

| Lapisan | Komponen |
|---|---|
| Public controller | `Guest\KunjunganController`, `RatingController`, `FaqController` |
| Dashboard controller | `DashboardController`, `KunjunganController`, `TugasController`, `UnitController`, `PegawaiController`, `JamLayananController`, `FaqController`, `UserController`, `RekapController`, `RingkasanController` |
| Service | `PhoneNormalizer`, `AiClassifier`, `PenugasanService`, `KunjunganStatusService`, `NotificationService`, `RekapService`, `FaqService` |
| Job | `KlasifikasiKunjungan`, `KirimNotifikasiKunjungan` |
| Enum | `Role`, `StatusKunjungan`, `KlasifikasiStatus`, `KategoriTamu`, `Hari` |
| Halaman publik | `Guest/Index`, `Guest/Status`, `Rating/Index` |
| Halaman dashboard | `Dashboard/Index`, `Dashboard/Kunjungan/Index`, `Dashboard/Kunjungan/Show`, `Dashboard/Tugas/Index`, halaman CRUD master, `Dashboard/Rekap/Index`, `Dashboard/Ringkasan/Index` |
| Auth | `Auth/Login` dan layout auth |

---

## 6. Rencana Pengujian

### Unit test

- Normalisasi nomor HP.
- Parsing JSON AI.
- Fallback model.
- Confidence rendah.
- Urutan penugasan pegawai.
- Transisi status kunjungan.
- Agregat rekap.

### Feature test

- Tamu baru dan tamu lama submit.
- `kode_publik` tidak berurutan.
- AI job sukses/gagal.
- Penugasan dan notifikasi.
- Rating hanya untuk `selesai`.
- Rating tidak bisa ganda.
- Matriks otorisasi.
- CRUD master data.
- Ringkasan memakai cache dan fallback saat AI gagal.

### Frontend

- `npm run build`.
- Inertia page render.
- Manual QA kiosk di layar kecil dan tablet.
- Loading state saat AI diproses.
- Empty state saat tidak ada data.

### Database test

- Gunakan PostgreSQL, bukan SQLite, untuk feature test yang menyentuh constraint, timestamp, dan query agregat.
- Jika memakai Neon, gunakan branch/database khusus test.

---

## 7. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Model AI gratis lambat/rate-limited | Status tamu lama diproses | Queue, timeout, fallback model, `perlu_ditinjau` |
| Neon cold start | Request pertama lambat | Loading state, monitoring, koneksi direct |
| Dua tamu mendapat pegawai sama | Beban tidak seimbang | Transaksi + lock di `PenugasanService` |
| Breeze/Inertia/Tailwind 4 tidak kompatibel | Fase 0 terhambat | Spike kecil di awal, verifikasi build |
| Kredensial WhatsApp belum ada | Notifikasi tidak terkirim | Driver `log` + email dulu, interface siap |
| Test SQLite berbeda dari PostgreSQL | Bug lolos ke produksi | Test integrasi memakai PostgreSQL |
| Data pribadi di log | Risiko privasi | Log metadata, hindari nomor HP/keperluan mentah |
| Gambar rancangan berbeda dari dokumen | Implementasi ambigu | Dokumen repo jadi acuan; gambar diperbarui terpisah |

---

## 8. Definition of Done MVP

- [ ] Tamu bisa scan QR/kiosk, isi form, dan mendapat arahan ruang tamu unit.
- [ ] AI menentukan unit; sistem memilih satu pegawai dan mengirim notifikasi.
- [ ] Pegawai/resepsionis bisa mengubah status sampai `selesai` atau `tidak_terlayani`.
- [ ] Rating hanya bisa diberikan sekali untuk kunjungan `selesai`.
- [ ] Resepsionis/admin bisa mengelola master data.
- [ ] Pimpinan bisa melihat rekap dan ringkasan mingguan.
- [ ] Semua test lulus.
- [ ] `npm run build` lulus.
- [ ] Tidak ada kredensial di repository.
- [ ] Queue worker dan failed job termonitor.

---

## 9. Urutan Eksekusi yang Disarankan

1. Fase 0 — fondasi dan environment.
2. Fase 1 — database dan model.
3. Fase 2 — auth dan otorisasi.
4. Fase 3 — alur tamu publik.
5. Fase 4 — AI, penugasan, notifikasi.
6. Fase 5 — dashboard staf.
7. Fase 6 — rating dan FAQ.
8. Fase 7 — master data dan user.
9. Fase 8 — rekap dan ringkasan.
10. Fase 9 — hardening dan deployment.

Fase 3 dan 4 sebaiknya dikerjakan sebagai satu vertical slice agar alur inti bisa diuji end-to-end sebelum memperluas dashboard.
