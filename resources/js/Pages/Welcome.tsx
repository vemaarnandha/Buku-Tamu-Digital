import { Head } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function Welcome() {
    return (
        <>
            <Head title="Buku Tamu Digital" />

            <main className="flex min-h-screen items-center justify-center bg-background px-6">
                <Card className="w-full max-w-xl">
                    <CardHeader>
                        <p className="text-sm font-medium text-muted-foreground">
                            Buku Tamu Digital
                        </p>
                        <CardTitle className="text-3xl">
                            Frontend siap dipakai
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-base leading-7 text-muted-foreground">
                            Inertia, React, TypeScript, Tailwind, dan shadcn/ui
                            sudah terpasang. Berikutnya kita bisa menyiapkan
                            halaman tamu dan dashboard.
                        </p>
                        <Button className="mt-6">Mulai</Button>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}