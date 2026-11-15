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
use App\Models\PrivateSession;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun tetap per role (predictable, password: "password")
        User::firstOrCreate(
            ['email' => 'developer@gym.test'],
            [
                'name' => 'Developer Account',
                'password' => Hash::make('password'),
                'role' => 'developer',
            ]
        );

        User::factory()->create([
            'name' => 'Owner Gym',
            'email' => 'owner@gym.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        User::factory()->create([
            'name' => 'Admin Gym',
            'email' => 'admin@gym.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $demoMember = User::factory()->create([
            'name' => 'Demo Member',
            'email' => 'member@gym.test',
            'password' => Hash::make('password'),
            'role' => 'member',
            'date_of_birth' => '1998-05-20',
        ]);

        // 2. Coaches dulu, karena ClassSchedule butuh coach
        $coaches = Coach::factory()->count(10)->create();

        // 3. Class schedules, di-recycle ke coach yang sudah ada
        $schedules = ClassSchedule::factory()
            ->count(20)
            ->recycle($coaches)
            ->create();

        // 4. Member random, demo member ikut masuk pool
        $members = User::factory()->count(40)->create();
        $members->push($demoMember);

        // 5. Membership demo member dibuat duluan (jadi membership pertama,
        //    jadi kena register fee), lalu sisanya random
        Membership::factory()->create([
            'user_id' => $demoMember->id,
            'package' => 'all_day_month',
            'start_date' => now('Asia/Jakarta')->subDays(5)->toDateString(),
            'payment_status' => 'paid',
        ]);

        Membership::factory()
            ->count(30)
            ->recycle($members)
            ->create();

        // 6. Booking random. try/catch karena kombinasi (user, jadwal, tanggal)
        //    bisa kena unique constraint, itu konsekuensi data random.
        $this->createBookings(60, fn () => Booking::factory()->recycle($members)->recycle($schedules));

        // 7. Booking demo member (3 buah)
        $this->createBookings(3, fn () => Booking::factory()->recycle($schedules), ['user_id' => $demoMember->id]);
        // 8. Private session: random + 2 buat demo member
            PrivateSession::factory()->count(10)->recycle($members)->create();
            PrivateSession::factory()->count(2)->create(['user_id' => $demoMember->id]);
        }

    private function createBookings(int $target, \Closure $makeFactory, array $overrides = []): void
    {
        $created = 0;
        $attempts = 0;

        while ($created < $target && $attempts < $target * 5) {
            $attempts++;

            try {
                $makeFactory()->create($overrides);
                $created++;
            } catch (QueryException $e) {
                continue;
            }
        }
    }
}