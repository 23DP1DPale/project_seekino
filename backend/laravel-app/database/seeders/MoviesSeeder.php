<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoviesSeeder extends Seeder
{
    public function run(): void
    {
        $movies = [
            [
                'id' => 1,
                'name' => 'Midnight Signal',
                'length' => 118,
                'description' => 'A tense mystery about a radio host who receives a message from the future.',
                'director' => 'Laura Bennett',
                'age_restriction' => '16+',
            ],
            [
                'id' => 2,
                'name' => 'The Last Orchard',
                'length' => 104,
                'description' => 'A warm family drama set during one decisive summer harvest.',
                'director' => 'Markus Hale',
                'age_restriction' => '12+',
            ],
            [
                'id' => 3,
                'name' => 'Neon Run',
                'length' => 126,
                'description' => 'A fast action thriller through a city that never sleeps.',
                'director' => 'Iris Cole',
                'age_restriction' => '13+',
            ],
            [
                'id' => 4,
                'name' => 'Quiet Planet',
                'length' => 139,
                'description' => 'A science fiction journey to a silent world at the edge of known space.',
                'director' => 'Theo Larsen',
                'age_restriction' => '13+',
            ],
            [
                'id' => 5,
                'name' => 'Laughing Lines',
                'length' => 96,
                'description' => 'A comedy about two stage rivals forced to write a show together.',
                'director' => 'Mia Roberts',
                'age_restriction' => '7+',
            ],
            [
                'id' => 6,
                'name' => 'Winter Evidence',
                'length' => 112,
                'description' => 'A detective follows a cold trail after new evidence appears years later.',
                'director' => 'Nora Finch',
                'age_restriction' => '16+',
            ],
        ];

        foreach ($movies as $movie) {
            DB::table('movies')->updateOrInsert(['id' => $movie['id']], $movie);
        }
    }
}
