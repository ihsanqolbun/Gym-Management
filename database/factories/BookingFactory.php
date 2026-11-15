<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingFactory extends Factory
{
    // day_of_week di DB pakai bahasa Indonesia, Carbon butuh nama Inggris
    private const DAYS = [
        'senin' => 'Monday', 'selasa' => 'Tuesday', 'rabu' => 'Wednesday',
        'kamis' => 'Thursday', 'jumat' => 'Friday', 'sabtu' => 'Saturday',
        'minggu' => 'Sunday',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_schedule_id' => ClassSchedule::factory(),

            // Tanggal random, lalu digeser ke hari kelas yang sesuai jadwal
            'booking_date' => function (array $attributes) {
                $schedule = ClassSchedule::find($attributes['class_schedule_id']);
                $random = Carbon::instance(fake()->dateTimeBetween('-1 month', '+2 weeks'));

                return $random->next(self::DAYS[$schedule->day_of_week])->toDateString();
            },

            // Tanggal lampau: kebanyakan attended, sisanya cancelled. Hari ini dan ke depan: booked.
            'status' => function (array $attributes) {
                $date = Carbon::parse($attributes['booking_date'], 'Asia/Jakarta');

                if ($date->gte(Carbon::today('Asia/Jakarta'))) {
                    return 'booked';
                }

                return fake()->boolean(80) ? 'attended' : 'cancelled';
            },

            // Hanya yang attended punya waktu check-in (11:00 UTC = 18:00 WIB)
            'checked_in_at' => function (array $attributes) {
                return $attributes['status'] === 'attended'
                    ? Carbon::parse($attributes['booking_date'])->setTime(11, 0)
                    : null;
            },

            'notes' => null,
        ];
    }
}