<?php

namespace Tests\Feature;

use App\Models\Slot;
use App\Models\Hold;
use Tests\TestCase;
use Illuminate\Support\Str;

class HoldTest extends TestCase
{
    public function test_can_create_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);
        $idempotencyKey = (string) Str::uuid();

        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Hold created successfully',
                'status' => 201
            ])
            ->assertJsonStructure([
                'data' => ['id', 'slot_id', 'status', 'expires_at'],
                'message',
                'status'
            ]);

        $this->assertEquals(4, $slot->fresh()->remaining);
        $this->assertDatabaseHas('holds', [
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD
        ]);
    }

    public function test_create_hold_requires_idempotency_key()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Idempotency-Key required',
                'status' => 400
            ]);
    }

    public function test_create_hold_returns_409_when_no_remaining()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 0]);
        $idempotencyKey = (string) Str::uuid();

        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'status' => 409
            ]);
    }

    public function test_create_hold_is_idempotent()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);
        $idempotencyKey = (string) Str::uuid();

        $response1 = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response1->assertStatus(201);

        $response2 = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response2->assertStatus(201);

        $this->assertEquals(
            $response1->json('data.id'),
            $response2->json('data.id')
        );

        $this->assertEquals(4, $slot->fresh()->remaining);
        $this->assertEquals(1, Hold::where('slot_id', $slot->id)->count());
    }

    public function test_can_confirm_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Hold confirmed successfully',
                'status' => 200
            ]);

        $this->assertEquals(Hold::STATUS_CONFIRMED, $hold->fresh()->status);
    }

    public function test_cannot_confirm_expired_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        // Create hold through the service
        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        // Manually expire the hold
        $hold->update(['expires_at' => now()->subMinutes(1)]);

        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'status' => 409
            ]);
    }

    public function test_cannot_confirm_already_confirmed_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        // Create hold through the service
        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        // Confirm first
        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");
        $response->assertStatus(200);

        // Try to confirm again
        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'status' => 409
            ]);
    }

    public function test_can_cancel_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        // Create hold through the service
        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        $response = $this->deleteJson("/api/v1/holds/{$hold->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Hold cancelled successfully',
                'status' => 200
            ]);

        $this->assertEquals(Hold::STATUS_CANCELLED, $hold->fresh()->status);
        $this->assertEquals(5, $slot->fresh()->remaining); // Returned the slot
    }

    public function test_cannot_cancel_confirmed_hold()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        // Create hold through the service
        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        // Confirm the hold first
        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");
        $response->assertStatus(200);

        // Try to cancel confirmed hold
        $response = $this->deleteJson("/api/v1/holds/{$hold->id}");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Error cancelling hold',
                'status' => 400
            ]);

        $this->assertEquals(Hold::STATUS_CONFIRMED, $hold->fresh()->status);
        $this->assertEquals(4, $slot->fresh()->remaining); // Should be 4 (1 decremented)
    }

    public function test_confirmed_hold_decrements_remaining_atomically()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 5]);

        // Create hold through the service (this decrements remaining)
        $idempotencyKey = (string) Str::uuid();
        $response = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey
        ]);
        $response->assertStatus(201);

        $hold = Hold::where('slot_id', $slot->id)->first();

        // Remaining should already be 4 (decremented when hold was created)
        $this->assertEquals(4, $slot->fresh()->remaining);

        // Confirm hold - should NOT decrement again
        $response = $this->postJson("/api/v1/holds/{$hold->id}/confirm");
        $response->assertStatus(200);

        $this->assertEquals(4, $slot->fresh()->remaining); // Still 4
    }

    public function test_concurrent_hold_creation_prevents_overselling()
    {
        $slot = Slot::create(['capacity' => 10, 'remaining' => 1]);

        $idempotencyKey1 = (string) Str::uuid();
        $idempotencyKey2 = (string) Str::uuid();

        $response1 = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey1
        ]);

        $response2 = $this->postJson("/api/v1/slots/{$slot->id}/hold", [], [
            'Idempotency-Key' => $idempotencyKey2
        ]);

        $statuses = [$response1->status(), $response2->status()];
        $this->assertContains(201, $statuses);
        $this->assertContains(409, $statuses);
        $this->assertEquals(0, $slot->fresh()->remaining);
    }
}
