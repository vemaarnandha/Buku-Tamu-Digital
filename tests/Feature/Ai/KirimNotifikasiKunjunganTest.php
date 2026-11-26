<?php

namespace Tests\Feature\Ai;

use App\Jobs\KirimNotifikasiKunjungan;
use App\Mail\NotifikasiKunjungan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Services\Notifikasi\FonnteWhatsAppGateway;
use App\Services\Notifikasi\LogWhatsAppGateway;
use App\Services\Notifikasi\WhatsAppGateway;
use App\Services\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class KirimNotifikasiKunjunganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'notifikasi.whatsapp.driver' => 'log',
            'notifikasi.email.enabled' => true,
        ]);

        Mail::fake();
    }

    public function test_mengirim_whatsapp_log_dan_email_lalu_mencatat_waktu(): void
    {
        $unit = Unit::factory()->create(['lokasi_ruang' => 'Ruang Tamu Unit Data']);
        $pegawai = Pegawai::factory()->create([
            'unit_id' => $unit->id,
            'nama' => 'Pegawai Data 1',
            'email' => 'pegawai.data.1@example.com',
        ]);
        $kunjungan = Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $pegawai->id,
            'keperluan' => 'Konsultasi data',
            'ringkasan_ai' => 'Tamu ingin konsultasi data.',
        ]);

        $job = new KirimNotifikasiKunjungan($kunjungan);
        $job->handle(app(NotifikasiService::class));

        $kunjungan->refresh();

        $this->assertNotNull($kunjungan->notifikasi_terkirim_pada);

        Mail::assertSent(NotifikasiKunjungan::class, fn ($mail) => $mail->hasTo($pegawai->email));
    }

    public function test_tidak_mengirim_email_jika_pegawai_tidak_punya_email(): void
    {
        $unit = Unit::factory()->create();
        $pegawai = Pegawai::factory()->create(['unit_id' => $unit->id, 'email' => null]);
        $kunjungan = Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $pegawai->id,
        ]);

        (new KirimNotifikasiKunjungan($kunjungan))->handle(app(NotifikasiService::class));

        Mail::assertNothingSent();
        $this->assertNotNull($kunjungan->fresh()->notifikasi_terkirim_pada);
    }

    public function test_melewati_pengiriman_jika_sudah_terkirim(): void
    {
        $unit = Unit::factory()->create();
        $pegawai = Pegawai::factory()->create(['unit_id' => $unit->id, 'email' => 'pegawai@example.com']);
        $kunjungan = Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $pegawai->id,
            'notifikasi_terkirim_pada' => now(),
        ]);

        (new KirimNotifikasiKunjungan($kunjungan))->handle(app(NotifikasiService::class));

        Mail::assertNothingSent();
    }

    public function test_melewati_pengiriman_jika_belum_ada_pegawai(): void
    {
        $kunjungan = Kunjungan::factory()->create(['pegawai_id' => null]);

        (new KirimNotifikasiKunjungan($kunjungan))->handle(app(NotifikasiService::class));

        Mail::assertNothingSent();
        $this->assertNull($kunjungan->fresh()->notifikasi_terkirim_pada);
    }

    public function test_kegagalan_whatsapp_membuat_job_gagal_dan_timestamp_null(): void
    {
        $unit = Unit::factory()->create();
        $pegawai = Pegawai::factory()->create(['unit_id' => $unit->id, 'email' => 'pegawai@example.com']);
        $kunjungan = Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $pegawai->id,
        ]);

        $this->app->instance(WhatsAppGateway::class, new class implements WhatsAppGateway
        {
            public function kirim(string $noWhatsapp, string $pesan): void
            {
                throw new \RuntimeException('gagal');
            }
        });

        try {
            (new KirimNotifikasiKunjungan($kunjungan))->handle(app(NotifikasiService::class));
            $this->fail('RuntimeException seharusnya dilempar.');
        } catch (\RuntimeException $e) {
            $this->assertSame('gagal', $e->getMessage());
        }

        $this->assertNull($kunjungan->fresh()->notifikasi_terkirim_pada);
    }

    public function test_log_gateway_mencatat_notifikasi(): void
    {
        Log::spy();

        $gateway = new LogWhatsAppGateway;
        $gateway->kirim('628123456789', 'pesan uji');

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Notifikasi WhatsApp (driver log).', Mockery::on(fn ($konteks) => isset($konteks['tujuan']) && $konteks['pesan'] === 'pesan uji'));
    }

    public function test_fonnte_gateway_mengirim_request_http(): void
    {
        config([
            'notifikasi.whatsapp.url' => 'https://fonnte.test/send',
            'notifikasi.whatsapp.token' => 'token-rahasia',
            'notifikasi.whatsapp.timeout' => 15,
        ]);

        Http::fake([
            'fonnte.test/*' => Http::response(['status' => true], 200),
        ]);

        $gateway = new FonnteWhatsAppGateway;
        $gateway->kirim('628123456789', 'pesan uji');

        Http::assertSent(fn ($request) => $request->url() === 'https://fonnte.test/send'
            && $request['target'] === '628123456789'
            && $request['message'] === 'pesan uji'
            && $request->hasHeader('Authorization', 'token-rahasia'));
    }

    public function test_backoff_dan_tries_kirim_notifikasi(): void
    {
        $job = new KirimNotifikasiKunjungan(Kunjungan::factory()->create());

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120], $job->backoff());
    }
}
