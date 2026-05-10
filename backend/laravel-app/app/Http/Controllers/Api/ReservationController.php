<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function profileReservations(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $user) {
            return $this->unauthenticatedResponse();
        }

        $rows = DB::table('reservations')
            ->join('screenings', 'reservations.screening', '=', 'screenings.id')
            ->join('movies', 'screenings.movie', '=', 'movies.id')
            ->join('halls', 'screenings.hall', '=', 'halls.id')
            ->leftJoin('reservations_seats', 'reservations.id', '=', 'reservations_seats.reservation')
            ->leftJoin('seats', 'reservations_seats.seat', '=', 'seats.id')
            ->where('reservations.user', $user->id)
            ->select([
                'reservations.id as reservation_id',
                'reservations.payment_status',
                'reservations.reservation_date',
                'reservations.expiration_date',
                'screenings.screening_date',
                'screenings.screening_time',
                'screenings.cost as screening_cost',
                'movies.name as movie_name',
                'halls.name as hall_name',
                'seats.id as seat_id',
                'seats.row_number',
                'seats.seat_number',
            ])
            ->orderByDesc('reservations.reservation_date')
            ->orderBy('seats.row_number')
            ->orderBy('seats.seat_number')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'ziņa' => 'Tev vēl nav nevienas rezervācijas.',
                'reservations' => [],
            ]);
        }

        $reservations = $rows
            ->groupBy('reservation_id')
            ->map(function ($reservationRows): array {
                $first = $reservationRows->first();
                $seats = $reservationRows
                    ->filter(fn ($row): bool => $row->seat_id !== null)
                    ->map(fn ($row): array => [
                        'row_number' => (int) $row->row_number,
                        'seat_number' => (int) $row->seat_number,
                    ])
                    ->values();

                return [
                    'id' => (int) $first->reservation_id,
                    'payment_status' => $first->payment_status,
                    'reservation_date' => $first->reservation_date,
                    'expiration_date' => $first->expiration_date,
                    'movie' => [
                        'name' => $first->movie_name,
                        'title' => $first->movie_name,
                    ],
                    'screening_date' => $first->screening_date,
                    'screening_time' => $first->screening_time,
                    'hall' => [
                        'name' => $first->hall_name,
                    ],
                    'seats' => $seats,
                    'total_price' => round((float) $first->screening_cost * $seats->count(), 2),
                ];
            })
            ->values();

        return response()->json([
            'ziņa' => 'Rezervācijas iegūtas veiksmīgi.',
            'reservations' => $reservations,
        ]);
    }

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
        $authenticatedUser = $this->authenticatedUser($request);

        if ($request->bearerToken() && ! $authenticatedUser) {
            return $this->unauthenticatedResponse();
        }

        try {
            $reservation = DB::transaction(function () use ($screeningId, $seatIds, $authenticatedUser): array {
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

                $userId = $authenticatedUser?->id ?? DB::table('users')->orderBy('id')->value('id') ?? 1;
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

    private function authenticatedUser(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            return null;
        }

        $apiToken = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $apiToken) {
            return null;
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        return $apiToken->user;
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'ziņa' => 'Autentifikācijas tokens nav derīgs vai nav norādīts.',
        ], 401);
    }
}
