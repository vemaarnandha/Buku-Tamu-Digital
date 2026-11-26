import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, useForm } from '@inertiajs/react';

export default function KunjunganShow({ kunjungan }: any) {
    const statusForm = useForm({ status: kunjungan?.status ?? 'menunggu', alasan_tidak_terlayani: '', catatan: '' });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Detail Kunjungan</h2>}>
            <Head title="Detail Kunjungan" />
            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>{kunjungan?.tamu?.nama}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <p>Kode: {kunjungan?.kode_publik}</p>
                            <p>Status: {kunjungan?.status}</p>
                            <p>Klasifikasi: {kunjungan?.klasifikasi_status}</p>
                            <p>Unit: {kunjungan?.unit?.nama ?? '-'}</p>
                            <p>Pegawai: {kunjungan?.pegawai?.nama ?? '-'}</p>
                            <p>Keperluan: {kunjungan?.keperluan}</p>
                            {kunjungan?.ringkasan_ai && <p>Ringkasan: {kunjungan.ringkasan_ai}</p>}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Ubah Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    statusForm.patch(route('dashboard.kunjungan.status', kunjungan.id));
                                }}
                                className="space-y-3"
                            >
                                <select
                                    className="w-full rounded border border-white/10 bg-background p-2"
                                    value={statusForm.data.status}
                                    onChange={(e) => statusForm.setData('status', e.target.value)}
                                >
                                    <option value="menunggu">menunggu</option>
                                    <option value="dilayani">dilayani</option>
                                    <option value="selesai">selesai</option>
                                    <option value="tidak_terlayani">tidak_terlayani</option>
                                </select>
                                <textarea
                                    className="w-full rounded border border-white/10 bg-background p-2"
                                    placeholder="Alasan tidak terlayani"
                                    value={statusForm.data.alasan_tidak_terlayani}
                                    onChange={(e) => statusForm.setData('alasan_tidak_terlayani', e.target.value)}
                                />
                                <textarea
                                    className="w-full rounded border border-white/10 bg-background p-2"
                                    placeholder="Catatan"
                                    value={statusForm.data.catatan}
                                    onChange={(e) => statusForm.setData('catatan', e.target.value)}
                                />
                                <Button type="submit" disabled={statusForm.processing}>
                                    Simpan Status
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
