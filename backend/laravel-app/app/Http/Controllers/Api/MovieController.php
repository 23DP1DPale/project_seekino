<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Screening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->with('genres')
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

        return response()->json($movies);
    }

    public function show(Movie $movie): JsonResponse
    {
        $movie->load([
            'genres',
            'screenings.hall',
            'feedbacks',
        ])->loadAvg('feedbacks as rating', 'rating')
            ->loadMin('screenings as price', 'cost');

        return response()->json($movie);
    }

    public function screenings(): JsonResponse
    {
        $screenings = Screening::query()
            ->with([
                'hall',
                'movie.genres',
            ])
            ->orderBy('screening_date')
            ->orderBy('screening_time')
            ->get();

        return response()->json($screenings);
    }
}
