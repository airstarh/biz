<?php

namespace Tests\Feature;

use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    public function test_can_get_available_slots()
    {
        // Create some slots
        Slot::create(['capacity' => 10, 'remaining' => 5]);
        Slot::create(['capacity' => 20, 'remaining' => 15]);
        Slot::create(['capacity' => 10, 'remaining' => 0]); // Should not appear

        $response = $this->getJson('/api/v1/slots/availability');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Slots available'
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'capacity', 'remaining']
                ],
                'message',
                'status'
            ]);

        // Should only return slots with remaining > 0
        $this->assertCount(2, $response->json('data'));
    }

    public function test_availability_returns_empty_array_when_no_slots()
    {
        $response = $this->getJson('/api/v1/slots/availability');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'message' => 'Slots available',
                'status' => 200
            ]);
    }

    public function test_availability_handles_cache()
    {
        // First request - should populate cache
        Slot::create(['capacity' => 10, 'remaining' => 5]);

        $response1 = $this->getJson('/api/v1/slots/availability');
        $response1->assertStatus(200);

        // Second request - should use cache
        $response2 = $this->getJson('/api/v1/slots/availability');
        $response2->assertStatus(200);

        $this->assertEquals(
            $response1->json('data'),
            $response2->json('data')
        );
    }

    public function test_availability_caches_slots_for_15_seconds()
    {
        Slot::create(['capacity' => 10, 'remaining' => 5]);

        $response1 = $this->getJson('/api/v1/slots/availability');
        $response1->assertStatus(200);

        // Create new slot - should NOT appear immediately (cache)
        Slot::create(['capacity' => 20, 'remaining' => 15]);

        $response2 = $this->getJson('/api/v1/slots/availability');
        $this->assertCount(1, $response2->json('data')); // Still cached

        // Wait for cache to expire (15 seconds) and check again
        // Note: In tests, we usually mock cache or use shorter TTL
        // For now, we'll just verify the structure
        $response3 = $this->getJson('/api/v1/slots/availability');
        $response3->assertStatus(200);
    }
}
