<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingStoreTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function makeSchedule(array $override = []): ClassSchedule
    {
        return ClassSchedule::create(array_merge([
            'class_name' => 'Basic Muay Thai',
            'level' => 'beginner',
            'day_of_week' => 'senin',
            'start_time' => '18:00',
            'end_time' => '19:30',
            'capacity' => 2,
            'is_active' => true,
        ], $override));
    }

    // Senin terdekat ke depan, cocok dengan day_of_week 'senin'
    private function nextMonday(): string
    {
        return now()->next('Monday')->toDateString();
    }

    public function test_member_can_book_and_user_id_is_forced_to_self(): void
    {
        $member = $this->makeUser('member', 'm1@test.com');
        $other = $this->makeUser('member', 'm2@test.com');
        $schedule = $this->makeSchedule();

        Sanctum::actingAs($member);

        // member coba nyelundupin user_id orang lain
        $this->postJson('/api/bookings', [
            'user_id' => $other->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'status' => 'booked',
        ]);
        $this->assertDatabaseMissing('bookings', ['user_id' => $other->id]);
    }

    public function test_rejects_inactive_schedule(): void
    {
        Sanctum::actingAs($this->makeUser('member', 'm1@test.com'));
        $schedule = $this->makeSchedule(['is_active' => false]);

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertUnprocessable()->assertJsonValidationErrors('class_schedule_id');
    }

    public function test_rejects_date_that_does_not_match_class_day(): void
    {
        Sanctum::actingAs($this->makeUser('member', 'm1@test.com'));
        $schedule = $this->makeSchedule(); // senin

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => now()->next('Tuesday')->toDateString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('booking_date');
    }

    public function test_rejects_past_date(): void
    {
        Sanctum::actingAs($this->makeUser('member', 'm1@test.com'));
        $schedule = $this->makeSchedule();

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => now()->subWeek()->toDateString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('booking_date');
    }

    public function test_rejects_double_booking(): void
    {
        $member = $this->makeUser('member', 'm1@test.com');
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($member);

        $payload = [
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ];

        $this->postJson('/api/bookings', $payload)->assertCreated();
        $this->postJson('/api/bookings', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('booking_date');
    }

    public function test_rejects_when_class_is_full(): void
    {
        $first = $this->makeUser('member', 'm1@test.com');
        $second = $this->makeUser('member', 'm2@test.com');
        $schedule = $this->makeSchedule(['capacity' => 1]);

        Booking::create([
            'user_id' => $first->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
            'status' => 'booked',
        ]);

        Sanctum::actingAs($second);

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertUnprocessable()->assertJsonValidationErrors('class_schedule_id');
    }

    public function test_admin_must_provide_user_id(): void
    {
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));
        $schedule = $this->makeSchedule();

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertUnprocessable()->assertJsonValidationErrors('user_id');
    }

    public function test_admin_can_book_for_a_member(): void
    {
        $member = $this->makeUser('member', 'm1@test.com');
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));
        $schedule = $this->makeSchedule();

        $this->postJson('/api/bookings', [
            'user_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertCreated();

        $this->assertDatabaseHas('bookings', ['user_id' => $member->id, 'status' => 'booked']);
    }

    public function test_owner_cannot_book(): void
    {
        Sanctum::actingAs($this->makeUser('owner', 'o@test.com'));
        $schedule = $this->makeSchedule();

        $this->postJson('/api/bookings', [
            'user_id' => 1,
            'class_schedule_id' => $schedule->id,
            'booking_date' => $this->nextMonday(),
        ])->assertForbidden();
    }
}