<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HallsAndSeatsSeeder extends Seeder
{
    public function run(): void
    {
        $halls = [
            ['id' => 1, 'name' => 'Zāle A', 'seat_amount' => 40],
            ['id' => 2, 'name' => 'Zāle B', 'seat_amount' => 24],
        ];

        foreach ($halls as $hall) {
            DB::table('halls')->updateOrInsert(['id' => $hall['id']], $hall);
        }

        $seatId = 1;

        $this->seedSeats($seatId, hall: 1, rows: 5, seatsPerRow: 8);
        $this->seedSeats($seatId, hall: 2, rows: 4, seatsPerRow: 6);
    }

    private function seedSeats(int &$seatId, int $hall, int $rows, int $seatsPerRow): void
    {
        for ($row = 1; $row <= $rows; $row++) {
            for ($seat = 1; $seat <= $seatsPerRow; $seat++) {
                DB::table('seats')->updateOrInsert(
                    ['id' => $seatId],
                    [
                        'row_number' => $row,
                        'seat_number' => $seat,
                        'hall' => $hall,
                    ],
                );

                $seatId++;
            }
        }
    }
}
