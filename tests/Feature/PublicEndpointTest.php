<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\Coach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_active_class_schedules_without_sensitive_fields(): void
    {
        $coach = Coach::factory()->create([
            'name' => 'Coach A',
            'email' => 'coach@secret.test',
            'phone' => '0812345',
        ]);

        ClassSchedule::factory()->create([
            'class_name' => 'Muay Thai Basic',
            'is_active' => true,
            'coach_id' => $coach->id,
        ]);
        ClassSchedule::factory()->create(['class_name' => 'Kelas Nonaktif', 'is_active' => false]);

        $response = $this->getJson('/api/public/class-schedules')->assertOk();

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.class_name', 'Muay Thai Basic')
            ->assertJsonPath('data.0.coach_name', 'Coach A')
            ->assertJsonMissingPath('data.0.capacity')
            ->assertJsonMissingPath('data.0.is_active');

        $this->assertStringNotContainsString('coach@secret.test', $response->getContent());
        $this->assertStringNotContainsString('0812345', $response->getContent());
    }

    public function test_guest_can_see_pricelist(): void
    {
        $this->getJson('/api/public/pricelist')
            ->assertOk()
            ->assertJsonPath('data.register_fee', 75000)
            ->assertJsonPath('data.prices.dewasa.all_day_month', 450000)
            ->assertJsonPath('data.prices.private_malam.visit_1x', 125000)
            ->assertJsonMissingPath('data.private');
    }

}
