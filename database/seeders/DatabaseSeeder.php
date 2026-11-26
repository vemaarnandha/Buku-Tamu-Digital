<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            JamLayananSeeder::class,
            UserSeeder::class,
            PegawaiSeeder::class,
            FaqSeeder::class,
            KunjunganSeeder::class,
        ]);
    }
}
