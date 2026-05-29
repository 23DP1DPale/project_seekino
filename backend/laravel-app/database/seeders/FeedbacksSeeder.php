<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbacksSeeder extends Seeder
{
    public function run(): void
    {
        // Atsauksmes izmanto iepriekš izveidoto filmu un lietotāju ID.
        $feedbacks = [
            [
                'id' => 1,
                'rating' => '4.5',
                'comment' => 'Spēcīga atmosfēra un gudrs noslēgums.',
                'created_at' => '2026-05-03 11:20:00',
                'movie' => 1,
                'user' => 1,
            ],
            [
                'id' => 2,
                'rating' => '4.0',
                'comment' => 'Lieliskas asa sižeta ainas un pārliecinošs ritms.',
                'created_at' => '2026-05-03 22:10:00',
                'movie' => 3,
                'user' => 2,
            ],
            [
                'id' => 3,
                'rating' => '5.0',
                'comment' => 'Skaista vizuālā valoda un atmiņā paliekošs stāsts.',
                'created_at' => '2026-05-05 09:45:00',
                'movie' => 4,
                'user' => 1,
            ],
            [
                'id' => 4,
                'rating' => '3.5',
                'comment' => 'Smieklīgi brīži, lai gan dažas ainas šķita lēnākas.',
                'created_at' => '2026-05-05 18:30:00',
                'movie' => 5,
                'user' => 2,
            ],
        ];

        // updateOrInsert ļauj palaist seederi vairākas reizes bez dublikātiem.
        foreach ($feedbacks as $feedback) {
            DB::table('feedbacks')->updateOrInsert(['id' => $feedback['id']], $feedback);
        }
    }
}
