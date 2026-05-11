<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminMovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $movies = Movie::query()
            ->orderBy('id')
            ->get()
            ->map(fn (Movie $movie): array => $this->movieResponse($movie))
            ->values();

        return response()->json([
            'ziņa' => 'Filmas iegūtas veiksmīgi.',
            'movies' => $movies,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $movie = Movie::create($validator->validated());

        return response()->json([
            'ziņa' => 'Filma veiksmīgi izveidota.',
            'movie' => $this->movieResponse($movie),
        ], 201);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $movie->update($validator->validated());

        return response()->json([
            'ziņa' => 'Filma veiksmīgi atjaunota.',
            'movie' => $this->movieResponse($movie->refresh()),
        ]);
    }

    public function destroy(Request $request, Movie $movie): JsonResponse
    {
        $admin = $this->adminUser($request);

        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        DB::transaction(function () use ($movie): void {
            $screeningIds = DB::table('screenings')
                ->where('movie', $movie->id)
                ->pluck('id');
            $reservationIds = DB::table('reservations')
                ->whereIn('screening', $screeningIds)
                ->pluck('id');

            DB::table('tickets')
                ->whereIn('reservation', $reservationIds)
                ->orWhereIn('screening', $screeningIds)
                ->delete();
            DB::table('reservations_seats')
                ->whereIn('reservation', $reservationIds)
                ->orWhereIn('screening', $screeningIds)
                ->delete();
            DB::table('reservations')
                ->whereIn('id', $reservationIds)
                ->delete();
            DB::table('screenings')
                ->whereIn('id', $screeningIds)
                ->delete();

            $movie->delete();
        });

        return response()->json([
            'ziņa' => 'Filma veiksmīgi dzēsta.',
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
                'ziņa' => 'Tev nav tiesību pārvaldīt filmas.',
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
            'name' => ['required', 'string', 'max:100'],
            'length' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'director' => ['required', 'string', 'max:100'],
            'age_restriction' => ['required', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Filmas nosaukums ir obligāts.',
            'name.string' => 'Filmas nosaukumam jābūt tekstam.',
            'name.max' => 'Filmas nosaukums nedrīkst pārsniegt 100 rakstzīmes.',
            'length.required' => 'Filmas garums ir obligāts.',
            'length.integer' => 'Filmas garumam jābūt veselam skaitlim.',
            'length.min' => 'Filmas garumam jābūt vismaz 1 minūtei.',
            'description.required' => 'Filmas apraksts ir obligāts.',
            'description.string' => 'Filmas aprakstam jābūt tekstam.',
            'director.required' => 'Režisors ir obligāts.',
            'director.string' => 'Režisoram jābūt tekstam.',
            'director.max' => 'Režisora vārds nedrīkst pārsniegt 100 rakstzīmes.',
            'age_restriction.required' => 'Vecuma ierobežojums ir obligāts.',
            'age_restriction.string' => 'Vecuma ierobežojumam jābūt tekstam.',
            'age_restriction.max' => 'Vecuma ierobežojums nedrīkst pārsniegt 10 rakstzīmes.',
        ];
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

    private function movieResponse(Movie $movie): array
    {
        return [
            'id' => $movie->id,
            'name' => $movie->name,
            'title' => $movie->name,
            'length' => $movie->length,
            'duration' => $movie->length,
            'description' => $movie->description,
            'director' => $movie->director,
            'age_restriction' => $movie->age_restriction,
            'ageRating' => $movie->age_restriction,
        ];
    }
}
