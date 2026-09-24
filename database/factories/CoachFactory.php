<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CoachFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name('male'),
            'email' => fake()->unique()->safeEmail(),
            'specialty' => fake()->randomElement([
                'Muay Thai Klasik',
                'Muay Thai Kompetitif',
                'Muay Boran',
                'Clinch & Knee Work',
                'Kondisi Fisik & Padwork',
            ]),
            'phone' => fake()->numerify('08##########'),
        ];
    }
}