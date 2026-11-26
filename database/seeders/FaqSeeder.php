<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $global = [
            ['pertanyaan' => 'Jam layanan kantor?', 'jawaban' => 'Senin-Jumat pukul 08.00-16.00 WIB.'],
            ['pertanyaan' => 'Di mana lokasi kantor?', 'jawaban' => 'Lokasi kantor sesuai alamat instansi.'],
        ];

        foreach ($global as $faq) {
            Faq::updateOrCreate(['pertanyaan' => $faq['pertanyaan'], 'unit_id' => null], $faq + ['aktif' => true]);
        }

        foreach (Unit::all() as $unit) {
            Faq::updateOrCreate(
                ['unit_id' => $unit->id, 'pertanyaan' => "Apa layanan {$unit->nama}?"],
                ['jawaban' => "Layanan yang disediakan oleh {$unit->nama}.", 'aktif' => true],
            );
        }
    }
}
