<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all();

        foreach ($units as $unit) {
            for ($i = 1; $i <= 2; $i++) {
                $email = "pegawai.{$unit->kode}.{$i}@example.com";

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "Pegawai {$unit->nama} {$i}",
                        'password' => Hash::make('password'),
                        'role' => Role::Pegawai,
                        'aktif' => true,
                    ],
                );

                Pegawai::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'unit_id' => $unit->id,
                        'nama' => $user->name,
                        'jabatan' => 'Staf',
                        'no_wa' => '628'.str_pad((string) ($unit->id * 10 + $i), 9, '0', STR_PAD_LEFT),
                        'email' => $email,
                        'aktif' => true,
                        'bisa_menerima_tamu' => true,
                    ],
                );
            }
        }
    }
}
