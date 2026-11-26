import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';

export default function RekapIndex(props: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Rekap</h2>}>
            <Head title="Rekap" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Rekap</CardTitle>
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
