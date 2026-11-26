<?php

namespace Tests\Feature\Ai;

use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Services\PenugasanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenugasanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_memilih_pegawai_dengan_beban_aktif_paling_sedikit(): void
    {
        $unit = Unit::factory()->create();
        $sibuk = Pegawai::factory()->create(['unit_id' => $unit->id]);
        $longgar = Pegawai::factory()->create(['unit_id' => $unit->id]);

        Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $sibuk->id,
            'status' => StatusKunjungan::Menunggu,
        ]);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($longgar->id, $terpilih->id);
    }

    public function test_memperhitungkan_status_dilayani_sebagai_beban_aktif(): void
    {
        $unit = Unit::factory()->create();
        $sibuk = Pegawai::factory()->create(['unit_id' => $unit->id]);
        $longgar = Pegawai::factory()->create(['unit_id' => $unit->id]);

        Kunjungan::factory()->create([
            'unit_id' => $unit->id,
            'pegawai_id' => $sibuk->id,
            'status' => StatusKunjungan::Dilayani,
        ]);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($longgar->id, $terpilih->id);
    }

    public function test_memilih_pegawai_dengan_penugasan_terakhir_paling_lama(): void
    {
        $unit = Unit::factory()->create();
        $lama = Pegawai::factory()->create([
            'unit_id' => $unit->id,
            'terakhir_ditugaskan_pada' => now()->subDays(2),
        ]);
        Pegawai::factory()->create([
            'unit_id' => $unit->id,
            'terakhir_ditugaskan_pada' => now()->subHour(),
        ]);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($lama->id, $terpilih->id);
    }

    public function test_pegawai_belum_pernah_ditugaskan_diprioritaskan(): void
    {
        $unit = Unit::factory()->create();
        $belumPernah = Pegawai::factory()->create([
            'unit_id' => $unit->id,
            'terakhir_ditugaskan_pada' => null,
        ]);
        Pegawai::factory()->create([
            'unit_id' => $unit->id,
            'terakhir_ditugaskan_pada' => now()->subHour(),
        ]);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($belumPernah->id, $terpilih->id);
    }

    public function test_memilih_id_paling_kecil_jika_seri(): void
    {
        $unit = Unit::factory()->create();
        $pertama = Pegawai::factory()->create(['unit_id' => $unit->id]);
        $kedua = Pegawai::factory()->create(['unit_id' => $unit->id]);

        $this->assertLessThan($kedua->id, $pertama->id);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($pertama->id, $terpilih->id);
    }

    public function test_mengabaikan_pegawai_tidak_aktif_atau_tidak_bisa_menerima_tamu(): void
    {
        $unit = Unit::factory()->create();
        Pegawai::factory()->create(['unit_id' => $unit->id, 'aktif' => false]);
        Pegawai::factory()->create(['unit_id' => $unit->id, 'bisa_menerima_tamu' => false]);
        Pegawai::factory()->create(['unit_id' => Unit::factory()->create()->id]);

        $valid = Pegawai::factory()->create(['unit_id' => $unit->id]);

        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($valid->id, $terpilih->id);
    }

    public function test_mengembalikan_null_jika_tidak_ada_pegawai(): void
    {
        $unit = Unit::factory()->create();
        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertNull($terpilih);
        $this->assertNull($kunjungan->fresh()->pegawai_id);
    }

    public function test_memperbarui_terakhir_ditugaskan_dan_pegawai_kunjungan(): void
    {
        $unit = Unit::factory()->create();
        $pegawai = Pegawai::factory()->create(['unit_id' => $unit->id, 'terakhir_ditugaskan_pada' => null]);
        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $terpilih = app(PenugasanService::class)->tugaskan($kunjungan, $unit);

        $this->assertSame($pegawai->id, $terpilih->id);
        $this->assertNotNull($pegawai->fresh()->terakhir_ditugaskan_pada);
        $this->assertSame($pegawai->id, $kunjungan->fresh()->pegawai_id);
    }
}
