import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 py-8">
            <div className="mb-6 flex flex-col items-center gap-2">
                <Link href="/">
                    <ApplicationLogo className="h-16 w-16 fill-current text-primary" />
                </Link>
                <span className="text-sm font-medium text-muted-foreground">
                    Buku Tamu Digital
                </span>
            </div>

            <div className="w-full max-w-md overflow-hidden rounded-2xl bg-card/55 p-6 shadow-xl ring-1 ring-white/10 backdrop-blur-xl sm:p-8">
                {children}
            </div>
        </div>
    );
}