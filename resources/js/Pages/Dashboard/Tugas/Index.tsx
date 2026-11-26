import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';

export default function TugasIndex({ kunjungans }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Tugas Saya</h2>}>
            <Head title="Tugas Saya" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Daftar Tugas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {(kunjungans ?? []).map((k: any) => (
                                    <div key={k.id} className="rounded border border-white/10 p-3">
                                        <p className="font-medium">{k.tamu?.nama}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {k.kode_publik} · {k.status} · {k.unit?.nama}
                                        </p>
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
