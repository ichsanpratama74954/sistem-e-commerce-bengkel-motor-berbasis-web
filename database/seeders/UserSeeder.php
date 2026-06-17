<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Rekayasa Perangkat Lunak',
            'email' => 'rekayasaperangkatlunak@gmail.com',
            'password' => Hash::make('rpl12345'),
            'role' => 'admin',
        ]);
    }
}