<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/coaches/create')->assertRedirect('/login');
    }

    public function test_member_gets_403_on_admin_pages(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/coaches')->assertForbidden();
        $this->actingAs($member)->get('/coaches/create')->assertForbidden();
        $this->actingAs($member)->get('/class-schedules/create')->assertForbidden();
    }

    public function test_admin_can_open_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/coaches/create')->assertOk();
        $this->actingAs($admin)->get('/class-schedules/create')->assertOk();
    }

    public function test_owner_can_view_coaches_but_not_create(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get('/coaches')->assertOk();
        $this->actingAs($owner)->get('/coaches/create')->assertForbidden();
    }

    public function test_api_still_returns_json_403_for_wrong_role(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        \Laravel\Sanctum\Sanctum::actingAs($member);

        $this->getJson('/api/users')
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden. Anda tidak memiliki akses untuk resource ini.']);
    }
}