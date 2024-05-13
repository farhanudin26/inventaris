<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        user::create([
            'email' => 'farhan26@gmail.com',
            'username' => 'farhan26',
            'role' => 'admin',
            'password' => Hash::make('123'),
        ]);
    }
}
