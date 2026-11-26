<?php

namespace Tests\Feature\Ai;

use App\Enums\KlasifikasiStatus;
use App\Jobs\KirimNotifikasiKunjungan;
use App\Jobs\KlasifikasiKunjungan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Services\Ai\AiClassifier;
use App\Services\Ai\AiClassifierException;
use App\Services\PenugasanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class KlasifikasiKunjunganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai.base_url' => 'https://ai.test/v1',
            'ai.api_key' => 'kunci-uji',
            'ai.model' => 'model-utama',
            'ai.fallback_models' => [],
            'ai.timeout' => 5,
            'ai.max_retries' => 2,
        ]);

        Queue::fake();
    }

    public function test_ai_sukses_mengisi_unit_kategori_ringkasan_dan_pegawai(): void
    {
        $unit = Unit::factory()->create(['kode' => 'data', 'aktif' => true]);
        $pegawai = Pegawai::factory()->create(['unit_id' => $unit->id]);
        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->fakeAiSukses('data', 0.91);

        $this->jalankan($kunjungan);

        $kunjungan->refresh();

        $this->assertSame($unit->id, $kunjungan->unit_id);
        $this->assertSame('konsultasi_data', $kunjungan->kategori);
        $this->assertSame('Tamu ingin konsultasi data statistik.', $kunjungan->ringkasan_ai);
        $this->assertSame($pegawai->id, $kunjungan->pegawai_id);
        $this->assertSame(KlasifikasiStatus::Otomatis, $kunjungan->klasifikasi_status);

        Queue::assertPushed(KirimNotifikasiKunjungan::class, fn ($job) => $job->kunjungan->is($kunjungan));
        $this->assertNotNull($pegawai->fresh()->terakhir_ditugaskan_pada);
    }

    public function test_confidence_rendah_menandai_perlu_ditinjau(): void
    {
        Unit::factory()->create(['kode' => 'data', 'aktif' => true]);
        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->fakeAiSukses('data', 0.69);

        $this->jalankan($kunjungan);

        $kunjungan->refresh();

        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->klasifikasi_status);
        $this->assertNull($kunjungan->unit_id);
        $this->assertNull($kunjungan->pegawai_id);
        $this->assertNull($kunjungan->kategori);
        $this->assertNull($kunjungan->ringkasan_ai);
        Queue::assertNotPushed(KirimNotifikasiKunjungan::class);
    }

    public function test_unit_tidak_dikenal_menandai_perlu_ditinjau(): void
    {
        Unit::factory()->create(['kode' => 'data', 'aktif' => true]);
        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->fakeAiSukses('tidak-ada', 0.95);

        $this->jalankan($kunjungan);

        $kunjungan->refresh();

        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->klasifikasi_status);
        $this->assertNull($kunjungan->unit_id);
        Queue::assertNotPushed(KirimNotifikasiKunjungan::class);
    }

    public function test_unit_nonaktif_menandai_perlu_ditinjau(): void
    {
        Unit::factory()->create(['kode' => 'data', 'aktif' => false]);
        Unit::factory()->create(['kode' => 'umum', 'aktif' => true]);
        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->fakeAiSukses('data', 0.95);

        $this->jalankan($kunjungan);

        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->fresh()->klasifikasi_status);
    }

    public function test_tanpa_pegawai_tetap_menyimpan_hasil_ai_dan_menandai_perlu_ditinjau(): void
    {
        $unit = Unit::factory()->create(['kode' => 'data', 'aktif' => true]);
        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->fakeAiSukses('data', 0.91);

        $this->jalankan($kunjungan);

        $kunjungan->refresh();

        $this->assertSame($unit->id, $kunjungan->unit_id);
        $this->assertSame('konsultasi_data', $kunjungan->kategori);
        $this->assertNotNull($kunjungan->ringkasan_ai);
        $this->assertNull($kunjungan->pegawai_id);
        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->klasifikasi_status);
        Queue::assertNotPushed(KirimNotifikasiKunjungan::class);
    }

    public function test_ai_gagal_memicu_retry_lalu_failed_menandai_perlu_ditinjau(): void
    {
        Http::fake([
            'ai.test/*' => Http::response(['error' => 'server error'], 500),
        ]);

        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        try {
            $this->jalankan($kunjungan);
            $this->fail('AiClassifierException seharusnya dilempar agar job di-retry.');
        } catch (AiClassifierException $e) {
            $this->assertSame(KlasifikasiStatus::Diproses, $kunjungan->fresh()->klasifikasi_status);
        }

        (new KlasifikasiKunjungan($kunjungan))->failed(new AiClassifierException('gagal'));

        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->fresh()->klasifikasi_status);
    }

    public function test_konfigurasi_ai_kosong_langsung_menandai_perlu_ditinjau(): void
    {
        config(['ai.base_url' => null]);

        $kunjungan = Kunjungan::factory()->create(['keperluan' => 'Konsultasi data statistik.']);

        $this->jalankan($kunjungan);

        $this->assertSame(KlasifikasiStatus::PerluDitinjau, $kunjungan->fresh()->klasifikasi_status);
        Http::assertNothingSent();
    }

    public function test_tidak_memproses_ulang_kunjungan_yang_sudah_diklasifikasi(): void
    {
        $kunjungan = Kunjungan::factory()->create([
            'klasifikasi_status' => KlasifikasiStatus::Otomatis,
            'keperluan' => 'Konsultasi data statistik.',
        ]);

        $this->jalankan($kunjungan);

        Http::assertNothingSent();
    }

    public function test_jumlah_percobaan_mengikuti_konfigurasi_max_retries(): void
    {
        config(['ai.max_retries' => 2]);

        $job = new KlasifikasiKunjungan(Kunjungan::factory()->create());

        $this->assertSame(3, $job->tries());
    }

    private function jalankan(Kunjungan $kunjungan): void
    {
        (new KlasifikasiKunjungan($kunjungan))->handle(
            app(AiClassifier::class),
            app(PenugasanService::class),
        );
    }

    private function fakeAiSukses(string $unitKode, float $confidence): void
    {
        Http::fake([
            'ai.test/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'unit_kode' => $unitKode,
                            'kategori' => 'konsultasi_data',
                            'ringkasan' => 'Tamu ingin konsultasi data statistik.',
                            'confidence' => $confidence,
                        ]),
                    ],
                ]],
            ]),
        ]);
    }
}
