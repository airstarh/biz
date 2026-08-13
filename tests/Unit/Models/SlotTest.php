<?php

namespace Tests\Unit\Models;

use App\Models\Slot;
use App\Models\Hold;
use Tests\TestCase;

class SlotTest extends TestCase
{
    // Remove use RefreshDatabase;

    public function test_slot_can_be_created_with_fillable_attributes()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $this->assertDatabaseHas('slots', [
            'id' => $slot->id,
            'capacity' => 10,
            'remaining' => 5,
        ]);
    }

    public function test_slot_has_fillable_attributes()
    {
        $slot = new Slot();
        $fillable = ['capacity', 'remaining'];

        $this->assertEquals($fillable, $slot->getFillable());
    }

    public function test_slot_has_many_holds()
    {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 5]);
        $hold = Hold::factory()->create(['slot_id' => $slot->id]);

        $this->assertInstanceOf(Hold::class, $slot->holds->first());
        $this->assertEquals($hold->id, $slot->holds->first()->id);
    }

    public function test_remaining_cannot_exceed_capacity()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // This should fail because remaining > capacity is not allowed by database constraint
        Slot::create([
            'capacity' => 5,
            'remaining' => 10, // exceeds capacity
        ]);
    }

    public function test_remaining_can_be_decremented()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $slot->decrement('remaining');

        $this->assertEquals(4, $slot->fresh()->remaining);
    }

    public function test_remaining_can_be_incremented()
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $slot->increment('remaining');

        $this->assertEquals(6, $slot->fresh()->remaining);
    }

    public function test_remaining_never_negative()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 0,
        ]);

        // This should throw QueryException because unsigned column can't go below 0
        $slot->decrement('remaining');
    }
}
