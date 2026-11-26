<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['kode' => 'data', 'nama' => 'Unit Data', 'lokasi_ruang' => 'Ruang Tamu Unit Data'],
            ['kode' => 'umum', 'nama' => 'Unit Umum', 'lokasi_ruang' => 'Ruang Tamu Unit Umum'],
            ['kode' => 'kepegawaian', 'nama' => 'Unit Kepegawaian', 'lokasi_ruang' => 'Ruang Tamu Unit Kepegawaian'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['kode' => $unit['kode']], $unit + ['aktif' => true]);
        }
    }
}
