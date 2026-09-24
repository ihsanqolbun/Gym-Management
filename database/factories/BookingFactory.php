<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_schedule_id' => ClassSchedule::factory(),
            'booking_date' => fake()->dateTimeBetween('-1 month', '+1 week'),
            'status' => 'booked',
            'checked_in_at' => null,
            'notes' => null,
        ];
    }
}