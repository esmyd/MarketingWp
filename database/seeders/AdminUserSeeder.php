<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'gosorio@siglotecnologico.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }
}
