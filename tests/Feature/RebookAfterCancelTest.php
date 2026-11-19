<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RebookAfterCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_rebook_same_class_and_date_after_cancel(): void
    {
        // Kunci waktu: Senin 23 Nov 2026 pagi WIB
        $this->travelTo(Carbon::parse('2026-11-23 10:00', 'Asia/Jakarta'));

        $member = User::factory()->create(['role' => 'member']);
        $schedule = ClassSchedule::factory()->create([
            'day_of_week' => 'senin',
            'is_active' => true,
            'capacity' => 10,
        ]);

        // Booking lama yang sudah cancelled, untuk Senin 30 Nov
        Booking::factory()->create([
            'user_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'booking_date' => '2026-11-30',
            'status' => 'cancelled',
            'checked_in_at' => null,
        ]);

        Sanctum::actingAs($member);

        $this->postJson('/api/bookings', [
            'class_schedule_id' => $schedule->id,
            'booking_date' => '2026-11-30',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['booking_date']);

        // Tetap cuma 1 row, dan statusnya masih cancelled
        $this->assertSame(1, Booking::count());
        $this->assertDatabaseHas('bookings', [
            'user_id' => $member->id,
            'status' => 'cancelled',
        ]);
    }
}