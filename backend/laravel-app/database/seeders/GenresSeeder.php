<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenresSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            [
                'id' => 1,
                'name' => 'Drama',
                'description' => 'Character focused stories with emotional conflicts.',
            ],
            [
                'id' => 2,
                'name' => 'Action',
                'description' => 'Fast paced films with high stakes and physical conflict.',
            ],
            [
                'id' => 3,
                'name' => 'Comedy',
                'description' => 'Light stories built around humor and timing.',
            ],
            [
                'id' => 4,
                'name' => 'Sci-Fi',
                'description' => 'Speculative stories about technology, space, and possible futures.',
            ],
            [
                'id' => 5,
                'name' => 'Mystery',
                'description' => 'Stories driven by clues, secrets, and investigation.',
            ],
            [
                'id' => 6,
                'name' => 'Thriller',
                'description' => 'Suspense driven stories with danger and pressure.',
            ],
        ];

        foreach ($genres as $genre) {
            DB::table('genres')->updateOrInsert(['id' => $genre['id']], $genre);
        }
    }
}
