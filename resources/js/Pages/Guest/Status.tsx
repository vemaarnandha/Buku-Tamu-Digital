import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Kunjungan = {
    kode_publik: string;
    keperluan: string;
    kategori: string | null;
    ringkasan_ai: string | null;
    status: string;
    klasifikasi_status: string;
    unit: { nama: string; lokasi_ruang: string } | null;
    pegawai: { nama: string; jabatan: string } | null;
    waktu_datang: string | null;
    notifikasi_terkirim_pada: string | null;
    alasan_tidak_terlayani: string | null;
};

export default function Status({ kunjungan }: { kunjungan: Kunjungan }) {
    useEffect(() => {
        if (kunjungan.klasifikasi_status !== 'diproses') {
            return;
        }

        const id = window.setInterval(() => {
            router.reload({ only: ['kunjungan'] });
        }, 3000);

        return () => window.clearInterval(id);
    }, [kunjungan.klasifikasi_status]);

    const isDiproses = kunjungan.klasifikasi_status === 'diproses';
    const isPerluDitinjau = kunjungan.klasifikasi_status === 'perlu_ditinjau';
    const isSelesai = kunjungan.status === 'selesai';

    return (
        <>
            <Head title="Status Kunjungan" />

            <main className="flex min-h-screen items-center justify-center bg-background px-4 py-8">
                <Card className="w-full max-w-2xl">
                    <CardHeader>
                        <p className="text-sm font-medium text-muted-foreground">
                            Buku Tamu Digital
                        </p>
                        <CardTitle className="text-2xl">
                            Terima kasih!
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            Kode kunjungan: {kunjungan.kode_publik}
                        </p>

                        {isDiproses && (
                            <div className="rounded-lg border border-sky-400/40 bg-sky-500/10 p-4 text-sm text-sky-200">
                                Keperluan Anda sedang diproses...
                                <br />
                                Menunggu klasifikasi AI. Halaman ini diperbarui
                                otomatis.
                            </div>
                        )}

                        {isPerluDitinjau && (
                            <div className="rounded-lg border border-amber-400/40 bg-amber-500/10 p-4 text-sm text-amber-200">
                                Petugas resepsionis akan mengarahkan Anda.
                            </div>
                        )}

                        {!isDiproses && !isPerluDitinjau && kunjungan.unit && (
                            <div className="rounded-lg border border-emerald-400/40 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                                <p className="text-lg font-semibold">
                                    Silakan ke {kunjungan.unit.lokasi_ruang}
                                </p>
                                {kunjungan.pegawai && (
                                    <p className="mt-1">
                                        Petugas: {kunjungan.pegawai.nama} akan
                                        menemui Anda di ruang tamu.
                                    </p>
                                )}
                                <p className="mt-1">
                                    Keperluan: {kunjungan.keperluan}
                                </p>
                            </div>
                        )}

                        {kunjungan.status === 'tidak_terlayani' && (
                            <div className="rounded-lg border border-red-400/40 bg-red-500/10 p-4 text-sm text-red-200">
                                Kunjungan tidak dapat dilayani.
                                {kunjungan.alasan_tidak_terlayani && (
                                    <span> {kunjungan.alasan_tidak_terlayani}</span>
                                )}
                            </div>
                        )}

                        {isSelesai && (
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">
                                    Kunjungan selesai. Terima kasih atas kunjungan
                                    Anda.
                                </p>
                                <Button render={<Link href="/rating" />}>
                                    Beri Rating
                                </Button>
                            </div>
                        )}

                        <div className="text-xs text-muted-foreground">
                            <p>Asisten FAQ tersedia di meja resepsionis.</p>
                        </div>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}