<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class RingkasanController extends Controller
{
    public function index(Request $request): Response
    {
        $now = Carbon::now();
        $start = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
        $end = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);

        $cacheKey = 'ringkasan_mingguan:'.$start->toDateString().':'.$end->toDateString();

        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($start, $end) {
            return $this->hitung($start, $end);
        });

        return Inertia::render('Dashboard/Ringkasan/Index', [
            'periode' => [
                'mulai' => $start->toDateString(),
                'selesai' => $end->toDateString(),
            ],
            'stats' => $data['stats'],
            'per_unit' => $data['per_unit'],
            'kategori_terbanyak' => $data['kategori_terbanyak'],
            'narasi' => $data['narasi'],
            'cached' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function hitung(Carbon $start, Carbon $end): array
    {
        $query = Kunjungan::query()
            ->whereBetween('waktu_datang', [$start, $end]);

        $total = (clone $query)->count();
        $tidakTerlayani = (clone $query)->where('status', 'tidak_terlayani')->count();

        $perUnit = (clone $query)
            ->selectRaw('unit_id, count(*) as total')
            ->groupBy('unit_id')
            ->get()
            ->map(function ($row) {
                $unit = $row->unit_id ? Unit::find($row->unit_id) : null;

                return [
                    'unit_id' => $row->unit_id,
                    'unit' => $unit?->nama ?? 'Tanpa unit',
                    'total' => (int) $row->total,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $kategoriTerbanyak = (clone $query)
            ->selectRaw('kategori, count(*) as total')
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['kategori' => $row->kategori, 'total' => (int) $row->total])
            ->values();

        $ratingIds = (clone $query)->pluck('id');
        $rataRating = Rating::whereIn('kunjungan_id', $ratingIds)->avg('nilai');

        $stats = [
            'total' => $total,
            'tidak_terlayani' => $tidakTerlayani,
            'rata_rata_rating' => $rataRating !== null ? round((float) $rataRating, 2) : null,
        ];

        $narasi = $this->narasiAi($stats, $perUnit, $kategoriTerbanyak);

        return [
            'stats' => $stats,
            'per_unit' => $perUnit,
            'kategori_terbanyak' => $kategoriTerbanyak,
            'narasi' => $narasi,
        ];
    }

    /**
     * @param  mixed  $perUnit
     * @param  mixed  $kategoriTerbanyak
     */
    protected function narasiAi(array $stats, $perUnit, $kategoriTerbanyak): ?string
    {
        $models = collect([config('ai.model'), ...(array) config('ai.fallback_models', [])])
            ->filter(fn ($model) => is_string($model) && trim($model) !== '')
            ->map(fn (string $model) => trim($model))
            ->unique()
            ->values()
            ->all();

        if ($models === [] || blank(config('ai.base_url')) || blank(config('ai.api_key'))) {
            return null;
        }

        $endpoint = str_ends_with(rtrim((string) config('ai.base_url'), '/'), '/chat/completions')
            ? rtrim((string) config('ai.base_url'), '/')
            : rtrim((string) config('ai.base_url'), '/').'/chat/completions';

        $ringkasan = 'Statistik minggu lalu: total '.$stats['total'].', tidak terlayani '.$stats['tidak_terlayani'].', rata-rata rating '.($stats['rata_rata_rating'] ?? '-').'.';

        foreach ($models as $model) {
            try {
                $response = Http::withToken((string) config('ai.api_key'))
                    ->timeout((int) config('ai.timeout', 30))
                    ->acceptJson()
                    ->asJson()
                    ->post($endpoint, [
                        'model' => $model,
                        'temperature' => 0.3,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda asisten pimpinan. Buat narasi singkat (2-3 kalimat) dari statistik kunjungan.'],
                            ['role' => 'user', 'content' => $ringkasan],
                        ],
                    ]);

                if ($response->failed()) {
                    continue;
                }

                $content = data_get($response->json(), 'choices.0.message.content');

                if (is_string($content) && trim($content) !== '') {
                    return trim($content);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
