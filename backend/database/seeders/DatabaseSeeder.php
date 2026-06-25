<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'     => 'Demo User',
            'email'    => 'demo@fintech.com',
            'password' => Hash::make('password123'),
        ]);

        $user->wallet()->create(['balance' => '0.00']);
    }
}
