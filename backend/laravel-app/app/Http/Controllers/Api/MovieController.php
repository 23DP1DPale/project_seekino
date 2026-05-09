<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Screening;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'rating', 'price'], true)) {
            $sort = 'name';
        }

        $movies = Movie::query()
            ->with([
                'genres',
                'screenings.cinemaHall',
            ])
            ->withAvg('feedbacks as rating', 'rating')
            ->withMin('screenings as price', 'cost')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->query('search');

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('director', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('genre'), function ($query) use ($request): void {
                $genre = $request->query('genre');

                $query->whereHas('genres', function ($query) use ($genre): void {
                    if (is_numeric($genre)) {
                        $query->where('genres.id', (int) $genre);

                        return;
                    }

                    $query->where('genres.name', $genre);
                });
            })
            ->orderBy($sort, $direction)
            ->get();

        return response()->json(
            $movies->map(fn (Movie $movie): array => $this->movieResponse($movie))->values()
        );
    }

    public function show(Movie $movie): JsonResponse
    {
        $movie->load([
            'genres',
            'screenings.cinemaHall',
            'feedbacks',
        ])->loadAvg('feedbacks as rating', 'rating')
            ->loadMin('screenings as price', 'cost');

        return response()->json($this->movieResponse($movie, includeDetails: true));
    }

    public function screenings(): JsonResponse
    {
        $screenings = Screening::query()
            ->with([
                'cinemaHall',
                'movieRecord.genres',
                'movieRecord.screenings.cinemaHall',
                'movieRecord.feedbacks',
            ])
            ->orderBy('screening_date')
            ->orderBy('screening_time')
            ->get();

        return response()->json(
            $screenings->map(fn (Screening $screening): array => $this->screeningResponse($screening))->values()
        );
    }

    public function showScreening(Screening $screening): JsonResponse
    {
        $screening->load([
            'cinemaHall',
            'movieRecord',
        ]);

        $hall = $this->hallFor($screening);
        $movie = $this->movieFor($screening);
        $reservedSeatIds = DB::table('reservations_seats')
            ->where('screening', $screening->id)
            ->pluck('seat')
            ->map(fn ($seat): int => (int) $seat)
            ->all();

        $seats = $hall
            ? DB::table('seats')
                ->where('hall', $hall->id)
                ->orderBy('row_number')
                ->orderBy('seat_number')
                ->get()
                ->map(fn ($seat): array => [
                    'id' => (int) $seat->id,
                    'row_number' => (int) $seat->row_number,
                    'seat_number' => (int) $seat->seat_number,
                    'is_reserved' => in_array((int) $seat->id, $reservedSeatIds, true),
                ])
                ->values()
            : collect();

        return response()->json([
            'id' => $screening->id,
            'date' => $screening->screening_date?->format('Y-m-d'),
            'time' => $screening->screening_time,
            'price' => (float) $screening->cost,
            'movie' => $movie ? [
                'id' => $movie->id,
                'name' => $movie->name,
                'title' => $movie->name,
                'poster' => $this->posterFor($movie->id),
            ] : null,
            'hall' => $hall ? [
                'id' => $hall->id,
                'name' => $hall->name,
            ] : null,
            'seats' => $seats,
        ]);
    }

    private function movieResponse(Movie $movie, bool $includeDetails = false): array
    {
        $nextScreening = $this->nextScreening($movie->screenings);
        $genres = $movie->genres
            ->map(fn ($genre): array => [
                'id' => $genre->id,
                'name' => $genre->name,
                'description' => $genre->description,
                'primary' => (bool) $genre->pivot?->primary_genre,
            ])
            ->values();
        $primaryGenre = $genres->firstWhere('primary', true) ?? $genres->first();
        $poster = $this->posterFor($movie->id);
        $rating = $this->averageRating($movie);
        $price = $this->lowestPrice($movie);

        $response = [
            'id' => $movie->id,
            'name' => $movie->name,
            'title' => $movie->name,
            'director' => $movie->director,
            'length' => $movie->length,
            'duration' => $movie->length,
            'description' => $movie->description,
            'genres' => $genres,
            'genre' => $primaryGenre['name'] ?? null,
            'age_restriction' => $movie->age_restriction,
            'ageRating' => $movie->age_restriction,
            'average_rating' => $rating,
            'rating' => $rating,
            'lowest_price' => $price,
            'price' => $price,
            'minPrice' => $price,
            'next_screening' => $nextScreening,
            'nextSession' => $this->nextSessionLabel($nextScreening),
            'hall' => $nextScreening['hall'] ?? null,
            'image' => $poster,
            'poster' => $poster,
            'formats' => ['2D'],
        ];

        if ($includeDetails) {
            $response['screenings'] = $movie->screenings
                ->sortBy(fn (Screening $screening): string => "{$screening->screening_date} {$screening->screening_time}")
                ->map(fn (Screening $screening): array => $this->screeningResponse($screening, includeMovie: false))
                ->values();

            $response['feedbacks'] = $movie->feedbacks
                ->map(fn ($feedback): array => [
                    'id' => $feedback->id,
                    'rating' => (float) $feedback->rating,
                    'comment' => $feedback->comment,
                    'created_at' => $feedback->created_at?->toISOString(),
                ])
                ->values();
        }

        return $response;
    }

    private function screeningResponse(Screening $screening, bool $includeMovie = true): array
    {
        $hall = $this->hallFor($screening);

        $response = [
            'id' => $screening->id,
            'screening_date' => $screening->screening_date?->format('Y-m-d'),
            'screening_time' => $screening->screening_time,
            'cost' => (float) $screening->cost,
            'price' => (float) $screening->cost,
            'hall' => $hall ? [
                'id' => $hall->id,
                'name' => $hall->name,
                'seat_amount' => $hall->seat_amount,
            ] : null,
        ];

        if ($includeMovie) {
            $movie = $this->movieFor($screening);

            $response['movie'] = $movie ? $this->movieResponse($movie) : null;
        }

        return $response;
    }

    private function nextScreening(EloquentCollection|Collection $screenings): ?array
    {
        $sortedScreenings = $screenings
            ->sortBy(fn (Screening $screening): string => "{$screening->screening_date} {$screening->screening_time}")
            ->values();

        $now = now();
        $screening = $sortedScreenings->first(function (Screening $screening) use ($now): bool {
            return $this->screeningDateTime($screening)?->greaterThanOrEqualTo($now) ?? false;
        }) ?? $sortedScreenings->first();

        if (! $screening) {
            return null;
        }

        $hall = $this->hallFor($screening);

        return [
            'id' => $screening->id,
            'date' => $screening->screening_date?->format('Y-m-d'),
            'time' => $screening->screening_time,
            'datetime' => $this->screeningDateTime($screening)?->toDateTimeString(),
            'price' => (float) $screening->cost,
            'hall' => $hall ? [
                'id' => $hall->id,
                'name' => $hall->name,
                'seat_amount' => $hall->seat_amount,
            ] : null,
        ];
    }

    private function nextSessionLabel(?array $screening): ?string
    {
        if (! $screening) {
            return null;
        }

        return "{$screening['date']} {$screening['time']}";
    }

    private function screeningDateTime(Screening $screening): ?Carbon
    {
        if (! $screening->screening_date || ! $screening->screening_time) {
            return null;
        }

        return Carbon::parse("{$screening->screening_date->format('Y-m-d')} {$screening->screening_time}");
    }

    private function averageRating(Movie $movie): ?float
    {
        if ($movie->rating !== null) {
            return round((float) $movie->rating, 1);
        }

        if (! $movie->relationLoaded('feedbacks') || $movie->feedbacks->isEmpty()) {
            return null;
        }

        return round((float) $movie->feedbacks->avg('rating'), 1);
    }

    private function lowestPrice(Movie $movie): ?float
    {
        if ($movie->price !== null) {
            return (float) $movie->price;
        }

        if (! $movie->relationLoaded('screenings') || $movie->screenings->isEmpty()) {
            return null;
        }

        return (float) $movie->screenings->min('cost');
    }

    private function hallFor(Screening $screening): ?Hall
    {
        if ($screening->relationLoaded('cinemaHall')) {
            $hall = $screening->getRelation('cinemaHall');

            return $hall instanceof Hall ? $hall : null;
        }

        if ($screening->relationLoaded('hall')) {
            $hall = $screening->getRelation('hall');

            return $hall instanceof Hall ? $hall : null;
        }

        return $screening->cinemaHall()->first();
    }

    private function movieFor(Screening $screening): ?Movie
    {
        if ($screening->relationLoaded('movieRecord')) {
            $movie = $screening->getRelation('movieRecord');

            return $movie instanceof Movie ? $movie : null;
        }

        if ($screening->relationLoaded('movie')) {
            $movie = $screening->getRelation('movie');

            return $movie instanceof Movie ? $movie : null;
        }

        return $screening->movieRecord()->first();
    }

    private function posterFor(int $movieId): ?string
    {
        return [
            1 => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1000&q=80',
            2 => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1000&q=80',
            3 => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=1000&q=80',
            4 => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=1000&q=80',
            5 => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=1000&q=80',
            6 => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=1000&q=80',
        ][$movieId] ?? null;
    }
}
