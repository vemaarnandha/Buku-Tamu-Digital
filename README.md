# 📖 Buku Tamu Digital

Buku tamu digital dengan **asisten resepsionis berbasis AI** untuk instansi pelayanan publik. Tamu cukup memindai QR atau memakai tablet kiosk, menulis keperluan dengan kalimat biasa, lalu sistem mengarahkan ke unit/pegawai yang tepat dan memberi tahu pegawai tersebut secara otomatis.

> Dikembangkan oleh satu kelompok yang terdiri dari 6 orang.

**Cocok untuk:** BPS, Perpustakaan Bung Karno, Dinas Pendidikan, Kantor Kecamatan, dan instansi sejenis.

---

## 📑 Daftar Isi

- [Latar Belakang](#-latar-belakang)
- [Fitur](#-fitur)
- [Peran Pengguna](#-peran-pengguna)
- [Alur Pemakaian](#-alur-pemakaian)
- [Peran AI](#-peran-ai)
- [Tech Stack](#-tech-stack)
- [Arsitektur](#-arsitektur)
- [Struktur Database](#-struktur-database)
- [Koneksi Database (Supabase / Neon)](#-koneksi-database-supabase--neon)
- [Struktur Folder](#-struktur-folder)
- [Instalasi](#-instalasi)
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

**Buku Tamu Digital** menggantikan buku kertas dengan form cepat, klasifikasi keperluan otomatis oleh AI, notifikasi ke pegawai tujuan, dan rekap otomatis untuk pimpinan.

---

## ✨ Fitur

### Fitur Inti
- **Form tamu cepat**: tamu yang pernah datang cukup mengisi nomor HP.
- **Data master**: unit, pegawai, dan jam layanan.
- **Status kunjungan**: `menunggu` → `dilayani` → `selesai`.
- **Rekap harian/bulanan** dengan ekspor dan cetak.
- **Tanda tangan digital** langsung di layar.
- **Rating singkat** sebelum tamu pulang.

### Interaktif & Mudah Dipakai
- Mode **kiosk layar penuh** dengan tombol besar.
- **QR per meja/lokasi** (tamu bisa mengisi dari ponsel sendiri).
- **Notifikasi ke pegawai** via WhatsApp/email.
- Layar **ucapan selamat datang** dan **arahan setelah submit** (contoh: "Silakan ke ruang …").

---

## 👥 Peran Pengguna

| Peran | Akses | Login |
|---|---|---|
| **Tamu** | Mengisi form, melihat arahan, bertanya ke asisten FAQ, memberi rating | ❌ Tidak perlu |
| **Resepsionis / Admin** | Kelola data master & FAQ, koreksi hasil klasifikasi AI, pantau antrean kunjungan | ✅ |
| **Pegawai** | Menerima notifikasi, menandai tamu sudah dilayani | ✅ |
| **Pimpinan** | Melihat rekap dan ringkasan mingguan | ✅ |

---

## 🔄 Alur Pemakaian

1. Tamu memindai **QR di meja depan** atau memakai **tablet mode kiosk**.
2. Tamu mengisi **nama, instansi**, dan menulis **keperluan** dengan kalimat biasa.
3. **AI membaca keperluan**, menentukan unit/pegawai tujuan, lalu menampilkan arahan *"Silakan ke ruang …"*.
4. **Pegawai tujuan** menerima notifikasi dan menandai tamu sudah dilayani.
5. Tamu memberi **rating singkat** sebelum pulang.

```mermaid
flowchart LR
    A[Scan QR / Kiosk] --> B[Isi nama, instansi, keperluan]
    B --> C[AI klasifikasi keperluan]
    C --> D[Arahan: Silakan ke ruang ...]
    C --> E[Notifikasi ke pegawai]
    E --> F[Pegawai tandai: dilayani]
    F --> G[Tamu beri rating]
    G --> H[Selesai]
```

---

## 🤖 Peran AI

| Fitur AI | Input | Output |
|---|---|---|
| **Klasifikasi keperluan** | Teks keperluan tamu | Unit tujuan, kategori keperluan, ringkasan satu kalimat. Hasil dapat diubah resepsionis. |
| **Asisten tanya-jawab** | Pertanyaan tamu (jam layanan, letak ruang, syarat layanan) | Jawaban **hanya dari data FAQ** yang diisi admin. |
| **Ringkasan mingguan** | Data kunjungan satu minggu | Ringkasan keperluan paling sering muncul untuk pimpinan. |

**Prinsip penggunaan AI:**
- Hasil klasifikasi selalu bisa dikoreksi manual oleh resepsionis.
- Output AI berupa JSON terstruktur dan divalidasi. Jika gagal atau tidak yakin, kunjungan ditandai **"perlu ditinjau resepsionis"**.
- Asisten tanya-jawab dibatasi pada data FAQ. Jika jawaban tidak ada, asisten menyarankan bertanya ke petugas.
- Hanya teks keperluan yang dikirim ke layanan AI, tanpa data identitas yang tidak diperlukan.
- Panggilan AI dijalankan lewat **queue** agar submit tamu tetap cepat.

---

## 🛠 Tech Stack

| Lapisan | Teknologi | Keterangan |
|---|---|---|
| Backend | [Laravel](https://laravel.com) 13 (PHP ≥ 8.3) | Framework utama, auth, queue, scheduler |
| Jembatan FE-BE | [Inertia.js](https://inertiajs.com) v3 | Halaman React dirender dari controller Laravel, tanpa REST API terpisah |
| Frontend | [React](https://react.dev) 19 + TypeScript | UI tamu, kiosk, dan dashboard |
| Build tool | [Vite](https://vite.dev) | Bundler bawaan ekosistem Laravel |
| Styling & UI | [Tailwind CSS](https://tailwindcss.com) v4 + [shadcn/ui](https://ui.shadcn.com) | Komponen besar dan ramah kiosk |
| **Database** | **PostgreSQL via [Supabase](https://supabase.com) atau [Neon](https://neon.com)** | Managed PostgreSQL (pilih salah satu, lihat [panduan koneksi](#-koneksi-database-supabase--neon)) |
| Auth & peran | Auth bawaan Laravel + kolom `role` | Bukan Supabase Auth. Pengguna disimpan di tabel `users` Laravel |
| Antrean & jadwal | Laravel Queue (driver `database`) + Scheduler | Notifikasi, panggilan AI, ringkasan mingguan |
| Integrasi AI | Laravel AI SDK atau HTTP client ke provider LLM | Klasifikasi, FAQ, ringkasan |
| Notifikasi | Laravel Mail + WhatsApp gateway | Fonnte untuk MVP, WhatsApp Cloud API untuk produksi |
| Testing | Pest | Backend |

**Library pendukung (usulan, sesuaikan dengan implementasi):** `signature_pad` (tanda tangan digital), library QR code PHP, Recharts (grafik rekap), Laravel Excel dan DomPDF (ekspor).

> **Catatan versi (per 21 November 2026):** Laravel 13 dirilis 17 Maret 2026 dan membutuhkan PHP 8.3 atau lebih baru. Periksa kembali versi saat memulai proyek.

---

## 🏗 Arsitektur

```mermaid
flowchart TB
    T[Tamu: QR / Kiosk] --> R[React + Inertia]
    P[Pegawai / Resepsionis / Pimpinan] --> R
    R --> L[Laravel 13]
    L --> DB[(PostgreSQL: Supabase / Neon)]
    L --> Q[Queue]
    Q --> AI[AI Provider]
    Q --> WA[WhatsApp / Email]
    S[Scheduler] --> Q
```

Setelah tamu submit, Laravel menyimpan kunjungan lalu mengirim job ke queue: satu job memanggil AI untuk klasifikasi, job berikutnya mengirim notifikasi ke pegawai tujuan. Scheduler menjalankan ringkasan mingguan.

---

## 🗄 Struktur Database

Tabel utama: `users`, `units`, `pegawai`, `tamu`, `kunjungan`, `faq`, `rating`.

```mermaid
erDiagram
    UNITS ||--o{ PEGAWAI : memiliki
    UNITS ||--o{ KUNJUNGAN : tujuan
    UNITS ||--o{ FAQ : "info unit"
    USERS ||--o| PEGAWAI : "akun"
    TAMU ||--o{ KUNJUNGAN : melakukan
    PEGAWAI ||--o{ KUNJUNGAN : melayani
    KUNJUNGAN ||--o| RATING : dinilai
```

| Tabel | Kolom utama (usulan) |
|---|---|
| `users` | id, name, email, password, role (`admin`, `resepsionis`, `pegawai`, `pimpinan`) |
| `units` | id, nama, ruang, jam_layanan (`jsonb`), deskripsi |
| `pegawai` | id, user_id, unit_id, nama, jabatan, no_wa, email |
| `tamu` | id, nama, instansi, no_hp (unik, disimpan dalam format ternormalisasi, mis. `62812...`) |
| `kunjungan` | id, tamu_id, unit_id, pegawai_id, keperluan, kategori, ringkasan_ai, status, lokasi_qr, tanda_tangan, waktu_datang, waktu_dilayani, waktu_selesai (`timestamptz`) |
| `faq` | id, unit_id (opsional), pertanyaan, jawaban, aktif |
| `rating` | id, kunjungan_id, nilai (1-5), komentar |

**Catatan desain:**
- Tamu dikenali **hanya dari nomor HP**, sehingga tamu lama cukup mengisi nomor HP dan data lainnya terisi otomatis.
- Normalisasi format nomor HP (08xx / +62xx / 62xx) sebelum disimpan agar tamu yang sama tidak terduplikasi.
- Gunakan `timestamptz` untuk waktu dan atur `APP_TIMEZONE=Asia/Jakarta`.

---

## 🐘 Koneksi Database (Supabase / Neon)

Supabase dan Neon sama-sama PostgreSQL, jadi Laravel memakai driver `pgsql` bawaan. **Pilih salah satu.**

> Proyek ini hanya memakai **database** dari Supabase/Neon. Autentikasi tetap memakai sistem auth Laravel, bukan Supabase Auth.

### Prasyarat
- Ekstensi PHP `pdo_pgsql` aktif.
- Akun dan project di [Supabase](https://supabase.com) atau [Neon](https://neon.com).

### Opsi A: Supabase

1. Buat project di Supabase, simpan password database.
2. Buka **Connect** di dashboard, salin connection string **Session pooler** (port `5432`). Jika jaringan mendukung IPv6 atau memakai IPv4 Add-On, boleh memakai direct connection.
3. **Jangan** memakai Transaction pooler (port `6543`) sebagai koneksi utama aplikasi, karena tidak mendukung prepared statement di sisi server yang dipakai ORM.
4. Percent-encode karakter khusus pada password (`&`, `#`, `?`, spasi).
5. Gunakan **schema khusus** (bukan `public`), karena Supabase mengekspos schema `public` sebagai Data API. Buat schema `laravel` lewat Table Editor atau SQL Editor:

   ```sql
   CREATE SCHEMA IF NOT EXISTS laravel;
   ```

```env
DB_CONNECTION=pgsql
DB_URL=postgres://postgres.[PROJECT-REF]:[PASSWORD]@[POOLER-HOST]:5432/postgres
DB_SCHEMA=laravel
DB_SSLMODE=require
```

### Opsi B: Neon

1. Buat project di Neon, klik **Connect** pada dashboard untuk menyalin connection string.
2. Neon menyediakan dua jenis string: **pooled** (hostname mengandung `-pooler`) dan **direct** (tanpa `-pooler`).
3. Untuk proyek ini, **mulailah dengan direct connection**. Trafiknya kecil, queue worker berjalan lama, dan migrasi lebih stabil lewat koneksi langsung.
4. Jika nanti memakai pooled connection (mis. deploy serverless), gunakan koneksi direct khusus untuk `php artisan migrate`.

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://[USER]:[PASSWORD]@ep-xxxx.[REGION].aws.neon.tech/[DBNAME]
DB_SCHEMA=public
DB_SSLMODE=require
```

> Jika compute Neon diatur *scale-to-zero*, query pertama setelah idle bisa lebih lambat. Perhatikan saat demo.

### Konfigurasi `config/database.php`

Laravel secara bawaan memakai `sslmode=prefer`. Ubah ke `require` dan jadikan schema bisa diatur lewat `.env`:

```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => env('DB_SCHEMA', 'public'),
    'sslmode' => env('DB_SSLMODE', 'require'),
],
```

### Catatan penting
- **Queue worker** (`php artisan queue:work`) adalah proses berumur panjang. Hubungkan ke direct connection atau session pooler, jangan transaction pooler.
- **Jangan commit** `DB_URL` atau password ke repositori.
- Untuk development lokal, tim bisa memakai database Supabase/Neon yang sama (satu branch/project bersama) atau PostgreSQL lokal agar tidak saling menimpa data.

---

## 📂 Struktur Folder

Struktur standar Laravel + React (menyesuaikan implementasi):

```
Buku-Tamu-Digital/
├── app/
│   ├── Http/Controllers/
│   ├── Jobs/              # Klasifikasi AI, kirim notifikasi
│   ├── Models/
│   └── Services/          # AiClassifier, NotificationService, RekapService
├── config/
│   └── database.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   └── js/                # Komponen React (halaman tamu, kiosk, dashboard)
├── routes/
│   ├── web.php
│   └── console.php        # Jadwal ringkasan mingguan
├── .env.example
├── vite.config.ts
└── README.md
```

---

## 🚀 Instalasi

### Prasyarat
- PHP ≥ 8.3 dengan ekstensi `pdo_pgsql`
- Composer
- Node.js dan npm
- Akun Supabase atau Neon (atau PostgreSQL lokal)
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
#    Untuk Supabase: buat dulu schema `laravel`

# 5. Migrasi dan seed data awal
php artisan migrate --seed

# 6. Jalankan aplikasi (tiga terminal)
php artisan serve
php artisan queue:work
npm run dev
```

Buka `http://localhost:8000`.

### Build untuk produksi

```bash
npm run build
php artisan config:cache
php artisan route:cache
```

### Scheduler (ringkasan mingguan)

Tambahkan cron di server:

```bash
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

---

## ⚙️ Konfigurasi Environment

Contoh variabel pada `.env` (nama variabel AI/WhatsApp menyesuaikan provider yang dipilih tim):

```env
APP_NAME="Buku Tamu Digital"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# Database (Supabase / Neon)
DB_CONNECTION=pgsql
DB_URL=
DB_SCHEMA=laravel
DB_SSLMODE=require

# Queue
QUEUE_CONNECTION=database

# AI (klasifikasi, FAQ, ringkasan mingguan)
AI_API_KEY=
AI_MODEL=

# Notifikasi
MAIL_MAILER=smtp
WHATSAPP_API_URL=
WHATSAPP_API_TOKEN=
```

> ⚠️ **Jangan pernah commit file `.env`, password database, atau API key ke repositori.**

---

## 🔒 Privasi & Keamanan Data

- **Tidak meminta foto KTP tamu.** Nomor HP saja sudah cukup untuk mengenali tamu yang sama.
- Data yang dikumpulkan seminimal mungkin: nama, instansi, nomor HP, keperluan, tanda tangan digital.
- Koneksi database memakai SSL (`sslmode=require`).
- Aplikasi memakai schema khusus di Supabase agar tabel tidak terekspos lewat Data API di schema `public`.
- Halaman tamu tidak memerlukan login, sedangkan dashboard dilindungi autentikasi berbasis peran.
- Rekap untuk pimpinan berupa data agregat.

---

## 🗺 Roadmap

- [ ] Setup proyek (Laravel, Inertia, Vite, React, Tailwind)
- [ ] Koneksi PostgreSQL (Supabase/Neon) dan migrasi awal
- [ ] Autentikasi dan manajemen peran
- [ ] CRUD unit, pegawai, jam layanan, FAQ
- [ ] Form tamu cepat dan pengenalan tamu via nomor HP
- [ ] Mode kiosk dan QR per lokasi
- [ ] Klasifikasi keperluan oleh AI (via queue)
- [ ] Notifikasi WhatsApp/email ke pegawai
- [ ] Status kunjungan dan dashboard pegawai
- [ ] Tanda tangan digital dan rating
- [ ] Asisten tanya-jawab (FAQ)
- [ ] Rekap harian/bulanan, ekspor, dan cetak
- [ ] Ringkasan mingguan AI untuk pimpinan

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
