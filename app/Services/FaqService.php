<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FaqService
{
    /**
     * @return array{jawaban: string, sumber: array<int, int>}
     */
    public function jawab(string $pertanyaan, ?int $unitId = null): array
    {
        $faqs = $this->pilihFaq($pertanyaan, $unitId);

        if ($faqs->isEmpty()) {
            return [
                'jawaban' => 'Maaf, belum ada informasi yang tersedia. Silakan tanya petugas.',
                'sumber' => [],
            ];
        }

        $konteks = $faqs->map(fn (Faq $faq) => "ID {$faq->id}: Q: {$faq->pertanyaan}\nA: {$faq->jawaban}")->implode("\n\n");

        $models = collect([config('ai.model'), ...(array) config('ai.fallback_models', [])])
            ->filter(fn ($model) => is_string($model) && trim($model) !== '')
            ->map(fn (string $model) => trim($model))
            ->unique()
            ->values()
            ->all();

        if ($models === [] || blank(config('ai.base_url')) || blank(config('ai.api_key'))) {
            return [
                'jawaban' => $faqs->first()->jawaban,
                'sumber' => [$faqs->first()->id],
            ];
        }

        $endpoint = str_ends_with(rtrim((string) config('ai.base_url'), '/'), '/chat/completions')
            ? rtrim((string) config('ai.base_url'), '/')
            : rtrim((string) config('ai.base_url'), '/').'/chat/completions';

        foreach ($models as $model) {
            try {
                $response = Http::withToken((string) config('ai.api_key'))
                    ->timeout((int) config('ai.timeout', 30))
                    ->acceptJson()
                    ->asJson()
                    ->post($endpoint, [
                        'model' => $model,
                        'temperature' => 0,
                        'messages' => [
                            ['role' => 'system', 'content' => $this->systemPrompt()],
                            ['role' => 'user', 'content' => "FAQ:\n{$konteks}\n\nPertanyaan: {$pertanyaan}"],
                        ],
                    ]);

                if ($response->failed()) {
                    continue;
                }

                $content = data_get($response->json(), 'choices.0.message.content');
                $data = $this->decodeContent($content);

                if (is_array($data) && isset($data['jawaban'])) {
                    $sumber = collect($data['sumber'] ?? [])
                        ->filter(fn ($id) => $faqs->pluck('id')->contains($id))
                        ->values()
                        ->all();

                    return [
                        'jawaban' => (string) $data['jawaban'],
                        'sumber' => $sumber === [] ? [$faqs->first()->id] : $sumber,
                    ];
                }

                if (is_string($content) && trim($content) !== '') {
                    return [
                        'jawaban' => trim($content),
                        'sumber' => [$faqs->first()->id],
                    ];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [
            'jawaban' => $faqs->first()->jawaban,
            'sumber' => [$faqs->first()->id],
        ];
    }

    /**
     * @return Collection<int, Faq>
     */
    protected function pilihFaq(string $pertanyaan, ?int $unitId)
    {
        $query = Faq::query()->where('aktif', true);

        if ($unitId !== null) {
            $query->where(function ($q) use ($unitId) {
                $q->whereNull('unit_id')->orWhere('unit_id', $unitId);
            });
        }

        $faqs = $query->get();

        $tokens = collect(preg_split('/\s+/', Str::lower($pertanyaan)))
            ->map(fn ($t) => trim($t, " \t\n\r\0\x0B.,!?;:()[]{}\"'"))
            ->filter(fn ($t) => mb_strlen($t) >= 3)
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return $faqs->take(8)->values();
        }

        $scored = $faqs->map(function (Faq $faq) use ($tokens) {
            $haystack = Str::lower($faq->pertanyaan.' '.$faq->jawaban);
            $score = 0;
            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score++;
                }
            }

            return ['faq' => $faq, 'score' => $score];
        })->sortByDesc('score')->values();

        $selected = $scored->filter(fn ($row) => $row['score'] > 0)->take(8);

        if ($selected->isEmpty()) {
            $selected = $scored->take(8);
        }

        return $selected->pluck('faq')->values();
    }

    protected function systemPrompt(): string
    {
        return 'Anda adalah asisten FAQ. Jawab HANYA berdasarkan FAQ yang diberikan. '
            .'Jika tidak ada informasi yang relevan, katakan belum ada informasinya dan sarankan bertanya ke petugas. '
            .'Balas HANYA dengan JSON valid: {"jawaban":"...","sumber":[1,2]} dengan sumber berisi ID FAQ yang dipakai.';
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
