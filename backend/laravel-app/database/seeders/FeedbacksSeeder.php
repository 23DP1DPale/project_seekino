<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbacksSeeder extends Seeder
{
    public function run(): void
    {
        $feedbacks = [
            [
                'id' => 1,
                'rating' => '4.5',
                'comment' => 'Strong atmosphere and a clever ending.',
                'created_at' => '2026-05-03 11:20:00',
                'movie' => 1,
                'user' => 1,
            ],
            [
                'id' => 2,
                'rating' => '4.0',
                'comment' => 'Great action scenes and solid pacing.',
                'created_at' => '2026-05-03 22:10:00',
                'movie' => 3,
                'user' => 2,
            ],
            [
                'id' => 3,
                'rating' => '5.0',
                'comment' => 'Beautiful visuals and a memorable story.',
                'created_at' => '2026-05-05 09:45:00',
                'movie' => 4,
                'user' => 1,
            ],
            [
                'id' => 4,
                'rating' => '3.5',
                'comment' => 'Funny moments, though a few scenes felt slow.',
                'created_at' => '2026-05-05 18:30:00',
                'movie' => 5,
                'user' => 2,
            ],
        ];

        foreach ($feedbacks as $feedback) {
            DB::table('feedbacks')->updateOrInsert(['id' => $feedback['id']], $feedback);
        }
    }
}
