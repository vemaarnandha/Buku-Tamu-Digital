<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Models\JamLayanan;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class JamLayananSeeder extends Seeder
{
    public function run(): void
    {
        $hariKerja = [Hari::Senin, Hari::Selasa, Hari::Rabu, Hari::Kamis, Hari::Jumat];

        foreach (Unit::all() as $unit) {
            foreach ($hariKerja as $hari) {
                JamLayanan::updateOrCreate(
                    ['unit_id' => $unit->id, 'hari' => $hari->value, 'jam_mulai' => '08:00:00'],
                    ['jam_selesai' => '12:00:00', 'aktif' => true],
                );
                JamLayanan::updateOrCreate(
                    ['unit_id' => $unit->id, 'hari' => $hari->value, 'jam_mulai' => '13:00:00'],
                    ['jam_selesai' => '16:00:00', 'aktif' => true],
                );
            }
        }
    }
}
