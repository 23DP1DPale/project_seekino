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
                'description' => 'Spriedzes pilna mistērija par radio vadītāju, kurš saņem ziņojumu no nākotnes.',
                'director' => 'Laura Bennett',
                'age_restriction' => '16+',
            ],
            [
                'id' => 2,
                'name' => 'The Last Orchard',
                'length' => 104,
                'description' => 'Sirsnīga ģimenes drāma par izšķirošu vasaras ražu un izvēlēm, kas maina tuvinieku dzīvi.',
                'director' => 'Markus Hale',
                'age_restriction' => '12+',
            ],
            [
                'id' => 3,
                'name' => 'Neon Run',
                'length' => 126,
                'description' => 'Dinamisks asa sižeta trilleris cauri pilsētai, kas nekad neguļ.',
                'director' => 'Iris Cole',
                'age_restriction' => '13+',
            ],
            [
                'id' => 4,
                'name' => 'Quiet Planet',
                'length' => 139,
                'description' => 'Zinātniskās fantastikas ceļojums uz klusu pasauli zināmā kosmosa pašā malā.',
                'director' => 'Theo Larsen',
                'age_restriction' => '13+',
            ],
            [
                'id' => 5,
                'name' => 'Laughing Lines',
                'length' => 96,
                'description' => 'Komēdija par diviem skatuves sāncenšiem, kuriem jārada kopīga izrāde.',
                'director' => 'Mia Roberts',
                'age_restriction' => '7+',
            ],
            [
                'id' => 6,
                'name' => 'Winter Evidence',
                'length' => 112,
                'description' => 'Detektīvs atsāk sen aizmirstu lietu, kad pēc vairākiem gadiem parādās jauni pierādījumi.',
                'director' => 'Nora Finch',
                'age_restriction' => '16+',
            ],
        ];

        foreach ($movies as $movie) {
            DB::table('movies')->updateOrInsert(['id' => $movie['id']], $movie);
        }
    }
}
