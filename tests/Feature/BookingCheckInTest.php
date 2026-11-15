<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingCheckInTest extends TestCase
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

    private function makeBooking(User $member, string $date, string $status = 'booked'): Booking
    {
        $schedule = ClassSchedule::create([
            'class_name' => 'Basic Muay Thai',
            'level' => 'beginner',
            'day_of_week' => 'senin',
            'start_time' => '18:00',
            'end_time' => '19:30',
            'capacity' => 10,
            'is_active' => true,
        ]);

        return Booking::create([
            'user_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => $date,
            'status' => $status,
        ]);
    }

    private function todayWib(): string
    {
        return now('Asia/Jakarta')->toDateString();
    }

    public function test_admin_can_check_in_on_booking_date(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib());
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertOk();

        $booking->refresh();
        $this->assertSame('attended', $booking->status);
        $this->assertNotNull($booking->checked_in_at);
    }

    public function test_developer_can_check_in(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib());
        Sanctum::actingAs($this->makeUser('developer', 'd@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertOk();
    }

    public function test_member_cannot_check_in_even_own_booking(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib());
        Sanctum::actingAs($member);

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertForbidden();
        $this->assertSame('booked', $booking->fresh()->status);
    }

    public function test_owner_cannot_check_in(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib());
        Sanctum::actingAs($this->makeUser('owner', 'o@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertForbidden();
    }

    public function test_rejects_check_in_for_cancelled_booking(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib(), 'cancelled');
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertUnprocessable();
    }

    public function test_rejects_double_check_in(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, $this->todayWib());
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertOk();
        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertUnprocessable();
    }

    public function test_rejects_check_in_on_a_different_date(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, now('Asia/Jakarta')->addDay()->toDateString());
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertUnprocessable();
    }

    public function test_today_is_calculated_in_wib_not_utc(): void
    {
        // 2026-11-15 18:00 UTC = 2026-11-16 01:00 WIB
        $this->travelTo(Carbon::parse('2026-11-15 18:00:00', 'UTC'));

        $member = $this->makeUser('member', 'm@test.com');
        $booking = $this->makeBooking($member, '2026-11-16');
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/bookings/{$booking->id}/check-in")->assertOk();
    }
}