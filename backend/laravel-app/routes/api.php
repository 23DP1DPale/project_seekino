<?php

use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{movie}', [MovieController::class, 'show']);
Route::get('/screenings', [MovieController::class, 'screenings']);
Route::get('/screenings/{screening}', [MovieController::class, 'showScreening']);
Route::post('/reservations', [ReservationController::class, 'store']);
