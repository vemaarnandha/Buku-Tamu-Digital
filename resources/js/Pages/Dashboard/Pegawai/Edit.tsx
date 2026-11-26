import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';

export default function PegawaiEdit(props: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Pegawai Edit</h2>}>
            <Head title="Pegawai Edit" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Pegawai Edit</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre className="text-xs">{JSON.stringify(props, null, 2)}</pre>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
