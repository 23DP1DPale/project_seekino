<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Hall;
use App\Models\Screening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminScreeningController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $screenings = Screening::query()
            ->with(['movieRecord', 'cinemaHall'])
            ->orderBy('screening_date')
            ->orderBy('screening_time')
            ->get();

        if (! $request->boolean('include_past')) {
            $screenings = $screenings
                ->filter(fn (Screening $screening): bool => $this->isFutureScreening($screening))
                ->values();
        }

        $screenings = $screenings
            ->map(fn (Screening $screening): array => $this->screeningResponse($screening))
            ->values();

        return response()->json([
            'ziņa' => 'Seansi iegūti veiksmīgi.',
            'screenings' => $screenings,
        ]);
    }

    public function halls(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $halls = Hall::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Hall $hall): array => [
                'id' => $hall->id,
                'name' => $hall->name,
                'seat_amount' => $hall->seat_amount,
            ])
            ->values();

        return response()->json([
            'ziņa' => 'Zāles iegūtas veiksmīgi.',
            'halls' => $halls,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        $this->addScheduleValidation($validator);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $screening = Screening::create($validator->validated());
        $screening->load(['movieRecord', 'cinemaHall']);

        return response()->json([
            'ziņa' => 'Seanss veiksmīgi izveidots.',
            'screening' => $this->screeningResponse($screening),
        ], 201);
    }

    public function update(Request $request, Screening $screening): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        $this->addScheduleValidation($validator, $screening);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $screening->update($validator->validated());
        $screening->refresh()->load(['movieRecord', 'cinemaHall']);

        return response()->json([
            'ziņa' => 'Seanss veiksmīgi atjaunots.',
            'screening' => $this->screeningResponse($screening),
        ]);
    }

    public function destroy(Request $request, Screening $screening): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        DB::transaction(function () use ($screening): void {
            $reservationIds = DB::table('reservations')
                ->where('screening', $screening->id)
                ->pluck('id');

            DB::table('tickets')
                ->where('screening', $screening->id)
                ->orWhereIn('reservation', $reservationIds)
                ->delete();
            DB::table('reservations_seats')
                ->where('screening', $screening->id)
                ->orWhereIn('reservation', $reservationIds)
                ->delete();
            DB::table('reservations')
                ->whereIn('id', $reservationIds)
                ->delete();

            $screening->delete();
        });

        return response()->json([
            'ziņa' => 'Seanss veiksmīgi dzēsts.',
        ]);
    }

    private function adminUser(Request $request): User|JsonResponse
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            return $this->unauthenticatedResponse();
        }

        $apiToken = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $apiToken || ! $apiToken->user) {
            return $this->unauthenticatedResponse();
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        if ($apiToken->user->role !== 'admin') {
            return response()->json([
                'ziņa' => 'Tev nav tiesību pārvaldīt seansus.',
            ], 403);
        }

        return $apiToken->user;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'movie' => ['required', 'integer', 'exists:movies,id'],
            'hall' => ['required', 'integer', 'exists:halls,id'],
            'screening_date' => ['required', 'date'],
            'screening_time' => ['required', 'date_format:H:i'],
            'cost' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'movie.required' => 'Filma ir obligāta.',
            'movie.integer' => 'Filmas identifikatoram jābūt skaitlim.',
            'movie.exists' => 'Norādītā filma neeksistē.',
            'hall.required' => 'Zāle ir obligāta.',
            'hall.integer' => 'Zāles identifikatoram jābūt skaitlim.',
            'hall.exists' => 'Norādītā zāle neeksistē.',
            'screening_date.required' => 'Seansa datums ir obligāts.',
            'screening_date.date' => 'Seansa datumam jābūt derīgam datumam.',
            'screening_time.required' => 'Seansa laiks ir obligāts.',
            'screening_time.date_format' => 'Seansa laikam jābūt formātā HH:MM.',
            'cost.required' => 'Cena ir obligāta.',
            'cost.numeric' => 'Cenai jābūt skaitlim.',
            'cost.min' => 'Cenai jābūt pozitīvam skaitlim.',
        ];
    }

    private function addScheduleValidation($validator, ?Screening $currentScreening = null): void
    {
        $validator->after(function ($validator) use ($currentScreening): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $query = Screening::query()
                ->where('screening_date', request('screening_date'))
                ->where('screening_time', request('screening_time'))
                ->where('hall', request('hall'));

            if ($currentScreening) {
                $query->where('id', '!=', $currentScreening->id);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'screening_time',
                    'Šajā zālē norādītajā datumā un laikā jau ir seanss.'
                );
            }
        });
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'ziņa' => 'Ievadītie dati nav derīgi.',
            'kļūdas' => $errors,
        ], 422);
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'ziņa' => 'Autentifikācijas tokens nav derīgs vai nav norādīts.',
        ], 401);
    }

    private function isFutureScreening(Screening $screening): bool
    {
        return $this->screeningDateTime($screening)?->greaterThan($this->currentDateTime()) ?? false;
    }

    private function screeningDateTime(Screening $screening): ?Carbon
    {
        if (! $screening->screening_date || ! $screening->screening_time) {
            return null;
        }

        return Carbon::parse(
            "{$screening->screening_date->format('Y-m-d')} {$screening->screening_time}",
            config('app.timezone')
        );
    }

    private function currentDateTime(): Carbon
    {
        return Carbon::now(config('app.timezone'));
    }

    private function screeningResponse(Screening $screening): array
    {
        $movie = $screening->relationLoaded('movieRecord') ? $screening->movieRecord : null;
        $hall = $screening->relationLoaded('cinemaHall') ? $screening->cinemaHall : null;

        return [
            'id' => $screening->id,
            'movie' => $screening->movie,
            'hall' => $screening->hall,
            'screening_date' => $screening->screening_date?->format('Y-m-d'),
            'screening_time' => substr((string) $screening->screening_time, 0, 5),
            'cost' => (float) $screening->cost,
            'movie_record' => $movie ? [
                'id' => $movie->id,
                'name' => $movie->name,
                'title' => $movie->name,
            ] : null,
            'hall_record' => $hall ? [
                'id' => $hall->id,
                'name' => $hall->name,
                'seat_amount' => $hall->seat_amount,
            ] : null,
        ];
    }
}
