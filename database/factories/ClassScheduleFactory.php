<?php

namespace Database\Factories;

use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startTime = fake()->randomElement(['06:00', '08:00', '17:00', '19:00']);
        $endTime = date('H:i', strtotime($startTime) + 3600); // +1 jam

        return [
            'coach_id' => Coach::factory(),
            'class_name' => fake()->randomElement([
                'Muay Thai Basic',
                'Muay Thai Advanced',
                'Sparring Class',
                'Padwork & Conditioning',
            ]),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'day_of_week' => fake()->randomElement([
                'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu',
            ]),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'capacity' => fake()->numberBetween(10, 25),
            'location' => fake()->randomElement(['Main Ring', 'Outdoor Area', 'Studio 2']),
            'is_active' => true,
        ];
    }
}