<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Lung',
            'email' => 'admin@lungxpert.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Doktor Lung',
            'email' => 'dokter@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'doctor',
        ]);
    }
}
