<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoviesGenresUsageSeeder extends Seeder
{
    public function run(): void
    {
        $usages = [
            ['id' => 1, 'movie' => 1, 'genre' => 5, 'primary_genre' => true],
            ['id' => 2, 'movie' => 1, 'genre' => 6, 'primary_genre' => false],
            ['id' => 3, 'movie' => 2, 'genre' => 1, 'primary_genre' => true],
            ['id' => 4, 'movie' => 3, 'genre' => 2, 'primary_genre' => true],
            ['id' => 5, 'movie' => 3, 'genre' => 6, 'primary_genre' => false],
            ['id' => 6, 'movie' => 4, 'genre' => 4, 'primary_genre' => true],
            ['id' => 7, 'movie' => 4, 'genre' => 1, 'primary_genre' => false],
            ['id' => 8, 'movie' => 5, 'genre' => 3, 'primary_genre' => true],
            ['id' => 9, 'movie' => 6, 'genre' => 5, 'primary_genre' => true],
            ['id' => 10, 'movie' => 6, 'genre' => 1, 'primary_genre' => false],
        ];

        foreach ($usages as $usage) {
            DB::table('movies_genres_usage')->updateOrInsert(['id' => $usage['id']], $usage);
        }
    }
}
