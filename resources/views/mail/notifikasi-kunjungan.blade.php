<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Notifikasi Kunjungan</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6; margin: 0; padding: 24px;">
    <h2 style="margin: 0 0 16px;">Tamu menunggu</h2>

    <p>Halo {{ $kunjungan->pegawai?->nama ?? 'Petugas' }},</p>

    <p>
        Ada tamu <strong>{{ $kunjungan->tamu?->nama ?? 'tamu' }}</strong>
        yang ingin bertemu Anda di
        <strong>{{ $kunjungan->unit?->lokasi_ruang ?? 'ruang tamu' }}</strong>.
    </p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 16px 4px 0; color: #6b7280;">Keperluan</td>
            <td style="padding: 4px 0;">{{ $kunjungan->ringkasan_ai ?: $kunjungan->keperluan }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 16px 4px 0; color: #6b7280;">Kode kunjungan</td>
            <td style="padding: 4px 0;">{{ $kunjungan->kode_publik }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 16px 4px 0; color: #6b7280;">Waktu datang</td>
            <td style="padding: 4px 0;">{{ optional($kunjungan->waktu_datang)->format('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <p>Mohon temui tamu di ruang tamu unit tersebut.</p>

    <p style="color: #6b7280; font-size: 12px;">Email otomatis dari Buku Tamu Digital.</p>
</body>
</html>
