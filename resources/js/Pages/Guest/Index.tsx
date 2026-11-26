import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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
                            <div className="mb-4 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
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
                            <div className="mb-4 rounded-md border border-green-300 bg-green-50 p-3 text-sm text-green-800">
                                Selamat datang kembali, {tamu.nama}! Kunjungan
                                selesai ke-{kunjunganSelesai}. Identitas terisi
                                otomatis.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="no_hp" value="Nomor HP" />

                                <div className="mt-1 flex gap-2">
                                    <TextInput
                                        id="no_hp"
                                        name="no_hp"
                                        value={data.no_hp}
                                        className="block w-full"
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

                            <div>
                                <InputLabel htmlFor="nama" value="Nama" />

                                <TextInput
                                    id="nama"
                                    name="nama"
                                    value={data.nama}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('nama', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.nama}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="kategori"
                                    value="Kategori"
                                />

                                <div className="mt-1 flex gap-4">
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

                            <div>
                                <InputLabel
                                    htmlFor="instansi"
                                    value="Instansi"
                                />

                                <TextInput
                                    id="instansi"
                                    name="instansi"
                                    value={data.instansi}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('instansi', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.instansi}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel htmlFor="alamat" value="Alamat" />

                                <TextInput
                                    id="alamat"
                                    name="alamat"
                                    value={data.alamat}
                                    className="mt-1 block w-full"
                                    onChange={(e) =>
                                        setData('alamat', e.target.value)
                                    }
                                />

                                <InputError
                                    message={errors.alamat}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="keperluan"
                                    value="Keperluan"
                                />

                                <textarea
                                    id="keperluan"
                                    name="keperluan"
                                    value={data.keperluan}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                <PrimaryButton disabled={processing}>
                                    Kirim
                                </PrimaryButton>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}