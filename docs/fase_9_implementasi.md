# Fase 9 — Hardening, Testing, dan Deployment

Dokumen ini mencatat implementasi Fase 9 pada proyek Buku Tamu Digital.

## Tujuan

MVP siap dipakai dan aman.

## Perubahan yang Dilakukan

- Sanitasi input teks (`strip_tags` + trim) pada `StoreKunjunganRequest`.
- Rate limit sudah diterapkan pada form publik, rating, dan FAQ.
- Retry/backoff job AI dan notifikasi sudah ada dari Fase 4.
- Test suite penuh dijalankan (`php artisan test`).
- `npm run build` berhasil.

## Checklist Deployment

1. Isi `.env` produksi (Neon `DB_URL`, `DB_SSLMODE=require`, AI, WhatsApp, `APP_KEY`).
2. `php artisan migrate --force`.
3. `npm run build`.
4. `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
5. Jalankan queue worker dengan Supervisor: `php artisan queue:work --tries=3 --backoff=...`.
6. Aktifkan HTTPS dan secure cookie (`SESSION_SECURE_COOKIE=true`).
7. Backup Neon (branch/snapshot).
8. Manual QA kiosk, dashboard, rating, FAQ.

## Validasi yang Dijalankan

- `php artisan test`: 76 tests, 185 assertions, lulus.
- `npm run build`: lulus.
- `vendor/bin/pint --dirty --format agent`: lulus.

## Catatan

- Scheduler tidak digunakan; ringkasan dihitung on-demand dan di-cache.
- Queue worker wajib berjalan agar klasifikasi/notifikasi diproses.
