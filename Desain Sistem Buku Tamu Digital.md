# Desain Sistem: Buku Tamu Digital

Buku tamu dengan asisten resepsionis (AI) · ERD, UML, dan wireframe

## 1. ERD (Entity Relationship Diagram)

8 tabel. Tamu lama dikenali lewat no_hp.

```mermaid
erDiagram
  UNITS ||--o{ PEGAWAI : memiliki
  UNITS ||--o{ JAM_LAYANAN : punya
  UNITS ||--o{ FAQ : punya
  UNITS ||--o{ KUNJUNGAN : dituju
  PEGAWAI ||--o{ KUNJUNGAN : melayani
  TAMU ||--o{ KUNJUNGAN : melakukan
  KUNJUNGAN ||--o| RATING : dinilai
  USERS ||--o| PEGAWAI : akun
  USERS {
    int id PK
    string nama
    string email UK
    string password
    string role "admin, resepsionis, pegawai, pimpinan"
  }
  UNITS {
    int id PK
    string nama_unit
    string lokasi_ruang
  }
  JAM_LAYANAN {
    int id PK
    int unit_id FK
    string hari
  }
  PEGAWAI {
    int id PK
    int unit_id FK
    int user_id FK
    string nama
    string jabatan
    string no_wa
  }
  TAMU {
    int id PK
    string nama
    string kategori "perorangan, instansi"
    string instansi "null jika perorangan"
    text alamat
    string no_hp UK
  }
  KUNJUNGAN {
    int id PK
    int tamu_id FK
    int unit_id FK
    int pegawai_id FK
    text keperluan
    string kategori_ai
    text ringkasan_ai
    string status "menunggu, dilayani, selesai"
    string ttd_digital
    string kode_qr
    datetime waktu_datang
    datetime waktu_dilayani
    datetime waktu_selesai
  }
  FAQ {
    int id PK
    int unit_id FK
    string pertanyaan
    text jawaban
  }
  RATING {
    int id PK
    int kunjungan_id FK
    int nilai
    string komentar
  }
```

## 2. UML

### 2.1 Use Case Diagram

```mermaid
flowchart LR
  T([Tamu])
  R([Resepsionis/Admin])
  P([Pegawai])
  M([Pimpinan])
  AI([Layanan AI])
  subgraph S[Sistem Buku Tamu Digital]
    U1([Scan QR / buka kiosk])
    U2([Isi form tamu])
    U3([Tanya asisten FAQ])
    U4([Lihat arahan ruang])
    U5([Beri rating])
    U6([Ubah hasil klasifikasi])
    U7([Kelola unit, pegawai, FAQ, jam layanan])
    U8([Cetak/ekspor rekap])
    U9([Terima notifikasi])
    U10([Tandai tamu dilayani])
    U11([Lihat rekap dan ringkasan mingguan])
    U12([Klasifikasi keperluan])
    U13([Buat ringkasan mingguan])
    U14([Cek tamu lama via no HP])
  end
  T --- U1 & U2 & U3 & U4 & U5 & U14
  R --- U6 & U7 & U8
  P --- U9 & U10
  M --- U11
  AI --- U12 & U13
  U2 -.->|include| U12
  U2 -.->|include| U14
  U11 -.->|include| U13
```

### 2.2 Activity Diagram (alur tamu)

```mermaid
flowchart TD
  A([Mulai]) --> B[Tamu scan QR / buka kiosk]
  B --> B2[Isi nomor HP]
  B2 --> C{Nomor HP ada di data tamu?}
  C -- Ya, tamu lama --> D[Data terisi otomatis, tampil kunjungan ke-N]
  C -- Tidak, tamu baru --> E[Isi nama, kategori, instansi, alamat]
  D --> F[Tulis keperluan dengan kalimat biasa]
  E --> F
  F --> G[Tanda tangan digital]
  G --> H[AI klasifikasi: unit, kategori, ringkasan]
  H --> I[Tampilkan arahan: Silakan ke ruang ...]
  I --> J[Notifikasi ke pegawai tujuan]
  J --> K[Status: menunggu]
  K --> L[Pegawai tandai dilayani]
  L --> M[Status: selesai]
  M --> N[Tamu beri rating]
  N --> O([Selesai])
```

### 2.3 Sequence Diagram (submit kunjungan)

