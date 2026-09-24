<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['harian', 'mingguan', 'bulanan']);

        $durationDays = match ($type) {
            'harian' => 1,
            'mingguan' => 7,
            'bulanan' => 30,
        };

        $price = match ($type) {
            'harian' => 50000,
            'mingguan' => 250000,
            'bulanan' => 800000,
        };

        $startDate = fake()->dateTimeBetween('-2 months', 'now');
        $endDate = (clone $startDate)->modify("+{$durationDays} days");

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'price' => $price,
            'payment_status' => 'paid',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $endDate > now() ? 'active' : 'expired',
        ];
    }
}