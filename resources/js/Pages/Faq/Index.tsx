import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function FaqIndex() {
    return (
        <>
            <Head title="FAQ" />
            <main className="flex min-h-screen items-center justify-center bg-background px-4 py-8">
                <Card className="w-full max-w-2xl">
                    <CardHeader>
                        <CardTitle>Asisten FAQ</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-sm text-muted-foreground">Tanya petugas jika informasi tidak tersedia.</p>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