```mermaid
sequenceDiagram
  actor Tamu
  participant UI as Web/Kiosk
  participant API as Server
  participant AI as Layanan AI
  participant DB as Database
  actor Pegawai
  Tamu->>UI: Isi nomor HP
  UI->>API: GET /tamu?no_hp=...
  API->>DB: Cari tamu by no_hp, hitung kunjungan
  DB-->>API: Data tamu dan jumlah kunjungan
  alt Nomor terdaftar (tamu lama)
    API-->>UI: Data terisi otomatis, kunjungan ke-N
  else Nomor belum ada (tamu baru)
    API-->>UI: Form kosong
    Tamu->>UI: Isi nama, kategori, instansi, alamat
  end
  Tamu->>UI: Tulis keperluan
  UI->>API: POST /kunjungan
  API->>DB: Simpan tamu (jika baru)
  API->>AI: Klasifikasi teks keperluan
  AI-->>API: unit, kategori, ringkasan
  API->>DB: Simpan kunjungan (menunggu)
  API-->>UI: Arahan ruang tujuan
  API-)Pegawai: Notifikasi WhatsApp/email
  Pegawai->>API: Tandai dilayani
  API->>DB: Update status
  Tamu->>UI: Beri rating
  UI->>API: POST /rating
  API->>DB: Simpan rating
```

### 2.4 Class Diagram

```mermaid
classDiagram
  class User{
    +int id
    +string nama
    +string role
    +login()
    +logout()
  }
  class Unit{
    +int id
    +string namaUnit
    +string lokasiRuang
    +cekJamLayanan()
  }
  class Pegawai{
    +int id
    +string nama
    +string noWa
    +terimaNotifikasi()
    +tandaiDilayani()
  }
  class Tamu{
    +int id
    +string nama
    +string kategori
    +string instansi
    +string alamat
    +string noHp
    +cariByHp()
    +hitungKunjungan()
  }
  class Kunjungan{
    +int id
    +string keperluan
    +string kategoriAI
    +string ringkasanAI
    +string status
    +buat()
    +ubahStatus()
    +ubahKlasifikasi()
  }
  class Rating{
    +int nilai
    +string komentar
    +simpan()
  }
  class Faq{
    +string pertanyaan
    +string jawaban
  }
  class AIService{
    +klasifikasi(teks)
    +jawabFaq(pertanyaan)
    +ringkasanMingguan()
  }
  User "1" -- "0..1" Pegawai
  Unit "1" o-- "*" Pegawai
  Unit "1" o-- "*" Faq
  Tamu "1" --> "*" Kunjungan
  Unit "1" --> "*" Kunjungan
  Pegawai "1" --> "*" Kunjungan
  Kunjungan "1" --> "0..1" Rating
  Kunjungan ..> AIService
  Faq ..> AIService
```

## 3. Wireframe

Kotak putus-putus = input. Tombol besar untuk mode kiosk.

#### 1 · Form tamu (kiosk/QR)

**Selamat Datang**

Nomor HP: 0812xxxx**CEK**

✔ Selamat datang kembali, Budi!\
Kunjungan ke-3 · data terisi otomatis

Nama: Budi

Kategori: ○ Perorangan ◉ Instansi

Instansi: Dinas X

Alamat: Jl. ...

Tulis keperluan Anda dengan kalimat biasa...

✍ Tanda tangan di sini

KIRIM

Nomor belum ada = tamu baru, isi data lengkap sekali saja.

#### 2 · Arahan setelah submit

**Terima kasih, Budi!**

Silakan ke\
**Ruang Pelayanan Data**\
Lantai 1

Keperluan: konsultasi data statistik.\
Petugas sudah diberi tahu.

💬 Tanya asisten: jam layanan, lokasi, syarat...

SELESAI

#### 3 · Dashboard resepsionis/pegawai

**3**Menunggu

**2**Dilayani

**14**Selesai

Budi · Dinas X tamu lama · ke-3\
Konsultasi data → Unit Datamenunggu ubah

Sari · CV Maju\
Antar surat → Umumdilayani

Andi · SMKN 1\
Magang → Kepegawaianselesai

Tandai dilayani

Hasil AI bisa diedit resepsionis.

#### 4 · Rekap pimpinan

Periode: Nov 2026Ekspor ▾ Cetak

**Kunjungan per unit**

Data

Umum

Kepegawaian

**Rata-rata rating: ★ 4,6**

**Ringkasan AI minggu ini**\
Keperluan terbanyak: konsultasi data dan pengantaran surat.