<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/booking', function () {
    return Inertia::render('Booking/Index');
})->middleware(['auth', 'verified'])->name('booking.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/coaches', function () {
    return Inertia::render('Coach/Index');
})->middleware(['auth', 'verified'])->name('coaches.index');

Route::get('/coaches/create', function () {
    return Inertia::render('Coach/Create');
})->middleware(['auth', 'verified'])->name('coaches.create');

Route::get('/coaches/{coach}/edit', function (\App\Models\Coach $coach) {
    return Inertia::render('Coach/Edit', [
        'coachId' => $coach->id,
    ]);
})->middleware(['auth', 'verified'])->name('coaches.edit');

Route::get('/class-schedules', function () {
    return Inertia::render('ClassSchedule/Index');
})->middleware(['auth', 'verified'])->name('class-schedules.index');

Route::get('/class-schedules/create', function () {
    return Inertia::render('ClassSchedule/Create');
})->middleware(['auth', 'verified'])->name('class-schedules.create');

Route::get('/class-schedules/{class_schedule}/edit', function (\App\Models\ClassSchedule $class_schedule) {
    return Inertia::render('ClassSchedule/Edit', [
        'scheduleId' => $class_schedule->id,
    ]);
})->middleware(['auth', 'verified'])->name('class-schedules.edit');

Route::get('/memberships', function () {
    return Inertia::render('Membership/Index');
})->middleware(['auth', 'verified'])->name('memberships.index');

Route::get('/memberships/create', function () {
    return Inertia::render('Membership/Create');
})->middleware(['auth', 'verified'])->name('memberships.create');

Route::get('/memberships/{membership}/edit', function (\App\Models\Membership $membership) {
    return Inertia::render('Membership/Edit', [
        'membershipId' => $membership->id,
    ]);
})->middleware(['auth', 'verified'])->name('memberships.edit');

require __DIR__.'/auth.php';