<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_change_status_date_or_class_via_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $schedule = ClassSchedule::factory()->create();
        $otherSchedule = ClassSchedule::factory()->create();

        $booking = Booking::factory()->create([
            'user_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => '2026-12-01',
            'status' => 'booked',
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/bookings/{$booking->id}", [
            'status' => 'attended',
            'booking_date' => '2026-12-08',
            'class_schedule_id' => $otherSchedule->id,
        ])->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'booked',
            'class_schedule_id' => $schedule->id,
        ]);
        $this->assertSame(
            '2026-12-01',
            $booking->fresh()->booking_date->toDateString()
        );
    }

    public function test_admin_can_update_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = Booking::factory()->create(['notes' => null]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/bookings/{$booking->id}", [
            'notes' => 'Bawa sarung tinju sendiri',
        ])->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'notes' => 'Bawa sarung tinju sendiri',
        ]);
    }
}
