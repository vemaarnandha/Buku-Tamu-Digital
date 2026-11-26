<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => Role::Admin],
            ['name' => 'Resepsionis', 'email' => 'resepsionis@example.com', 'role' => Role::Resepsionis],
            ['name' => 'Pimpinan', 'email' => 'pimpinan@example.com', 'role' => Role::Pimpinan],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                    'aktif' => true,
                ],
            );
        }
    }
}
