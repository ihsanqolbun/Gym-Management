<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\Coach;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun khusus (bukan random) — role penting, harus predictable
        User::firstOrCreate(
            ['email' => 'developer@gym.test'],
            [
                'name' => 'Developer Account',
                'password' => Hash::make('password'),
                'role' => 'developer',
            ]
        );

        $owner = User::factory()->create([
            'name' => 'Owner Gym',
            'email' => 'owner@gym.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Gym',
            'email' => 'admin@gym.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Coaches — dibuat duluan karena ClassSchedule butuh ini
        $coaches = Coach::factory()->count(10)->create();

        // 3. Class Schedules — di-"recycle" ke coach yang udah ada,
        //    bukan bikin coach baru tiap row
        $schedules = ClassSchedule::factory()
            ->count(20)
            ->recycle($coaches)
            ->create();

        // 4. Members (role default sudah 'member' dari UserFactory)
        $members = User::factory()->count(40)->create();

        // 5. Memberships — gak semua member punya, ~30 dari 40 aja
        //    (biar ada juga data member yang "belum daftar membership")
        Membership::factory()
            ->count(30)
            ->recycle($members)
            ->create();

        // 6. Bookings — kombinasi random member + schedule.
        //    Pakai try/catch karena ada unique constraint
        //    (user_id, class_schedule_id, booking_date) yang bisa collision.
        $bookingTarget = 60;
        $created = 0;
        $attempts = 0;

        while ($created < $bookingTarget && $attempts < $bookingTarget * 3) {
            $attempts++;

            try {
                Booking::factory()
                    ->recycle($members)
                    ->recycle($schedules)
                    ->create();

                $created++;
            } catch (QueryException $e) {
                // Kena unique constraint (kombinasi user+schedule+tanggal
                // udah pernah ada) — skip aja, coba kombinasi lain di
                // iterasi berikutnya. Ini bukan bug, memang konsekuensi
                // dari data yang di-random.
                continue;
            }
        }
    }
}