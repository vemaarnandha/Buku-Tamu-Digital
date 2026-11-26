<?php

namespace Tests\Feature\Ai;

use App\Models\Unit;
use App\Services\Ai\AiBelumDikonfigurasiException;
use App\Services\Ai\AiClassifier;
use App\Services\Ai\AiClassifierException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiClassifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai.base_url' => 'https://ai.test/v1',
            'ai.api_key' => 'kunci-uji',
            'ai.model' => 'model-utama',
            'ai.fallback_models' => ['model-cadangan'],
            'ai.timeout' => 5,
        ]);

        Unit::factory()->create([
            'kode' => 'data',
            'nama' => 'Unit Data',
            'deskripsi' => 'Melayani konsultasi data statistik.',
            'aktif' => true,
        ]);

        Unit::factory()->create([
            'kode' => 'umum',
            'nama' => 'Unit Umum',
            'aktif' => true,
        ]);
    }

    public function test_mengembalikan_hasil_dari_model_utama(): void
    {
        Http::fake([
            'ai.test/*' => Http::response($this->responsAi([
                'unit_kode' => 'data',
                'kategori' => 'konsultasi_data',
                'ringkasan' => 'Tamu ingin konsultasi data statistik.',
                'confidence' => 0.91,
            ])),
        ]);

        $hasil = app(AiClassifier::class)->klasifikasi('Saya ingin konsultasi data statistik.');

        $this->assertSame('data', $hasil->unitKode);
        $this->assertSame('konsultasi_data', $hasil->kategori);
        $this->assertSame('Tamu ingin konsultasi data statistik.', $hasil->ringkasan);
        $this->assertSame(0.91, $hasil->confidence);

        Http::assertSent(fn (Request $request) => $request['model'] === 'model-utama'
            && $request->url() === 'https://ai.test/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer kunci-uji')
            && str_contains($request['messages'][0]['content'], 'kode: data')
            && $request['messages'][1]['content'] === 'Saya ingin konsultasi data statistik.');
    }

    public function test_menerima_json_dalam_code_fence(): void
    {
        Http::fake([
            'ai.test/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "```json\n".json_encode([
                            'unit_kode' => 'umum',
                            'kategori' => 'informasi_umum',
                            'ringkasan' => 'Tamu membutuhkan informasi umum.',
                            'confidence' => 0.80,
                        ])."\n```",
                    ],
                ]],
            ]),
        ]);

        $hasil = app(AiClassifier::class)->klasifikasi('Mau tanya informasi.');

        $this->assertSame('umum', $hasil->unitKode);
    }

    public function test_fallback_ke_model_berikutnya_saat_http_429(): void
    {
        Http::fake(function (Request $request) {
            if ($request['model'] === 'model-utama') {
                return Http::response(['error' => 'rate limited'], 429);
            }

            return Http::response($this->responsAi($this->hasilData()));
        });

        $hasil = app(AiClassifier::class)->klasifikasi('Konsultasi data.');

        $this->assertSame('data', $hasil->unitKode);
        Http::assertSentCount(2);
    }

    public function test_fallback_ke_model_berikutnya_saat_http_5xx(): void
    {
        Http::fake(function (Request $request) {
            if ($request['model'] === 'model-utama') {
                return Http::response(['error' => 'server error'], 503);
            }

            return Http::response($this->responsAi($this->hasilData()));
        });

        $hasil = app(AiClassifier::class)->klasifikasi('Konsultasi data.');

        $this->assertSame('data', $hasil->unitKode);
        Http::assertSentCount(2);
    }

    public function test_fallback_ke_model_berikutnya_saat_timeout(): void
    {
        $koneksiGagal = Http::failedConnection();

        Http::fake(function (Request $request) use ($koneksiGagal) {
            if ($request['model'] === 'model-utama') {
                return $koneksiGagal($request);
            }

            return Http::response($this->responsAi($this->hasilData()));
        });

        $hasil = app(AiClassifier::class)->klasifikasi('Konsultasi data.');

        $this->assertSame('data', $hasil->unitKode);
        Http::assertSentCount(2);
    }

    public function test_fallback_ke_model_berikutnya_saat_json_tidak_valid(): void
    {
        Http::fake(function (Request $request) {
            if ($request['model'] === 'model-utama') {
                return Http::response([
                    'choices' => [['message' => ['content' => 'Maaf, saya tidak bisa membantu.']]],
                ]);
            }

            return Http::response($this->responsAi($this->hasilData()));
        });

        $hasil = app(AiClassifier::class)->klasifikasi('Konsultasi data.');

        $this->assertSame('data', $hasil->unitKode);
        Http::assertSentCount(2);
    }

    public function test_gagal_jika_semua_model_gagal(): void
    {
        Http::fake([
            'ai.test/*' => Http::response(['error' => 'server error'], 500),
        ]);

        try {
            app(AiClassifier::class)->klasifikasi('Konsultasi data.');
            $this->fail('AiClassifierException seharusnya dilempar.');
        } catch (AiClassifierException $e) {
            $this->assertSame('Semua model AI gagal melakukan klasifikasi.', $e->getMessage());
        }

        Http::assertSentCount(2);
    }

    public function test_gagal_jika_konfigurasi_ai_belum_lengkap(): void
    {
        config(['ai.base_url' => null]);

        $this->expectException(AiBelumDikonfigurasiException::class);

        app(AiClassifier::class)->klasifikasi('Konsultasi data.');
    }

    public function test_gagal_jika_tidak_ada_unit_aktif(): void
    {
        Unit::query()->update(['aktif' => false]);

        $this->expectException(AiClassifierException::class);

        app(AiClassifier::class)->klasifikasi('Konsultasi data.');
    }

    /**
     * @return array<string, mixed>
     */
    private function responsAi(array $data): array
    {
        return [
            'choices' => [
                ['message' => ['content' => json_encode($data)]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hasilData(): array
    {
        return [
            'unit_kode' => 'data',
            'kategori' => 'konsultasi_data',
            'ringkasan' => 'Tamu ingin konsultasi data statistik.',
            'confidence' => 0.91,
        ];
    }
}
