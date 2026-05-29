<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Demo lietotājs parastajām rezervācijas un profila darbībām.
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'nickname' => 'janis',
                'email' => 'janis@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
        );

        // Demo administrators filmu, seansu un lietotāju pārvaldības skatam.
        DB::table('users')->updateOrInsert(
            ['id' => 2],
            [
                'nickname' => 'anna',
                'email' => 'anna@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );
    }
}
