import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';

export default function KunjunganIndex({ kunjungans, stats }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Antrean Kunjungan</h2>}>
            <Head title="Antrean Kunjungan" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                        {Object.entries(stats ?? {}).map(([k, v]) => (
                            <Card key={k}>
                                <CardHeader>
                                    <CardTitle className="text-sm capitalize">{k}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-bold">{String(v)}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Daftar Kunjungan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {(kunjungans ?? []).map((k: any) => (
                                    <div key={k.id} className="rounded border border-white/10 p-3">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-medium">{k.tamu?.nama}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {k.kode_publik} · {k.status} · {k.klasifikasi_status}
                                                </p>
                                            </div>
                                            <Link
                                                href={route('dashboard.kunjungan.show', k.id)}
                                                className="text-sm text-primary"
                                            >
                                                Detail
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
