<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PrivateSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'session_date' => fn () => Carbon::instance(fake()->dateTimeBetween('-1 month', '+1 month'))
                ->toDateString(),

            // Semua jam ini lolos aturan: mulai >= 06:00, selesai (+3 jam) <= 20:00
            'start_time' => fake()->randomElement(['06:00', '08:00', '10:00', '11:00', '13:00', '15:00', '16:00', '17:00']),

            // Slot ditentukan dari jam mulai, pakai batas yang sama dengan config
            'time_slot' => fn (array $a) => ($a['start_time'] >= config('pricelist.private.siang_start')
                && $a['start_time'] < config('pricelist.private.siang_end'))
                ? 'private_siang'
                : 'private_malam',

            'location_type' => fake()->randomElement(['gym', 'rumah']),

            'address' => fn (array $a) => $a['location_type'] === 'rumah' ? fake()->address() : null,

            // Satu sumber kebenaran harga: config/pricelist.php
            'price' => fn (array $a) => config("pricelist.prices.{$a['time_slot']}.visit_1x"),

            // Tanggal lampau: sudah diputuskan. Hari ini ke depan: masih bisa requested.
            'status' => function (array $a) {
                $isPast = Carbon::parse($a['session_date'], 'Asia/Jakarta')
                    ->lt(Carbon::today('Asia/Jakarta'));

                return $isPast
                    ? fake()->randomElement(['approved', 'approved', 'rejected', 'cancelled'])
                    : fake()->randomElement(['requested', 'requested', 'approved', 'rejected', 'cancelled']);
            },

            // Coach cuma ditugaskan kalau sudah approved
            'coach_id' => fn (array $a) => $a['status'] === 'approved'
                ? Coach::inRandomOrder()->value('id')
                : null,

            'admin_note' => fn (array $a) => $a['status'] === 'rejected'
                ? 'Jadwal coach penuh pada tanggal tersebut.'
                : null,
        ];
    }
}