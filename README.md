# 📖 Buku Tamu Digital

Buku tamu digital dengan **asisten resepsionis berbasis AI** untuk instansi pelayanan publik. Tamu memindai **QR di meja resepsionis** atau memakai tablet kiosk, menulis keperluan dengan kalimat biasa, lalu AI menentukan unit tujuan. Sistem menugaskan **satu pegawai**, mengirim notifikasi, dan mengarahkan tamu ke **ruang tamu unit tujuan**. Rating hanya diberikan untuk kunjungan yang sudah **selesai**.

> Dikembangkan oleh satu kelompok yang terdiri dari 6 orang.

**Cocok untuk:** BPS, Perpustakaan Bung Karno, Dinas Pendidikan, Kantor Kecamatan, dan instansi sejenis.

**Dokumen terkait:** [Desain Sistem Buku Tamu Digital](./docs/Desain%20Sistem%20Buku%20Tamu%20Digital.md) — ERD, UML, wireframe, aturan bisnis, dan kontrak AI.

---

## 📌 Status Implementasi

| Bagian | Status | Catatan |
|---|---|---|
| Dokumentasi & desain | ✅ | README + dokumen desain |
| Skeleton Laravel 13 | ✅ | Baru model `User` dan migrasi dasar |
| Database Neon | ✅ | Migrasi diterapkan dan seed data tersedia |
| Model & migrasi domain | ✅ | 8 tabel domain, model, enum, factory, dan seeder tersedia |
| UI React + Inertia | ✅ | Inertia 3, React 19, TypeScript, shadcn/ui terpasang |
| Auth & manajemen peran | 🚧 | Fase 2 selesai: login, role middleware, policy, redirect dashboard; CRUD pengguna Fase 7 |
| AI Kilo Gateway | ✅ | `config/ai.php`, `AiClassifier`, fallback model, job klasifikasi |
| Queue & notifikasi | ✅ | Job `KlasifikasiKunjungan` & `KirimNotifikasiKunjungan`, driver log + email, interface WhatsApp |
| Testing | 🚧 | PHPUnit tersedia; test domain belum |
| Deployment | 🚧 | — |

> ✅ = sudah ada · 🚧 = rencana/sedang dikerjakan. README ini tidak mengklaim fitur yang belum terpasang.

---

## 📑 Daftar Isi

