import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export default function RatingIndex({ no_hp, kunjungan, message }: any) {
    const form = useForm({
        no_hp: no_hp ?? '',
        kunjungan_id: kunjungan?.id ?? '',
        nilai: 5,
        komentar: '',
    });

    const cek = () => {
        if (!form.data.no_hp.trim()) return;
        window.location.href = `/rating?no_hp=${encodeURIComponent(form.data.no_hp)}`;
    };

    return (
        <>
            <Head title="Rating" />
            <main className="flex min-h-screen items-center justify-center bg-background px-4 py-8">
                <Card className="w-full max-w-xl">
                    <CardHeader>
                        <CardTitle>Beri Rating</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="no_hp">Nomor HP</Label>
                            <div className="flex gap-2">
                                <Input
                                    id="no_hp"
                                    value={form.data.no_hp}
                                    onChange={(e) => form.setData('no_hp', e.target.value)}
                                />
                                <Button type="button" variant="secondary" onClick={cek}>
                                    Cek
                                </Button>
                            </div>
                        </div>

                        {message && <p className="text-sm text-muted-foreground">{message}</p>}

                        {kunjungan && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    form.post('/rating');
                                }}
                                className="space-y-3"
                            >
                                <p className="text-sm">Kunjungan: {kunjungan.kode_publik}</p>
                                <div className="space-y-2">
                                    <Label>Nilai (1-5)</Label>
                                    <Input
                                        type="number"
                                        min={1}
                                        max={5}
                                        value={form.data.nilai}
                                        onChange={(e) => form.setData('nilai', Number(e.target.value))}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>Komentar</Label>
                                    <Textarea
                                        value={form.data.komentar}
                                        onChange={(e) => form.setData('komentar', e.target.value)}
                                    />
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    Kirim Rating
                                </Button>
                            </form>
                        )}
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
