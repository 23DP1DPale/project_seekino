<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'screening_id' => ['required', 'integer', 'exists:screenings,id'],
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'distinct'],
        ], [
            'screening_id.required' => 'Seanss nav norādīts.',
            'screening_id.integer' => 'Seansa identifikators nav derīgs.',
            'screening_id.exists' => 'Norādītais seanss neeksistē.',
            'seat_ids.required' => 'Nav izvēlēta neviena sēdvieta.',
            'seat_ids.array' => 'Sēdvietām jābūt saraksta formātā.',
            'seat_ids.min' => 'Nav izvēlēta neviena sēdvieta.',
            'seat_ids.*.integer' => 'Sēdvietas identifikators nav derīgs.',
            'seat_ids.*.distinct' => 'Sēdvietu sarakstā ir dublikāti.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $screeningId = (int) $request->input('screening_id');
        $seatIds = collect($request->input('seat_ids'))
            ->map(fn ($seatId): int => (int) $seatId)
            ->unique()
            ->values();

        try {
            $reservation = DB::transaction(function () use ($screeningId, $seatIds): array {
                $screening = DB::table('screenings')->where('id', $screeningId)->first();

                if (! $screening) {
                    abort(response()->json(['message' => 'Norādītais seanss neeksistē.'], 404));
                }

                $seats = DB::table('seats')
                    ->whereIn('id', $seatIds)
                    ->orderBy('row_number')
                    ->orderBy('seat_number')
                    ->get();

                if ($seats->count() !== $seatIds->count()) {
                    abort(response()->json(['message' => 'Viena vai vairākas izvēlētās sēdvietas neeksistē.'], 422));
                }

                $invalidHallSeat = $seats->first(fn ($seat) => (int) $seat->hall !== (int) $screening->hall);

                if ($invalidHallSeat) {
                    abort(response()->json([
                        'message' => 'Viena vai vairākas izvēlētās sēdvietas nepieder šī seansa zālei.',
                    ], 422));
                }

                $reservedSeatIds = DB::table('reservations_seats')
                    ->where('screening', $screeningId)
                    ->whereIn('seat', $seatIds)
                    ->lockForUpdate()
                    ->pluck('seat')
                    ->map(fn ($seatId): int => (int) $seatId);

                if ($reservedSeatIds->isNotEmpty()) {
                    abort(response()->json([
                        'message' => 'Viena vai vairākas izvēlētās sēdvietas šim seansam jau ir rezervētas.',
                        'reserved_seat_ids' => $reservedSeatIds->values(),
                    ], 409));
                }

                $userId = DB::table('users')->orderBy('id')->value('id') ?? 1;
                $now = now();
                $reservationId = DB::table('reservations')->insertGetId([
                    'reservation_date' => $now,
                    'payment_status' => 'pending',
                    'expiration_date' => $now->copy()->addMinutes(15),
                    'user' => $userId,
                    'screening' => $screeningId,
                ]);

                DB::table('reservations_seats')->insert(
                    $seatIds->map(fn (int $seatId): array => [
                        'reservation' => $reservationId,
                        'seat' => $seatId,
                        'screening' => $screeningId,
                    ])->all()
                );

                $selectedSeats = $seats
                    ->map(fn ($seat): array => [
                        'id' => (int) $seat->id,
                        'row_number' => (int) $seat->row_number,
                        'seat_number' => (int) $seat->seat_number,
                    ])
                    ->values();

                return [
                    'id' => $reservationId,
                    'seats' => $selectedSeats,
                    'total_price' => round((float) $screening->cost * $selectedSeats->count(), 2),
                ];
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Viena vai vairākas izvēlētās sēdvietas šim seansam jau ir rezervētas.',
                ], 409);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Rezervācija izveidota.',
            'reservation' => $reservation,
        ], 201);
    }
}
