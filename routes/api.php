<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CoachController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\MembershipController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:developer,owner,admin'])->group(function () {
    Route::apiResource('coaches', CoachController::class)->only(['index', 'show']);
});

Route::middleware(['auth:sanctum', 'role:developer,admin'])->group(function () {
    Route::apiResource('coaches', CoachController::class)->only(['store', 'update', 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:developer,owner,admin,member'])->group(function () {
    Route::apiResource('class-schedules', ClassScheduleController::class)->only(['index', 'show']);
});

Route::middleware(['auth:sanctum', 'role:developer,admin'])->group(function () {
    Route::apiResource('class-schedules', ClassScheduleController::class)->only(['store', 'update', 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('memberships', MembershipController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});