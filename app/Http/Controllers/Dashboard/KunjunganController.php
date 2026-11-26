<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\KlasifikasiStatus;
use App\Enums\Role;
use App\Enums\StatusKunjungan;
use App\Http\Controllers\Controller;
use App\Jobs\KirimNotifikasiKunjungan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Services\PenugasanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class KunjunganController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Kunjungan::query()
            ->with(['tamu', 'unit', 'pegawai'])
            ->latest('waktu_datang');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('klasifikasi_status')) {
            $query->where('klasifikasi_status', $request->string('klasifikasi_status'));
        }

        if ($request->boolean('perlu_ditinjau')) {
            $query->where('klasifikasi_status', KlasifikasiStatus::PerluDitinjau);
        }

        if ($request->boolean('tanpa_pegawai')) {
            $query->whereNull('pegawai_id');
        }

        if ($request->boolean('menunggu_lama')) {
            $query->where('status', StatusKunjungan::Menunggu)
                ->where('waktu_datang', '<=', now()->subMinutes(15));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->integer('unit_id'));
        }

        $kunjungans = $query->limit(200)->get();

        $formatted = $kunjungans->map(fn (Kunjungan $k) => $this->format($k))->all();

        $stats = [
            'menunggu' => Kunjungan::where('status', StatusKunjungan::Menunggu)->count(),
            'dilayani' => Kunjungan::where('status', StatusKunjungan::Dilayani)->count(),
            'selesai' => Kunjungan::where('status', StatusKunjungan::Selesai)->count(),
            'tidak_terlayani' => Kunjungan::where('status', StatusKunjungan::TidakTerlayani)->count(),
        ];

        return Inertia::render('Dashboard/Kunjungan/Index', [
            'kunjungans' => $formatted,
            'kunjungan' => $formatted,
            'items' => $formatted,
            'stats' => $stats,
            'statistik' => $stats,
            'filters' => $request->only([
                'status', 'klasifikasi_status', 'perlu_ditinjau', 'tanpa_pegawai',
                'menunggu_lama', 'unit_id',
            ]),
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'kode', 'nama']),
        ]);
    }

    public function show(Kunjungan $kunjungan): Response
    {
        $kunjungan->load(['tamu', 'unit', 'pegawai', 'diubahOleh']);

        return Inertia::render('Dashboard/Kunjungan/Show', [
            'kunjungan' => $this->format($kunjungan, true),
            'units' => Unit::where('aktif', true)->orderBy('nama')->get(['id', 'kode', 'nama']),
            'pegawais' => Pegawai::where('aktif', true)->orderBy('nama')->get(['id', 'unit_id', 'nama', 'jabatan']),
        ]);
    }

    public function klasifikasi(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $this->authorize('correctClassification', $kunjungan);

        $validated = $request->validate([
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'ringkasan_ai' => ['nullable', 'string', 'max:5000'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $ringkasan = $validated['ringkasan_ai'] ?? $validated['ringkasan'] ?? $kunjungan->ringkasan_ai;

        if ($request->user()->role === Role::Admin && empty($validated['catatan'])) {
            throw ValidationException::withMessages(['catatan' => 'Catatan wajib diisi untuk koreksi admin.']);
        }

        $unitChanged = array_key_exists('unit_id', $validated)
            && (int) $validated['unit_id'] !== (int) $kunjungan->unit_id;

        DB::transaction(function () use ($kunjungan, $validated, $ringkasan, $unitChanged, $request): void {
            $kunjungan->forceFill([
                'unit_id' => array_key_exists('unit_id', $validated) ? $validated['unit_id'] : $kunjungan->unit_id,
                'kategori' => array_key_exists('kategori', $validated) ? $validated['kategori'] : $kunjungan->kategori,
                'ringkasan_ai' => $ringkasan,
                'klasifikasi_status' => KlasifikasiStatus::Manual,
                'diubah_oleh' => $request->user()->id,
                'catatan' => array_key_exists('catatan', $validated) ? $validated['catatan'] : $kunjungan->catatan,
            ])->save();

            if ($unitChanged) {
                $kunjungan->forceFill(['pegawai_id' => null])->save();

                $unit = $kunjungan->unit;
                if ($unit) {
                    $pegawai = app(PenugasanService::class)->tugaskan($kunjungan, $unit);
                    if ($pegawai) {
                        KirimNotifikasiKunjungan::dispatch($kunjungan->fresh());
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Klasifikasi diperbarui.');
    }

    public function pegawai(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $this->authorize('assignPegawai', $kunjungan);

        $validated = $request->validate([
            'pegawai_id' => ['nullable', 'integer', 'exists:pegawai,id'],
        ]);

        if (array_key_exists('pegawai_id', $validated)) {
            $pegawai = $validated['pegawai_id'] ? Pegawai::find($validated['pegawai_id']) : null;

            $kunjungan->forceFill(['pegawai_id' => $pegawai?->id])->save();

            if ($pegawai) {
                $pegawai->forceFill(['terakhir_ditugaskan_pada' => now()])->save();
                KirimNotifikasiKunjungan::dispatch($kunjungan->fresh());
            }
        }

        return redirect()->back()->with('success', 'Pegawai diperbarui.');
    }

    public function status(Request $request, Kunjungan $kunjungan): RedirectResponse
    {
        $this->authorize('updateStatus', $kunjungan);

        $user = $request->user();

        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusKunjungan::class)],
            'alasan_tidak_terlayani' => ['nullable', 'string', 'max:5000'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $current = $kunjungan->status;
        $target = StatusKunjungan::from($validated['status']);

        $standard = [
            StatusKunjungan::Menunggu->value => [StatusKunjungan::Dilayani->value, StatusKunjungan::TidakTerlayani->value],
            StatusKunjungan::Dilayani->value => [StatusKunjungan::Selesai->value, StatusKunjungan::TidakTerlayani->value],
        ];

        $isStandard = isset($standard[$current->value]) && in_array($target->value, $standard[$current->value], true);

        if ($user->role === Role::Pegawai) {
            if (! in_array($target->value, [StatusKunjungan::Dilayani->value, StatusKunjungan::Selesai->value], true)) {
                throw ValidationException::withMessages(['status' => 'Transisi tidak diizinkan.']);
            }
            if ($current === StatusKunjungan::Menunggu && $target !== StatusKunjungan::Dilayani) {
                throw ValidationException::withMessages(['status' => 'Transisi tidak diizinkan.']);
            }
            if ($current === StatusKunjungan::Dilayani && $target !== StatusKunjungan::Selesai) {
                throw ValidationException::withMessages(['status' => 'Transisi tidak diizinkan.']);
            }
        }

        if (! $isStandard) {
            if ($user->role !== Role::Admin) {
                throw ValidationException::withMessages(['status' => 'Transisi tidak diizinkan.']);
            }
            $request->validate(['catatan' => ['required', 'string', 'max:5000']]);
        } else {
            if ($target === StatusKunjungan::Dilayani && $kunjungan->pegawai_id === null) {
                throw ValidationException::withMessages(['pegawai_id' => 'Pegawai wajib diisi.']);
            }
            if ($target === StatusKunjungan::TidakTerlayani && empty($validated['alasan_tidak_terlayani'])) {
                throw ValidationException::withMessages(['alasan_tidak_terlayani' => 'Alasan wajib diisi.']);
            }
            if ($target === StatusKunjungan::TidakTerlayani && $user->role === Role::Pegawai) {
                throw ValidationException::withMessages(['status' => 'Transisi tidak diizinkan.']);
            }
        }

        $updates = [
            'status' => $target,
            'diubah_oleh' => $user->id,
        ];

        if (array_key_exists('alasan_tidak_terlayani', $validated)) {
            $updates['alasan_tidak_terlayani'] = $validated['alasan_tidak_terlayani'];
        }
        if (array_key_exists('catatan', $validated)) {
            $updates['catatan'] = $validated['catatan'];
        }

        if ($target === StatusKunjungan::Dilayani && $kunjungan->waktu_dilayani === null) {
            $updates['waktu_dilayani'] = now();
        }
        if ($target === StatusKunjungan::Selesai && $kunjungan->waktu_selesai === null) {
            $updates['waktu_selesai'] = now();
            if ($kunjungan->waktu_dilayani === null) {
                $updates['waktu_dilayani'] = now();
            }
        }

        $kunjungan->forceFill($updates)->save();

        return redirect()->back()->with('success', 'Status diperbarui.');
    }

    protected function format(Kunjungan $k, bool $detailed = false): array
    {
        return [
            'id' => $k->id,
            'kode_publik' => $k->kode_publik,
            'keperluan' => $k->keperluan,
            'kategori' => $k->kategori,
            'ringkasan_ai' => $k->ringkasan_ai,
            'status' => $k->status->value,
            'klasifikasi_status' => $k->klasifikasi_status->value,
            'alasan_tidak_terlayani' => $k->alasan_tidak_terlayani,
            'catatan' => $k->catatan,
            'waktu_datang' => optional($k->waktu_datang)->toIso8601String(),
            'waktu_dilayani' => optional($k->waktu_dilayani)->toIso8601String(),
            'waktu_selesai' => optional($k->waktu_selesai)->toIso8601String(),
            'notifikasi_terkirim_pada' => optional($k->notifikasi_terkirim_pada)->toIso8601String(),
            'tamu' => $k->tamu ? [
                'id' => $k->tamu->id,
                'nama' => $k->tamu->nama,
                'kategori' => $k->tamu->kategori,
                'instansi' => $k->tamu->instansi,
                'alamat' => $k->tamu->alamat,
                'no_hp' => $k->tamu->no_hp,
            ] : null,
            'unit' => $k->unit ? [
                'id' => $k->unit->id,
                'kode' => $k->unit->kode,
                'nama' => $k->unit->nama,
                'lokasi_ruang' => $k->unit->lokasi_ruang,
            ] : null,
            'pegawai' => $k->pegawai ? [
                'id' => $k->pegawai->id,
                'nama' => $k->pegawai->nama,
                'jabatan' => $k->pegawai->jabatan,
            ] : null,
            'diubah_oleh' => $k->diubahOleh ? [
                'id' => $k->diubahOleh->id,
                'name' => $k->diubahOleh->name,
            ] : null,
        ];
    }
}
