<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    public function index(Request $request): Response|StreamedResponse
    {
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');
        $unitId = $request->input('unit_id');
        $status = $request->input('status');
        $kategori = $request->input('kategori');

        $query = Kunjungan::query()
            ->with(['unit', 'tamu'])
            ->when($dari, fn ($q) => $q->whereDate('waktu_datang', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('waktu_datang', '<=', $sampai))
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori));

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

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

        $ratingQuery = Rating::query()
            ->whereIn('kunjungan_id', (clone $query)->pluck('id'));
        $rataRating = $ratingQuery->avg('nilai');

        return Inertia::render('Dashboard/Rekap/Index', [
            'filters' => [
                'dari' => $dari,
                'sampai' => $sampai,
                'unit_id' => $unitId,
                'status' => $status,
                'kategori' => $kategori,
            ],
            'stats' => [
                'total' => $total,
                'tidak_terlayani' => $tidakTerlayani,
                'rata_rata_rating' => $rataRating !== null ? round((float) $rataRating, 2) : null,
            ],
            'per_unit' => $perUnit,
            'kategori_terbanyak' => $kategoriTerbanyak,
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Kunjungan>  $rows
     */
    protected function exportCsv($rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['kode_publik', 'tamu', 'unit', 'status', 'kategori', 'waktu_datang']);

            foreach ($rows as $k) {
                fputcsv($handle, [
                    $k->kode_publik,
                    $k->tamu?->nama,
                    $k->unit?->nama,
                    $k->status->value,
                    $k->kategori,
                    optional($k->waktu_datang)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'rekap-kunjungan.csv', ['Content-Type' => 'text/csv']);
    }
}