- [Status Implementasi](#-status-implementasi)
- [Latar Belakang](#-latar-belakang)
- [Fitur](#-fitur)
- [Peran Pengguna](#-peran-pengguna)
- [Alur Pemakaian](#-alur-pemakaian)
- [Keputusan Desain](#-keputusan-desain)
- [Peran AI](#-peran-ai)
- [Tech Stack](#-tech-stack)
- [Arsitektur](#-arsitektur)
- [Struktur Database](#-struktur-database)
- [Koneksi Database (Neon)](#-koneksi-database-neon)
- [Struktur Folder](#-struktur-folder)
- [Instalasi](#-instalasi)
- [Akun Uji Akses (Demo)](#-akun-uji-akses-demo)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Privasi & Keamanan Data](#-privasi--keamanan-data)
- [Roadmap](#-roadmap)
- [Tim Pengembang](#-tim-pengembang)
- [Lisensi](#-lisensi)

---

## 🎯 Latar Belakang

Buku tamu kertas punya beberapa masalah yang berulang:

| Masalah | Dampak |
|---|---|
| Tulisan tangan sulit dibaca | Data tamu tidak bisa dipakai ulang |
| Petugas tidak tahu tamu ingin bertemu siapa | Tamu menunggu lama atau salah ruangan |
| Rekap kunjungan dihitung manual | Memakan waktu dan rawan salah hitung |

**Buku Tamu Digital** menggantikan buku kertas dengan form cepat, klasifikasi keperluan otomatis oleh AI, penugasan pegawai, notifikasi, dan rekap otomatis untuk pimpinan.

---

## ✨ Fitur

### Fitur Inti

- **Form tamu cepat**: tamu yang pernah datang cukup mengisi nomor HP; identitas terisi otomatis.
- **QR di meja resepsionis**: tamu membuka form dari ponsel atau kiosk. QR tidak dipakai untuk rating.
- **Data master**: unit, pegawai, jam layanan, dan FAQ.
- **Penugasan otomatis**: setelah AI menentukan unit, sistem memilih satu pegawai yang paling sedikit beban aktifnya.
- **Notifikasi ke pegawai**: WhatsApp/email ke pegawai yang ditugaskan.
- **Status kunjungan**: `menunggu` → `dilayani` → `selesai`, plus `tidak_terlayani`.
- **Rekap harian/bulanan** dengan ekspor dan cetak.
- **Rating singkat** di kiosk, hanya untuk kunjungan `selesai`, dicari lewat nomor HP.

### Interaktif & Mudah Dipakai

- Mode **kiosk layar penuh** dengan tombol besar.
- Layar **status** yang diperbarui otomatis selama AI memproses keperluan.
- Layar **ucapan selamat datang** dan **arahan setelah submit** (contoh: “Silakan ke ruang …”).
- **Asisten tanya-jawab** untuk jam layanan, lokasi, dan syarat layanan.

---

## 👥 Peran Pengguna

| Peran | Akses | Login |
|---|---|---|
| **Tamu** | Mengisi form, melihat status/arahan, bertanya ke asisten FAQ, memberi rating kunjungan selesai | ❌ Tidak perlu |
| **Resepsionis** | Memantau antrean, mengoreksi hasil AI, mengganti pegawai, menandai tidak terlayani, mengelola data master & FAQ | ✅ |
| **Admin** | Semua akses resepsionis + mengelola pengguna dan peran | ✅ |
| **Pegawai** | Menerima notifikasi, melihat tugas, menandai tamu dilayani/selesai | ✅ |
| **Pimpinan** | Melihat rekap dan ringkasan mingguan (read-only) | ✅ |

---

## 🔄 Alur Pemakaian

1. Tamu memindai **QR di meja resepsionis** atau memakai **tablet kiosk**.
2. Tamu mengisi **nomor HP**. Jika sudah pernah datang, identitas terisi otomatis; **keperluan selalu diisi baru**.
3. Setelah submit, kunjungan disimpan dengan status `menunggu`. AI memproses keperluan lewat **queue**, dan halaman status diperbarui otomatis.
4. AI menentukan unit. Sistem memilih **satu pegawai** di unit tersebut, mengirim notifikasi, lalu menampilkan arahan ke **ruang tamu unit** dan nama pegawai. Pegawai menemui tamu di ruang tamu.
5. Jika tidak ada pegawai atau pegawai tidak bisa melayani, resepsionis mengganti pegawai atau menandai kunjungan **tidak terlayani**. Tamu diarahkan pulang.
6. Pegawai menandai tamu **dilayani**, lalu **selesai**.
7. Tamu memberi **rating di kiosk** dengan memasukkan nomor HP. Hanya kunjungan `selesai` yang bisa dinilai.

```mermaid
flowchart LR
    A[Scan QR meja resepsionis / kiosk] --> B[Cek nomor HP]
    B --> C{Tamu lama?}
    C -- Ya --> D[Identitas terisi otomatis]
    C -- Tidak --> E[Isi identitas]
    D --> F[Tulis keperluan]
    E --> F
    F --> G[Simpan kunjungan: menunggu]
    G --> H[Queue: AI klasifikasi]
    H --> I[AI tentukan unit]
    I --> J[Pilih pegawai & kirim notifikasi]
    J --> K[Tampilkan arahan ruang tamu unit]
    K --> L[Pegawai tandai dilayani]
    L --> M[Pegawai tandai selesai]
    M --> N[Tamu rating via nomor HP]
    N --> O[Selesai]
    I -.->|gagal / tidak yakin| P[Perlu ditinjau resepsionis]
    P --> J
    J -.->|tidak ada pegawai| Q[Tandai tidak terlayani]
    Q --> O
```

---

## 🧭 Keputusan Desain

| No | Keputusan | Alasan |
|---|---|---|
| 1 | QR hanya ada di **meja resepsionis**, bukan per meja/lokasi. | Tidak perlu tabel `lokasi_qr`; QR hanya membuka form. |
| 2 | Rating dicari lewat **nomor HP**, tetapi unik per **kunjungan**. | Nomor HP mengenali tamu; satu kunjungan tetap hanya punya satu rating, dan tamu yang datang lagi bisa menilai kunjungan berikutnya. |
| 3 | Tanda tangan digital **tidak dipakai di MVP**. | Tidak ada kebutuhan mendesak; bisa ditambahkan nanti sebagai opsional. |
| 4 | Notifikasi ke **satu pegawai** yang ditugaskan. | Tamu tidak perlu menunggu semua pegawai; resepsionis bisa mengganti pegawai. |
| 5 | Jika tetap tidak ada pegawai yang bisa melayani, kunjungan **tidak_terlayani** dan tamu pulang. | Resepsionis/admin mencoba mengganti pegawai terlebih dahulu; keputusan akhir tetap di mereka, bukan otomatis oleh sistem. |
| 6 | Tamu lama boleh memperbarui identitas; **keperluan per kunjungan**. | Tamu yang sama bisa datang dengan keperluan berbeda. |
| 7 | Endpoint memakai **Inertia**, tanpa REST API terpisah. | Halaman React dirender dari controller Laravel. |
| 8 | AI memakai **Kilo Gateway** dengan model gratis + fallback. | Model gratis bisa lambat/rate-limited; fallback menjaga layanan tetap jalan. |
| 9 | AI dan notifikasi lewat **queue**; halaman status polling. | Submit tamu tetap cepat dan tidak menunggu model AI. |
| 10 | Status kunjungan ada empat: `menunggu`, `dilayani`, `selesai`, `tidak_terlayani`. | Membedakan kunjungan yang benar-benar selesai dari yang gagal dilayani. |
| 11 | Ringkasan mingguan **tidak disimpan di tabel**. | Dihitung saat halaman pimpinan dibuka dan di-cache sebentar; tidak perlu tabel laporan. |
| 12 | Tamu diarahkan ke **ruang tamu unit tujuan**; pegawai menemui tamu di sana. | Memakai `units.lokasi_ruang`; tidak perlu tabel ruang baru. |

Detail aturan, ERD, dan UML ada di [dokumen desain](./docs/Desain%20Sistem%20Buku%20Tamu%20Digital.md).

---

## 🤖 Peran AI

| Fitur AI | Input | Output | Catatan |
|---|---|---|---|
| **Klasifikasi keperluan** | Teks keperluan tamu | `unit_kode`, kategori, ringkasan satu kalimat, confidence | Lewat queue; hasil bisa dikoreksi resepsionis |
| **Asisten tanya-jawab** | Pertanyaan tamu | Jawaban dari FAQ aktif | Hanya dari data FAQ; jika tidak ada, sarankan petugas |
| **Ringkasan mingguan** | Data kunjungan satu minggu | Narasi untuk pimpinan | Dihitung saat halaman dibuka, di-cache; tidak disimpan di tabel |

**Prinsip penggunaan AI:**

- Hasil klasifikasi selalu bisa dikoreksi manual oleh resepsionis.
- Output AI berupa JSON terstruktur dan divalidasi. Jika gagal, unit tidak dikenal, atau confidence di bawah `0.70`, kunjungan ditandai **perlu ditinjau**.
- Hanya teks keperluan yang dikirim ke layanan AI, tanpa nama, nomor HP, atau alamat tamu.
- Panggilan AI dijalankan lewat **queue** agar submit tamu tetap cepat.
- Model gratis dari **Kilo Gateway** dicoba berurutan: model utama, lalu daftar fallback. Jika semua gagal, job di-retry; setelah batas retry, kunjungan masuk daftar perlu ditinjau.
- Asisten FAQ dibatasi pada FAQ aktif. Jika jawaban tidak ada, asisten menyarankan bertanya ke petugas.

Kontrak output, fallback, dan aturan validasi lengkap ada di [dokumen desain §5](./docs/Desain%20Sistem%20Buku%20Tamu%20Digital.md#5-kontrak-ai).

---

## 🛠 Tech Stack

| Lapisan | Teknologi | Status |
|---|---|---|
| Backend | [Laravel](https://laravel.com) 13 (PHP ≥ 8.3) | ✅ terpasang |
| Jembatan FE-BE | [Inertia.js](https://inertiajs.com) v3 | ✅ terpasang |
| Frontend | [React](https://react.dev) 19 + TypeScript | ✅ terpasang |
| Build tool | [Vite](https://vite.dev) 8 | ✅ terpasang |
| Styling & UI | [Tailwind CSS](https://tailwindcss.com) v4 + [shadcn/ui](https://ui.shadcn.com) | ✅ terpasang |
| Database | PostgreSQL via [Neon](https://neon.com) | 🚧 konfigurasi |
| Auth & peran | Auth bawaan Laravel + kolom `role` | 🚧 rencana |
| Antrean | Laravel Queue (`database`) | ✅ tabel queue tersedia |
| Integrasi AI | **Kilo Gateway** (endpoint kompatibel OpenAI) + model gratis | 🚧 rencana |
| Notifikasi | Laravel Mail + WhatsApp gateway (Fonnte MVP, Cloud API produksi) | 🚧 rencana |
| Testing | PHPUnit | ✅ tersedia |
| Tanda tangan digital | — | ❌ tidak dipakai di MVP |

**Library pendukung (usulan):** pembuat QR statis untuk meja resepsionis, Recharts (grafik rekap), Laravel Excel dan DomPDF (ekspor).

> **Catatan versi (per 21 November 2026):** Laravel 13 dirilis 17 Maret 2026 dan membutuhkan PHP 8.3 atau lebih baru. Periksa kembali versi saat memulai proyek.

---

## 🏗 Arsitektur

```mermaid
flowchart TB
    T[Tamu: QR meja resepsionis / kiosk] --> R[React + Inertia]
    P[Pegawai / Resepsionis / Admin / Pimpinan] --> R
    R --> L[Laravel 13]
    L --> DB[(PostgreSQL: Neon)]
    L --> Q[Queue database]
    Q --> AI[Kilo Gateway]
    Q --> WA[WhatsApp / Email]
    Q --> DB
```

Setelah tamu submit, Laravel menyimpan kunjungan lalu mengirim job ke queue:

1. **KlasifikasiKunjungan** memanggil AI, memvalidasi hasil, mengisi unit/kategori/ringkasan, memilih pegawai, lalu mengirim notifikasi.
2. **KirimNotifikasiKunjungan** mengirim WhatsApp/email ke pegawai yang ditugaskan.

Ringkasan mingguan tidak memakai job/scheduler; halaman pimpinan menghitungnya saat dibuka dan menyimpan hasil sementara di cache. Halaman status kunjungan melakukan polling Inertia sampai `klasifikasi_status` tidak lagi `diproses`. Tidak ada REST API terpisah; semua halaman memakai Inertia.

---

## 🗄 Struktur Database

**8 tabel.** ERD lengkap ada di [dokumen desain §1](./docs/Desain%20Sistem%20Buku%20Tamu%20Digital.md#1-erd).

| Tabel | Isi |
|---|---|
| `users` | id, name, email, password, role (`admin`, `resepsionis`, `pegawai`, `pimpinan`), aktif |
| `units` | id, kode, nama, lokasi_ruang, deskripsi, aktif |
| `jam_layanan` | id, unit_id, hari, jam_mulai, jam_selesai, aktif |
| `pegawai` | id, user_id (nullable), unit_id, nama, jabatan, no_wa, email, aktif, bisa_menerima_tamu, terakhir_ditugaskan_pada |
| `tamu` | id, nama, kategori (`perorangan`/`instansi`), instansi, alamat, no_hp (unik, ternormalisasi) |
| `kunjungan` | id, kode_publik (unik), tamu_id, unit_id (nullable), pegawai_id (nullable), keperluan, kategori, ringkasan_ai, status, klasifikasi_status, diubah_oleh, alasan_tidak_terlayani, catatan, waktu_datang, waktu_dilayani, waktu_selesai, notifikasi_terkirim_pada |
| `faq` | id, unit_id (opsional), pertanyaan, jawaban, aktif |
| `rating` | id, kunjungan_id (unik), nilai (1–5), komentar |

**Catatan desain:**

- **Tidak ada tanda tangan digital** di MVP.
- Tamu dikenali **hanya dari nomor HP**, sehingga tamu lama cukup mengisi nomor HP dan identitas terisi otomatis.
- Normalisasi format nomor HP (`08xx` / `+62xx` / `62xx` → `62xx`) sebelum disimpan agar tamu yang sama tidak terduplikasi.
- **Rating hanya untuk kunjungan `selesai`**; `rating.kunjungan_id` diberi unique constraint.
- `kunjungan.unit_id` dan `pegawai_id` nullable karena AI dan penugasan berjalan lewat queue.
- Status kunjungan: `menunggu`, `dilayani`, `selesai`, `tidak_terlayani`.
- `klasifikasi_status`: `diproses`, `otomatis`, `manual`, `perlu_ditinjau`.
- `units.lokasi_ruang` dipakai sebagai **ruang tamu unit**; pegawai yang ditugaskan menemui tamu di sana.
- Ringkasan mingguan **tidak disimpan di tabel**; dihitung saat halaman pimpinan dibuka dan di-cache.
- Gunakan `timestamptz` untuk waktu dan atur `APP_TIMEZONE=Asia/Jakarta`.

---

## 🐘 Koneksi Database (Neon)

Proyek ini memakai **Neon** sebagai PostgreSQL managed. Laravel memakai driver `pgsql` bawaan.

> Autentikasi tetap memakai sistem auth Laravel, bukan layanan auth Neon.

### Prasyarat

- Ekstensi PHP `pdo_pgsql` aktif.
- Akun dan project di [Neon](https://neon.com).

### Langkah

1. Buat project di Neon, lalu klik **Connect** pada dashboard untuk menyalin connection string.
2. Neon menyediakan dua jenis string:
   - **Direct** — hostname tanpa `-pooler`.
   - **Pooled** — hostname mengandung `-pooler`.
3. Untuk proyek ini, **gunakan direct connection** sebagai koneksi utama. Trafiknya kecil, queue worker berjalan lama, dan migrasi lebih stabil lewat koneksi langsung.
4. Jika nanti memakai pooled connection (misalnya deploy serverless), gunakan koneksi direct khusus untuk `php artisan migrate` dan `php artisan queue:work`.
5. Salin connection string ke `.env`:

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://[USER]:[PASSWORD]@ep-xxxx.[REGION].aws.neon.tech/[DBNAME]
DB_SCHEMA=public
DB_SSLMODE=require
```

6. Jalankan migrasi:

```bash
php artisan migrate
```

> Jika compute Neon diatur *scale-to-zero*, query pertama setelah idle bisa lebih lambat. Perhatikan saat demo.

### Konfigurasi `config/database.php`

`config/database.php` sudah membaca `DB_SCHEMA` dan `DB_SSLMODE` dari `.env`:

```php
'pgsql' => [
    // ...
    'search_path' => env('DB_SCHEMA', 'public'),
    'sslmode' => env('DB_SSLMODE', 'prefer'),
],
```

Untuk Neon, gunakan `DB_SCHEMA=public` dan `DB_SSLMODE=require`.

### Catatan penting

- **Queue worker** (`php artisan queue:work`) adalah proses berumur panjang. Gunakan direct connection, bukan pooled connection.
- **Jangan commit** `DB_URL` atau password ke repositori.
- Untuk development lokal, tim bisa memakai project Neon yang sama atau PostgreSQL lokal agar tidak saling menimpa data.

---

## 📂 Struktur Folder

Struktur **target** (menyesuaikan implementasi). Repo saat ini masih skeleton Laravel.

```
Buku-Tamu-Digital/
├── app/
│   ├── Enums/              # StatusKunjungan, KlasifikasiStatus
│   ├── Http/Controllers/
│   ├── Jobs/               # KlasifikasiKunjungan, KirimNotifikasiKunjungan
│   ├── Models/
│   ├── Policies/           # Otorisasi per peran
│   └── Services/           # AiClassifier, PenugasanService, NotificationService, RekapService
├── config/
│   └── database.php
├── database/
│   ├── migrations/
│   └── seeders/            # Unit, pegawai, FAQ, contoh kunjungan
├── resources/
│   ├── css/
│   └── js/
│       ├── Pages/          # Tamu, Kiosk, Dashboard, Rating, Rekap
│       └── Components/
├── routes/
│   ├── web.php
│   └── console.php         # Cron/job terjadwal (belum dipakai di MVP)
├── .env.example
├── vite.config.js
└── README.md
```

---

## 🚀 Instalasi

### Prasyarat

- PHP ≥ 8.3 dengan ekstensi `pdo_pgsql`
- Composer
- Node.js dan npm
- Akun Neon (atau PostgreSQL lokal)
- Git

### Langkah

```bash
# 1. Clone repositori
git clone https://github.com/vemaarnandha/Buku-Tamu-Digital.git
cd Buku-Tamu-Digital

# 2. Install dependensi backend & frontend
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Isi koneksi database di .env (lihat bagian Koneksi Database)
#    Neon: gunakan direct connection, DB_SCHEMA=public, DB_SSLMODE=require

# 5. Migrasi dan seed data awal
php artisan migrate --seed

# 6. Jalankan aplikasi (tiga terminal)
php artisan serve
php artisan queue:work
npm run dev
```

Buka `http://localhost:8000`.

> **Catatan:** repo saat ini baru berisi skeleton Laravel. Migrasi domain, UI React/Inertia, dan fitur AI belum ada. Langkah di atas menjadi acuan setelah fitur mulai dibuat.

### Build untuk produksi

```bash
npm run build
php artisan config:cache
php artisan route:cache
```

### Scheduler (belum dipakai)

Ringkasan mingguan dihitung saat halaman dibuka, bukan lewat job terjadwal. Jika nanti ada job terjadwal, tambahkan cron:

```bash
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

Queue worker di produksi sebaiknya dijalankan lewat Supervisor agar otomatis restart.

---

## 🔑 Akun Uji Akses (Demo)

Akun berikut dibuat oleh `DatabaseSeeder` saat menjalankan `php artisan migrate --seed` atau `php artisan db:seed`. Semua akun memakai password yang sama: **`password`**.

| Peran | Email | Akses awal |
|---|---|---|
| Admin | `admin@example.com` | Antrean kunjungan + seluruh akses resepsionis + pengguna & peran |
| Resepsionis | `resepsionis@example.com` | Antrean kunjungan dan pengelolaan operasional |
| Pegawai Unit Data 1 | `pegawai.data.1@example.com` | Tugas sendiri |
| Pegawai Unit Data 2 | `pegawai.data.2@example.com` | Tugas sendiri |
| Pegawai Unit Umum 1 | `pegawai.umum.1@example.com` | Tugas sendiri |
| Pegawai Unit Umum 2 | `pegawai.umum.2@example.com` | Tugas sendiri |
| Pegawai Unit Kepegawaian 1 | `pegawai.kepegawaian.1@example.com` | Tugas sendiri |
| Pegawai Unit Kepegawaian 2 | `pegawai.kepegawaian.2@example.com` | Tugas sendiri |
| Pimpinan | `pimpinan@example.com` | Rekap & ringkasan (read-only) |

> ⚠️ Akun ini hanya untuk pengembangan, demo, dan pengujian lokal. Ganti atau hapus sebelum aplikasi dipakai di produksi.

---

## ⚙️ Konfigurasi Environment

Contoh variabel pada `.env`:

```env
APP_NAME="Buku Tamu Digital"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# Database (Neon)
DB_CONNECTION=pgsql
DB_URL=
DB_SCHEMA=public
DB_SSLMODE=require

# Queue
QUEUE_CONNECTION=database

# AI - Kilo Gateway (endpoint kompatibel OpenAI)
AI_BASE_URL=
AI_API_KEY=
AI_MODEL=
AI_FALLBACK_MODELS=
AI_TIMEOUT=30
AI_MAX_RETRIES=2

# Notifikasi
MAIL_MAILER=smtp
WHATSAPP_DRIVER=log
WHATSAPP_API_URL=
WHATSAPP_API_TOKEN=
WHATSAPP_TIMEOUT=15
NOTIFIKASI_EMAIL_ENABLED=true
```

> ⚠️ **Jangan pernah commit file `.env`, password database, atau API key ke repositori.**

---

## 🔒 Privasi & Keamanan Data

- **Tidak meminta foto KTP atau tanda tangan.** Nomor HP saja cukup untuk mengenali tamu yang sama.
- Data yang dikumpulkan seminimal mungkin: nama, kategori, instansi, alamat, nomor HP, keperluan, dan rating.
- Nomor HP disimpan dalam format ternormalisasi dan hanya dipakai untuk mengenali tamu serta mencari kunjungan selesai saat rating.
- Tabel `rating` **tidak menyimpan nomor HP**; nomor hanya dipakai saat pencarian kunjungan.
- Koneksi database memakai SSL (`sslmode=require`).
- Database Neon diakses langsung lewat driver `pgsql`; tidak ada Data API yang mengekspos tabel.
- Halaman tamu tidak memerlukan login, sedangkan dashboard dilindungi autentikasi berbasis peran.
- Perubahan klasifikasi/status mencatat `diubah_oleh` dan waktu perubahan sebagai jejak audit sederhana.
- Rekap untuk pimpinan berupa data agregat.
- QR hanya ada di meja resepsionis, membuka form, dan berada di area yang diawasi petugas. Form publik diberi rate limit.
- Halaman status kunjungan memakai `kode_publik` acak, bukan ID berurutan, agar data tamu lain tidak mudah ditebak.
- Teks yang dikirim ke AI hanya `keperluan`, tanpa identitas tamu.
- **Kebijakan retensi data** (berapa lama data tamu disimpan) belum ditetapkan; perlu diputuskan sebelum produksi.

---

## 🗺 Roadmap

- [x] Setup proyek (Laravel 13, Vite, Tailwind)
- [x] Dokumen desain sistem (ERD, UML, wireframe, aturan bisnis)
- [ ] Konfigurasi PostgreSQL (Neon) dan migrasi awal
- [ ] Autentikasi dan manajemen peran (`admin`, `resepsionis`, `pegawai`, `pimpinan`)
- [ ] CRUD unit, pegawai, jam layanan, dan FAQ
- [ ] Form tamu cepat, cek tamu lama via nomor HP, dan QR meja resepsionis
- [x] Pasang Inertia + React + TypeScript + shadcn/ui
- [x] Integrasi Kilo Gateway: klasifikasi, fallback model, validasi JSON
- [x] Queue klasifikasi + penugasan pegawai + notifikasi WhatsApp/email
- [ ] Halaman status kunjungan dengan polling Inertia
- [ ] Dashboard resepsionis (koreksi AI, ganti pegawai, tandai tidak terlayani)
- [ ] Dashboard pegawai (tandai dilayani/selesai)
- [ ] Rating via nomor HP (hanya kunjungan selesai)
- [ ] Asisten tanya-jawab FAQ
- [ ] Rekap harian/bulanan, ekspor, dan cetak
- [ ] Ringkasan mingguan AI untuk pimpinan (on-demand + cache)
- [ ] Test fitur, seed data demo, dan skenario presentasi
- [ ] Deployment (Supervisor queue, HTTPS, backup)
- [ ] Tanda tangan digital opsional (di luar MVP)

---

## 🤝 Tim Pengembang

| Nama | Peran |
|---|---|
| _Nama anggota 1_ | _Contoh: Project Manager / Backend_ |
| _Nama anggota 2_ | _Contoh: Frontend_ |
| _Nama anggota 3_ | _Contoh: UI/UX_ |
| _Nama anggota 4_ | _Contoh: Database / AI_ |
| _Nama anggota 5_ | _Contoh: QA / Dokumentasi_ |
| _Nama anggota 6_ | _Contoh: Fullstack_ |

### Alur Kontribusi

1. Buat branch dari `main`: `git checkout -b feat/nama-fitur`
2. Commit dengan pesan jelas: `git commit -m "feat: tambah form tamu"`
3. Push dan buat Pull Request untuk di-review anggota lain.

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan tugas/pembelajaran. Tentukan lisensi (misalnya MIT) sesuai kesepakatan tim.
