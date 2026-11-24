<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_is_redirected_to_landing_page_after_login(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->post('/login', [
            'email' => $member->email,
            'password' => 'password',
        ])->assertRedirect('/');
    }

    public function test_staff_is_redirected_to_dashboard_after_login(): void
    {
        foreach (['admin', 'developer', 'owner'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->post('/logout');
        }
    }
}