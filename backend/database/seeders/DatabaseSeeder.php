<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('transactions')->truncate();
        DB::table('wallets')->truncate();
        DB::table('personal_access_tokens')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $user = User::create([
            'name'     => 'Demo User',
            'email'    => 'demo@fintech.com',
            'password' => Hash::make('password123'),
        ]);

        $user->wallet()->create(['balance' => '0.00']);
    }
}
