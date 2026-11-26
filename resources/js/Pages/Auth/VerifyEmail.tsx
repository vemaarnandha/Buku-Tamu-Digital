import { Button } from '@/components/ui/button';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Email Verification" />

            <div className="mb-6">
                <h1 className="text-xl font-semibold text-foreground">
                    Verify Email
                </h1>
                <p className="text-sm text-muted-foreground">
                    Thanks for signing up! Before getting started, please verify
                    your email address by clicking the link we just emailed to
                    you. If you didn't receive the email, we will gladly send
                    you another.
                </p>
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-sm font-medium text-emerald-400">
                    A new verification link has been sent to your email
                    address.
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="flex items-center justify-between">
                    <Button type="submit" disabled={processing}>
                        Resend Verification Email
                    </Button>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                    >
                        Log Out
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}