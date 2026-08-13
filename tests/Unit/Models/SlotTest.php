<?php

namespace Tests\Unit\Models;

use App\Models\Slot;
use App\Models\Hold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a slot can be created with fillable attributes
     */
    public function test_slot_can_be_created_with_fillable_attributes(): void
    {
        $slot = Slot::create([
            'capacity' => 10,
            'remaining' => 8,
        ]);

        $this->assertDatabaseHas('slots', [
            'id' => $slot->id,
            'capacity' => 10,
            'remaining' => 8,
        ]);
    }

    /**
     * Test that slot has the correct fillable attributes
     */
    public function test_slot_has_fillable_attributes(): void
    {
        $slot = new Slot();
        $fillable = $slot->getFillable();

        $this->assertEquals(['capacity', 'remaining'], $fillable);
    }

    /**
     * Test that slot has many holds relationship
     */
    public function test_slot_has_many_holds(): void
    {
        $slot = Slot::factory()->create([
            'capacity' => 5,
            'remaining' => 5,
        ]);

        // Create multiple holds for this slot
        Hold::factory()->count(3)->create([
            'slot_id' => $slot->id,
            'status' => 'held',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $slot->holds);
        $this->assertCount(3, $slot->holds);
        $this->assertInstanceOf(Hold::class, $slot->holds->first());
    }

    /**
     * Test that remaining cannot exceed capacity
     */
    public function test_remaining_cannot_exceed_capacity(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // This should fail because remaining > capacity
        Slot::create([
            'capacity' => 5,
            'remaining' => 10,
        ]);
    }

    /**
     * Test that remaining can be decremented
     */
    public function test_remaining_can_be_decremented(): void
    {
        $slot = Slot::create([
            'capacity' => 5,
            'remaining' => 5,
        ]);

        $slot->decrement('remaining');

        $this->assertEquals(4, $slot->fresh()->remaining);
    }

    /**
     * Test that remaining can be incremented
     */
    public function test_remaining_can_be_incremented(): void
    {
        $slot = Slot::create([
            'capacity' => 5,
            'remaining' => 3,
        ]);

        $slot->increment('remaining');

        $this->assertEquals(4, $slot->fresh()->remaining);
    }
}
