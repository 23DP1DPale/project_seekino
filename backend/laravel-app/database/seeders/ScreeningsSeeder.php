<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScreeningsSeeder extends Seeder
{
    public function run(): void
    {
        // Datumi tiek rēķināti no šodienas, lai demo seansi vienmēr būtu nākotnē.
        $screenings = [
            ['id' => 1, 'screening_date' => now()->addDays(1)->toDateString(), 'screening_time' => '18:00:00', 'cost' => '8.50', 'hall' => 1, 'movie' => 1],
            ['id' => 2, 'screening_date' => now()->addDays(1)->toDateString(), 'screening_time' => '20:30:00', 'cost' => '9.00', 'hall' => 2, 'movie' => 3],
            ['id' => 3, 'screening_date' => now()->addDays(2)->toDateString(), 'screening_time' => '16:00:00', 'cost' => '7.50', 'hall' => 1, 'movie' => 5],
            ['id' => 4, 'screening_date' => now()->addDays(2)->toDateString(), 'screening_time' => '19:15:00', 'cost' => '8.00', 'hall' => 2, 'movie' => 2],
            ['id' => 5, 'screening_date' => now()->addDays(3)->toDateString(), 'screening_time' => '18:45:00', 'cost' => '9.50', 'hall' => 1, 'movie' => 4],
            ['id' => 6, 'screening_date' => now()->addDays(3)->toDateString(), 'screening_time' => '21:00:00', 'cost' => '8.75', 'hall' => 2, 'movie' => 6],
            ['id' => 7, 'screening_date' => now()->addDays(4)->toDateString(), 'screening_time' => '17:30:00', 'cost' => '7.00', 'hall' => 1, 'movie' => 2],
            ['id' => 8, 'screening_date' => now()->addDays(4)->toDateString(), 'screening_time' => '20:00:00', 'cost' => '9.25', 'hall' => 2, 'movie' => 1],
        ];

        foreach ($screenings as $screening) {
            DB::table('screenings')->updateOrInsert(['id' => $screening['id']], $screening);
        }
    }
}
