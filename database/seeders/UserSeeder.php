<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Admin
        User::create([
            'name'     => 'Administrator eKRS',
            'email'    => 'admin@ekrs.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // 2. Seed Dosen
        User::create([
            'name'     => 'Dr. Budi Santoso',
            'email'    => 'dosen@ekrs.com',
            'password' => Hash::make('password'),
            'role'     => 'dosen',
        ]);

        // 3. Seed Mahasiswa
        User::create([
            'name'     => 'Siswa Teladan',
            'email'    => 'mahasiswa@ekrs.com',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
        ]);
    }
}
