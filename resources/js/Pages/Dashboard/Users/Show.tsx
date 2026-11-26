import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';

export default function UsersShow(props: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Users Show</h2>}>
            <Head title="Users Show" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Users Show</CardTitle>
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
