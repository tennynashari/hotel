<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\HousekeepingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Rooms Management
    Route::get('/room-types', [RoomController::class, 'roomTypes']);
    Route::get('/rooms-statistics', [RoomController::class, 'statistics']);
    Route::apiResource('rooms', RoomController::class);
    Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus']);

    // Guests Management
    Route::apiResource('guests', GuestController::class);
    Route::get('/guests/search/{query}', [GuestController::class, 'search']);

    // Bookings Management
    Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability']);
    Route::apiResource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn']);
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Payments Management
    Route::apiResource('payments', PaymentController::class);
    Route::get('/bookings/{booking}/payments', [PaymentController::class, 'bookingPayments']);

    // Housekeeping Management
    Route::get('/housekeeping-statistics', [HousekeepingController::class, 'statistics']);
    Route::apiResource('housekeeping', HousekeepingController::class);
    Route::patch('/housekeeping/{housekeeping}/status', [HousekeepingController::class, 'updateStatus']);

    // Users Management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});
