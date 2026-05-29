<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Seeders ir sakārtoti pēc datu atkarībām: vispirms pamatdati, pēc tam saistītās tabulas.
        $this->call([
            UsersSeeder::class,
            MoviesSeeder::class,
            GenresSeeder::class,
            MoviesGenresUsageSeeder::class,
            HallsAndSeatsSeeder::class,
            ScreeningsSeeder::class,
            FeedbacksSeeder::class,
        ]);
    }
}
