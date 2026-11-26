<?php

namespace App\Services\Ai;

use App\Models\Unit;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiClassifier
{
    public function klasifikasi(string $keperluan): HasilKlasifikasi
    {
        $models = $this->models();

        if ($models === []) {
            throw new AiBelumDikonfigurasiException('Model AI belum dikonfigurasi.');
        }

        if (blank(config('ai.base_url')) || blank(config('ai.api_key'))) {
            throw new AiBelumDikonfigurasiException('Base URL atau API key AI belum dikonfigurasi.');
        }

        $units = Unit::query()
            ->where('aktif', true)
            ->orderBy('kode')
            ->get(['kode', 'nama', 'deskripsi']);

        if ($units->isEmpty()) {
            throw new AiClassifierException('Tidak ada unit aktif untuk diklasifikasi.');
        }

        $prompt = $this->systemPrompt($units);
        $teksKeperluan = Str::limit(trim($keperluan), 1000, '');

        foreach ($models as $model) {
            try {
                $response = Http::withToken((string) config('ai.api_key'))
                    ->timeout((int) config('ai.timeout', 30))
                    ->acceptJson()
                    ->asJson()
                    ->post($this->endpoint(), [
                        'model' => $model,
                        'temperature' => 0,
                        'messages' => [
                            ['role' => 'system', 'content' => $prompt],
                            ['role' => 'user', 'content' => $teksKeperluan],
                        ],
                    ]);

                if ($response->status() === 429 || $response->serverError() || $response->failed()) {
                    Log::warning('Klasifikasi AI gagal pada model.', [
                        'model' => $model,
                        'status' => $response->status(),
                    ]);

                    continue;
                }

                $data = $this->decodeContent(data_get($response->json(), 'choices.0.message.content'));

                if ($data === null) {
                    Log::warning('Respons AI bukan JSON yang valid.', ['model' => $model]);

                    continue;
                }

                $hasil = HasilKlasifikasi::fromArray($data);

                if ($hasil === null) {
                    Log::warning('Struktur JSON klasifikasi AI tidak valid.', ['model' => $model]);

                    continue;
                }

                Log::info('Klasifikasi AI berhasil.', ['model' => $model]);

                return $hasil;
            } catch (ConnectionException $e) {
                Log::warning('Koneksi ke layanan AI gagal.', [
                    'model' => $model,
                    'pesan' => $e->getMessage(),
                ]);
            }
        }

        throw new AiClassifierException('Semua model AI gagal melakukan klasifikasi.');
    }

    /**
     * @return array<int, string>
     */
    protected function models(): array
    {
        return collect([config('ai.model'), ...(array) config('ai.fallback_models', [])])
            ->filter(fn ($model) => is_string($model) && trim($model) !== '')
            ->map(fn (string $model) => trim($model))
            ->unique()
            ->values()
            ->all();
    }

    protected function endpoint(): string
    {
        $baseUrl = rtrim((string) config('ai.base_url'), '/');

        return str_ends_with($baseUrl, '/chat/completions')
            ? $baseUrl
            : $baseUrl.'/chat/completions';
    }

    /**
     * @param  Collection<int, Unit>  $units
     */
    protected function systemPrompt($units): string
    {
        $daftarUnit = $units
            ->map(fn (Unit $unit) => '- kode: '.$unit->kode.' | nama: '.$unit->nama.' | deskripsi: '.($unit->deskripsi ?: '-'))
            ->implode("\n");

        return <<<PROMPT
Anda adalah asisten resepsionis pada instansi pelayanan publik.

Tugas Anda: tentukan unit tujuan berdasarkan teks keperluan tamu.

Daftar unit yang tersedia:
{$daftarUnit}

Balas HANYA dengan JSON valid tanpa penjelasan tambahan, dengan format:
{"unit_kode":"kode unit","kategori":"kategori singkat","ringkasan":"satu kalimat ringkasan keperluan","confidence":0.0}

Aturan:
- unit_kode harus salah satu kode unit di daftar.
- kategori diisi singkat, contoh: konsultasi_data, administrasi, pengaduan, kepegawaian, informasi_umum.
- ringkasan ditulis dalam bahasa Indonesia, maksimal satu kalimat.
- confidence bernilai antara 0 dan 1.
- Jika keperluan tidak jelas, gunakan confidence rendah.
PROMPT;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeContent(mixed $content): ?array
    {
        if (is_array($content)) {
            return $content;
        }

        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $content = trim($content);

        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $content, $matches)) {
            $content = $matches[1];
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $decoded = json_decode(substr($content, $start, $end - $start + 1), true);

        return is_array($decoded) ? $decoded : null;
    }
}
