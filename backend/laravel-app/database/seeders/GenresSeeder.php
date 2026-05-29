<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenresSeeder extends Seeder
{
    public function run(): void
    {
        // Žanru ID tiek izmantoti movies_genres_usage tabulā.
        $genres = [
            [
                'id' => 1,
                'name' => 'Drāma',
                'description' => 'Uz tēliem balstīti stāsti ar emocionāliem konfliktiem un personīgām izvēlēm.',
            ],
            [
                'id' => 2,
                'name' => 'Asa sižeta',
                'description' => 'Ātra tempa filmas ar augstām likmēm, spriedzi un fizisku konfliktu.',
            ],
            [
                'id' => 3,
                'name' => 'Komēdija',
                'description' => 'Vieglas noskaņas stāsti, kuru centrā ir humors, situācijas un precīzs komiskais ritms.',
            ],
            [
                'id' => 4,
                'name' => 'Zinātniskā fantastika',
                'description' => 'Iztēles stāsti par tehnoloģijām, kosmosu un iespējamām nākotnēm.',
            ],
            [
                'id' => 5,
                'name' => 'Mistērija',
                'description' => 'Stāsti, kurus virza pavedieni, noslēpumi un izmeklēšana.',
            ],
            [
                'id' => 6,
                'name' => 'Trilleris',
                'description' => 'Spriedzes stāsti ar apdraudējumu, spiedienu un negaidītiem pavērsieniem.',
            ],
        ];

        // Pēc ID atjauno esošu žanru vai izveido to, ja tas vēl neeksistē.
        foreach ($genres as $genre) {
            DB::table('genres')->updateOrInsert(['id' => $genre['id']], $genre);
        }
    }
}
