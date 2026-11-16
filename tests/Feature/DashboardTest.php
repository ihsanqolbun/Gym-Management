<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\Membership;
use App\Models\PrivateSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
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
            'capacity' => 3,
            'is_active' => true,
        ], $override));
    }

    private function makeMembership(User $user, string $endDate, string $payment, string $status): Membership
    {
        return Membership::create([
            'user_id' => $user->id,
            'type' => 'bulanan',
            'price' => 450000,
            'payment_status' => $payment,
            'start_date' => '2026-10-01',
            'end_date' => $endDate,
            'status' => $status,
        ]);
    }

    private function makePrivateSession(User $user, string $date, string $status): PrivateSession
    {
        return PrivateSession::create([
            'user_id' => $user->id,
            'session_date' => $date,
            'start_time' => '08:00',
            'time_slot' => 'private_malam',
            'location_type' => 'gym',
            'price' => 125000,
            'status' => $status,
        ]);
    }

    // Bekukan jam: Senin 2026-11-16 10:00 WIB
    private function nowIsMondayWib(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16 10:00:00', 'Asia/Jakarta'));
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_staff_roles_get_staff_view(): void
    {
        $this->nowIsMondayWib();

        foreach (['developer', 'owner', 'admin'] as $role) {
            Sanctum::actingAs($this->makeUser($role, "{$role}@test.com"));

            $this->getJson('/api/dashboard')
                ->assertOk()
                ->assertJsonPath('data.role_view', 'staff');
        }
    }

    public function test_staff_summary_numbers_are_correct(): void
    {
        $this->nowIsMondayWib();

        $m1 = $this->makeUser('member', 'm1@test.com');
        $m2 = $this->makeUser('member', 'm2@test.com');
        $m3 = $this->makeUser('member', 'm3@test.com');

        // Kelas Senin, kapasitas 3: booked + attended makan slot, cancelled tidak -> sisa 1
        $schedule = $this->makeSchedule(['capacity' => 3]);
        Booking::create(['user_id' => $m1->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-16', 'status' => 'booked']);
        Booking::create(['user_id' => $m2->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-16', 'status' => 'attended']);
        Booking::create(['user_id' => $m3->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-16', 'status' => 'cancelled']);

        // Tidak boleh muncul: kelas hari lain dan kelas nonaktif
        $this->makeSchedule(['day_of_week' => 'selasa', 'class_name' => 'Kelas Selasa']);
        $this->makeSchedule(['is_active' => false, 'class_name' => 'Kelas Nonaktif']);

        // Membership: 1 aktif (paid), 1 expired dengan pembayaran pending
        $this->makeMembership($m1, '2026-12-01', 'paid', 'active');
        $this->makeMembership($m2, '2026-10-20', 'pending', 'expired');

        // Private session: 1 requested (dihitung), 1 approved (tidak)
        $this->makePrivateSession($m1, '2026-11-20', 'requested');
        $this->makePrivateSession($m2, '2026-11-21', 'approved');

        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-11-16')
            ->assertJsonCount(1, 'data.classes_today')
            ->assertJsonPath('data.classes_today.0.class_name', 'Basic Muay Thai')
            ->assertJsonPath('data.classes_today.0.available_slots', 1)
            ->assertJsonPath('data.active_memberships', 1)
            ->assertJsonPath('data.pending_payments', 1)
            ->assertJsonPath('data.pending_private_sessions', 1);
    }

    public function test_today_is_calculated_in_wib_not_utc(): void
    {
        // 2026-11-15 18:00 UTC = 2026-11-16 01:00 WIB (Senin). Di UTC masih Minggu.
        $this->travelTo(Carbon::parse('2026-11-15 18:00:00', 'UTC'));
        $this->makeSchedule(['day_of_week' => 'senin']);
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-11-16')
            ->assertJsonCount(1, 'data.classes_today');
    }

    public function test_member_sees_only_own_upcoming_data(): void
    {
        $this->nowIsMondayWib();

        $me = $this->makeUser('member', 'me@test.com');
        $other = $this->makeUser('member', 'other@test.com');
        $schedule = $this->makeSchedule();

        $membership = $this->makeMembership($me, '2026-12-10', 'paid', 'active');
        $this->makeMembership($other, '2026-12-20', 'paid', 'active');

        // Booking milik gue: 1 mendatang (dihitung), 1 lampau dan 1 cancelled (tidak)
        Booking::create(['user_id' => $me->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-23', 'status' => 'booked']);
        Booking::create(['user_id' => $me->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-09', 'status' => 'booked']);
        Booking::create(['user_id' => $me->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-30', 'status' => 'cancelled']);
        // Booking orang lain: tidak boleh bocor
        Booking::create(['user_id' => $other->id, 'class_schedule_id' => $schedule->id, 'booking_date' => '2026-11-23', 'status' => 'booked']);

        $this->makePrivateSession($me, '2026-11-20', 'requested');
        $this->makePrivateSession($me, '2026-11-21', 'rejected');
        $this->makePrivateSession($other, '2026-11-22', 'requested');

        Sanctum::actingAs($me);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.role_view', 'member')
            ->assertJsonPath('data.membership.id', $membership->id)
            ->assertJsonCount(1, 'data.upcoming_bookings')
            ->assertJsonCount(1, 'data.upcoming_private_sessions')
            ->assertJsonMissingPath('data.pending_payments');
    }

    public function test_member_without_active_membership_gets_null(): void
    {
        $this->nowIsMondayWib();

        $me = $this->makeUser('member', 'me@test.com');
        $this->makeMembership($me, '2026-10-20', 'paid', 'expired');
        Sanctum::actingAs($me);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.membership', null);
    }
}