<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{movie}/feedbacks', [MovieController::class, 'feedbacks']);
Route::post('/movies/{movie}/feedbacks', [MovieController::class, 'storeFeedback']);
Route::get('/movies/{movie}', [MovieController::class, 'show']);
Route::get('/screenings', [MovieController::class, 'screenings']);
Route::get('/screenings/{screening}', [MovieController::class, 'showScreening']);
Route::get('/profile/reservations', [ReservationController::class, 'profileReservations']);
Route::patch('/profile/reservations/{reservation}/cancel', [ReservationController::class, 'cancelProfileReservation']);
Route::post('/reservations', [ReservationController::class, 'store']);
