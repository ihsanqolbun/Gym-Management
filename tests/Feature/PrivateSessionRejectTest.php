<?php

namespace Tests\Feature;

use App\Models\PrivateSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrivateSessionRejectTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequestedSession(): PrivateSession
    {
        return PrivateSession::factory()->create([
            'user_id' => User::factory()->create(['role' => 'member'])->id,
            'session_date' => '2026-12-01',
            'start_time' => '08:00',
            'status' => 'requested',
            'coach_id' => null,
            'admin_note' => null,
        ]);
    }

    public function test_admin_can_reject_session_with_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = $this->makeRequestedSession();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/private-sessions/{$session->id}", [
            'status' => 'rejected',
            'admin_note' => 'Jadwal coach penuh.',
        ])->assertOk();

        $this->assertDatabaseHas('private_sessions', [
            'id' => $session->id,
            'status' => 'rejected',
            'admin_note' => 'Jadwal coach penuh.',
        ]);
    }

    public function test_status_other_than_approved_or_rejected_is_invalid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = $this->makeRequestedSession();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/private-sessions/{$session->id}", [
            'status' => 'cancelled',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('private_sessions', [
            'id' => $session->id,
            'status' => 'requested',
        ]);
    }

    public function test_member_cannot_reject_session(): void
    {
        $session = $this->makeRequestedSession();

        Sanctum::actingAs($session->user);

        $this->patchJson("/api/private-sessions/{$session->id}", [
            'status' => 'rejected',
        ])->assertForbidden();

        $this->assertDatabaseHas('private_sessions', [
            'id' => $session->id,
            'status' => 'requested',
        ]);
    }
}