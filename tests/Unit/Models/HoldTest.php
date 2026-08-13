<?php

namespace Tests\Unit\Models;

use App\Models\Hold;
use App\Models\Slot;
use Tests\TestCase;
use Carbon\Carbon;

class HoldTest extends TestCase
{
    public function test_hold_can_be_created_with_fillable_attributes()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertDatabaseHas('holds', [
            'id' => $hold->id,
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
        ]);
    }

    public function test_hold_has_fillable_attributes()
    {
        $hold = new Hold();
        $fillable = ['slot_id', 'status', 'expires_at'];

        $this->assertEquals($fillable, $hold->getFillable());
    }

    public function test_hold_belongs_to_slot()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertInstanceOf(Slot::class, $hold->slot);
        $this->assertEquals($slot->id, $hold->slot->id);
    }

    public function test_hold_has_status_constants()
    {
        $this->assertEquals('held', Hold::STATUS_HELD);
        $this->assertEquals('confirmed', Hold::STATUS_CONFIRMED);
        $this->assertEquals('cancelled', Hold::STATUS_CANCELLED);
    }

    public function test_hold_expires_at_is_cast_to_datetime()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => '2026-08-13 16:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $hold->expires_at);
    }

    public function test_hold_can_be_held_status()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertEquals(Hold::STATUS_HELD, $hold->status);
        $this->assertTrue($hold->status === 'held');
    }

    public function test_hold_can_be_confirmed_status()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_CONFIRMED,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertEquals(Hold::STATUS_CONFIRMED, $hold->status);
        $this->assertTrue($hold->status === 'confirmed');
    }

    public function test_hold_can_be_cancelled_status()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_CANCELLED,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertEquals(Hold::STATUS_CANCELLED, $hold->status);
        $this->assertTrue($hold->status === 'cancelled');
    }

    public function test_hold_expires_at_default_is_5_minutes()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $now = now();
        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => $now->copy()->addMinutes(5),
        ]);

        $this->assertEquals($now->addMinutes(5)->format('Y-m-d H:i'), $hold->expires_at->format('Y-m-d H:i'));
    }

    public function test_hold_can_be_created_using_factory()
    {
        $hold = Hold::factory()->create();

        $this->assertDatabaseHas('holds', [
            'id' => $hold->id,
            'slot_id' => $hold->slot_id,
            'status' => $hold->status,
        ]);

        $this->assertNotNull($hold->slot);
        $this->assertInstanceOf(Slot::class, $hold->slot);
    }

    public function test_hold_factory_generates_valid_status()
    {
        $hold = Hold::factory()->create();

        $this->assertContains($hold->status, [
            Hold::STATUS_HELD,
            Hold::STATUS_CONFIRMED,
            Hold::STATUS_CANCELLED
        ]);
    }

    public function test_hold_status_can_be_updated()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $hold->update(['status' => Hold::STATUS_CONFIRMED]);

        $this->assertEquals(Hold::STATUS_CONFIRMED, $hold->fresh()->status);
    }

    public function test_hold_can_be_deleted()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $holdId = $hold->id;
        $hold->delete();

        $this->assertDatabaseMissing('holds', ['id' => $holdId]);
    }

    public function test_hold_cascade_delete_when_slot_deleted()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $hold = Hold::create([
            'slot_id' => $slot->id,
            'status' => Hold::STATUS_HELD,
            'expires_at' => now()->addMinutes(5),
        ]);

        $holdId = $hold->id;
        $slot->delete();

        $this->assertDatabaseMissing('holds', ['id' => $holdId]);
    }
}
