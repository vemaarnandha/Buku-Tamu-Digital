<?php

namespace Tests\Feature;

use App\Models\Kunjungan;
use App\Models\Rating;
use App\Models\Tamu;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_no_hp_unique(): void
    {
        Tamu::factory()->create(['no_hp' => '628111111111']);

        $this->expectException(QueryException::class);
        Tamu::factory()->create(['no_hp' => '628111111111']);
    }

    public function test_rating_kunjungan_unique(): void
    {
        $kunjungan = Kunjungan::factory()->create();

        Rating::factory()->create(['kunjungan_id' => $kunjungan->id]);

        $this->expectException(QueryException::class);
        Rating::factory()->create(['kunjungan_id' => $kunjungan->id]);
    }

    public function test_kunjungan_relations(): void
    {
        $unit = Unit::factory()->create();
        $kunjungan = Kunjungan::factory()->create(['unit_id' => $unit->id]);

        $this->assertSame($unit->id, $kunjungan->unit->id);
        $this->assertNotNull($kunjungan->tamu);
    }
}
