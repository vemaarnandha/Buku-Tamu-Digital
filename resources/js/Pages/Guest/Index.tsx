import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';

import InputError from '@/Components/InputError';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Tamu = {
    id: number;
    nama: string;
    kategori: string | null;
    instansi: string | null;
    alamat: string | null;
    no_hp: string;
} | null;

export default function Form({
    tamu,
    no_hp,
    kunjunganSelesai,
    jamLayananHariIni,
    diLuarJamLayanan,
}: {
    tamu: Tamu;
    no_hp: string | null;
    kunjunganSelesai: number;
    jamLayananHariIni: string[];
    diLuarJamLayanan: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        no_hp: no_hp ?? tamu?.no_hp ?? '',
        nama: tamu?.nama ?? '',
        kategori: tamu?.kategori ?? 'perorangan',
        instansi: tamu?.instansi ?? '',
        alamat: tamu?.alamat ?? '',
        keperluan: '',
    });

    useEffect(() => {
        if (no_hp) {
            setData('no_hp', no_hp);
        }
        if (tamu) {
            setData('nama', tamu.nama);
            setData('kategori', tamu.kategori ?? 'perorangan');
            setData('instansi', tamu.instansi ?? '');
            setData('alamat', tamu.alamat ?? '');
        }
    }, [tamu, no_hp]);

    const cekNomor = () => {
        if (!data.no_hp.trim()) {
            return;
        }

        router.get(
            route('kunjungan.create'),
            { no_hp: data.no_hp },
            { preserveState: true, preserveScroll: true },
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('kunjungan.store'), {
            onSuccess: () => reset('keperluan'),
        });
    };

    return (
        <>
            <Head title="Buku Tamu Digital" />

            <main className="flex min-h-screen items-center justify-center bg-background px-4 py-8">
                <Card className="w-full max-w-2xl">
                    <CardHeader>
                        <p className="text-sm font-medium text-muted-foreground">
                            Buku Tamu Digital
                        </p>
                        <CardTitle className="text-2xl">Selamat Datang</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Meja Resepsionis
                        </p>
                    </CardHeader>
                    <CardContent>
                        {diLuarJamLayanan && (
                            <div className="mb-4 rounded-lg border border-amber-400/40 bg-amber-500/10 p-3 text-sm text-amber-200">
                                Perhatian: saat ini di luar jam layanan
                                {jamLayananHariIni.length > 0
                                    ? ` (${jamLayananHariIni.join(', ')})`
                                    : ''}
                                . Kunjungan tetap dapat dicatat.
                            </div>
                        )}

                        {jamLayananHariIni.length > 0 && (
                            <p className="mb-4 text-xs text-muted-foreground">
                                Jam layanan hari ini: {jamLayananHariIni.join(', ')}
                            </p>
                        )}

                        {tamu && (
                            <div className="mb-4 rounded-lg border border-emerald-400/40 bg-emerald-500/10 p-3 text-sm text-emerald-200">
                                Selamat datang kembali, {tamu.nama}! Kunjungan
                                selesai ke-{kunjunganSelesai}. Identitas terisi
                                otomatis.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="no_hp">Nomor HP</Label>

                                <div className="flex gap-2">
                                    <Input
                                        id="no_hp"
                                        name="no_hp"
                                        value={data.no_hp}
                                        autoComplete="tel"
                                        onChange={(e) =>
                                            setData('no_hp', e.target.value)
                                        }
                                    />
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={cekNomor}
                                    >
                                        Cek
                                    </Button>
                                </div>

                                <InputError
                                    message={errors.no_hp}
                                    className="mt-2"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="nama">Nama</Label>

                                <Input
                                    id="nama"
                                    name="nama"
                                    value={data.nama}
                                    onChange={(e) =>
                                        setData('nama', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.nama}
                                    className="mt-2"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Kategori</Label>

                                <div className="flex gap-4">
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="radio"
                                            name="kategori"
                                            value="perorangan"
                                            checked={data.kategori === 'perorangan'}
                                            onChange={(e) =>
                                                setData('kategori', e.target.value)
                                            }
                                        />
                                        Perorangan
                                    </label>
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="radio"
                                            name="kategori"
                                            value="instansi"
                                            checked={data.kategori === 'instansi'}
                                            onChange={(e) =>
                                                setData('kategori', e.target.value)
                                            }
                                        />
                                        Instansi
                                    </label>
                                </div>

                                <InputError
                                    message={errors.kategori}
                                    className="mt-2"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="instansi">Instansi</Label>

                                <Input
                                    id="instansi"
                                    name="instansi"
                                    value={data.instansi}
                                    onChange={(e) =>
                                        setData('instansi', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.instansi}
                                    className="mt-2"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="alamat">Alamat</Label>

                                <Input
                                    id="alamat"
                                    name="alamat"
                                    value={data.alamat}
                                    onChange={(e) =>
                                        setData('alamat', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.alamat}
                                    className="mt-2"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="keperluan">Keperluan</Label>

                                <Textarea
                                    id="keperluan"
                                    name="keperluan"
                                    value={data.keperluan}
                                    rows={4}
                                    placeholder="Tulis keperluan Anda dengan kalimat biasa..."
                                    onChange={(e) =>
                                        setData('keperluan', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.keperluan}
                                    className="mt-2"
                                />
                            </div>

                            <div className="flex items-center justify-end">
                                <Button type="submit" disabled={processing}>
                                    Kirim
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}