<?php

namespace Tests\Feature;

use App\Models\PrivateSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrivateSessionCancelTest extends TestCase
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

    private function makeSession(User $member, string $date, string $startTime, string $status = 'requested'): PrivateSession
    {
        return PrivateSession::create([
            'user_id' => $member->id,
            'session_date' => $date,
            'start_time' => $startTime,
            'time_slot' => 'private_siang',
            'location_type' => 'gym',
            'price' => 100000,
            'status' => $status,
        ]);
    }

    // Bekukan jam di momen tertentu. Input ditulis dalam WIB biar gampang dibaca.
    private function nowIsWib(string $datetime): void
    {
        $this->travelTo(Carbon::parse($datetime, 'Asia/Jakarta'));
    }

    public function test_member_can_cancel_own_session_more_than_4_hours_before_start(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '18:00');
        $this->nowIsWib('2026-11-15 07:00');
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertOk();
        $this->assertSame('cancelled', $session->fresh()->status);
    }

    public function test_member_can_cancel_exactly_4_hours_before_start(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '11:00');
        $this->nowIsWib('2026-11-15 07:00');
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertOk();
    }

    public function test_member_cannot_cancel_less_than_4_hours_before_start(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '08:00');
        $this->nowIsWib('2026-11-15 07:30');
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertUnprocessable();
        $this->assertSame('requested', $session->fresh()->status);
    }

    public function test_member_cannot_cancel_a_session_that_already_passed(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-09', '16:00', 'approved');
        $this->nowIsWib('2026-11-15 10:00');
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertUnprocessable();
        $this->assertSame('approved', $session->fresh()->status);
    }

    public function test_cancellation_window_uses_wib_not_utc(): void
    {
        // Sesi 10:00 WIB (= 03:00 UTC). Sekarang 07:00 WIB (= 00:00 UTC), tersisa 3 jam, harus ditolak.
        // Kalau start_time salah dibaca sebagai UTC, sisa waktunya 10 jam dan malah lolos.
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '10:00');
        $this->travelTo(Carbon::parse('2026-11-15 00:00:00', 'UTC'));
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertUnprocessable();
    }

    public function test_member_cannot_cancel_someone_elses_session(): void
    {
        $owner = $this->makeUser('member', 'm1@test.com');
        $other = $this->makeUser('member', 'm2@test.com');
        $session = $this->makeSession($owner, '2026-11-15', '18:00');
        $this->nowIsWib('2026-11-15 07:00');
        Sanctum::actingAs($other);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertForbidden();
        $this->assertSame('requested', $session->fresh()->status);
    }

    public function test_member_cannot_cancel_rejected_session(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '18:00', 'rejected');
        $this->nowIsWib('2026-11-15 07:00');
        Sanctum::actingAs($member);

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertForbidden();
    }

    public function test_admin_can_cancel_inside_window_and_past_sessions(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $soon = $this->makeSession($member, '2026-11-15', '08:00');
        $past = $this->makeSession($member, '2026-11-09', '16:00', 'approved');
        $this->nowIsWib('2026-11-15 07:30');
        Sanctum::actingAs($this->makeUser('admin', 'a@test.com'));

        $this->patchJson("/api/private-sessions/{$soon->id}/cancel")->assertOk();
        $this->patchJson("/api/private-sessions/{$past->id}/cancel")->assertOk();
    }

    public function test_owner_cannot_cancel(): void
    {
        $member = $this->makeUser('member', 'm@test.com');
        $session = $this->makeSession($member, '2026-11-15', '18:00');
        $this->nowIsWib('2026-11-15 07:00');
        Sanctum::actingAs($this->makeUser('owner', 'o@test.com'));

        $this->patchJson("/api/private-sessions/{$session->id}/cancel")->assertForbidden();
    }
}