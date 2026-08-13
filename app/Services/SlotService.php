<?php

namespace App\Services;

use App\Models\Slot;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlotService
{
    private const CACHE_KEY = 'available_slots';
    private const CACHE_TTL = 15; // seconds

    public function getAvailableSlots()
    {
        // Cache stampede protection using mutex
        $lock = Cache::lock(self::CACHE_KEY . '_mutex', 5);

        try {
            // Try to get from cache
            $slots = Cache::get(self::CACHE_KEY);

            if ($slots !== null) {
                return $slots;
            }

            // If cache miss, acquire lock
            if ($lock->get()) {
                try {
                    // Get from database
                    $slots = Slot::select('id', 'capacity', 'remaining')
                        ->where('remaining', '>', 0)
                        ->get();

                    // Store in cache
                    Cache::put(self::CACHE_KEY, $slots, self::CACHE_TTL);

                    return $slots;
                } finally {
                    $lock->release();
                }
            }

            // If can't acquire lock, wait and retry
            return $this->getAvailableSlots();
        } catch (\Exception $e) {
            // Fallback to database query if cache fails
            return Slot::select('id', 'capacity', 'remaining')
                ->where('remaining', '>', 0)
                ->get();
        }
    }

    public function createHold(int $slotId, string $idempotencyKey): array
    {
        // Check if request already processed
        $cachedResult = Cache::get("idempotent_{$idempotencyKey}");
        if ($cachedResult) {
            return $cachedResult;
        }

        DB::beginTransaction();

        try {
            $slot = Slot::findOrFail($slotId);

            // Check availability
            if ($slot->remaining <= 0) {
                throw new \Exception('No available slots');
            }

            // Create hold
            $hold = Hold::create([
                'slot_id' => $slotId,
                'status' => Hold::STATUS_HELD,
                'expires_at' => now()->addMinutes(5),
            ]);

            // Decrement remaining
            $slot->decrement('remaining');

            DB::commit();

            // Invalidate cache
            $this->invalidateCache();

            $result = [
                'hold' => $hold,
                'message' => 'Hold created successfully',
                'status' => 201
            ];

            // Cache idempotent result
            Cache::put("idempotent_{$idempotencyKey}", $result, 600);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmHold(Hold $hold): void
    {
        DB::beginTransaction();

        try {
            // Check if hold is still valid
            if ($hold->status !== Hold::STATUS_HELD) {
                throw new \Exception('Hold is not in held status');
            }

            if ($hold->expires_at < now()) {
                throw new \Exception('Hold has expired');
            }

            // Update hold status
            $hold->update(['status' => Hold::STATUS_CONFIRMED]);

            DB::commit();

            // Invalidate cache
            $this->invalidateCache();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelHold(Hold $hold): void
    {
        DB::beginTransaction();

        try {
            // Check if hold can be cancelled
            if ($hold->status === Hold::STATUS_CONFIRMED) {
                throw new \Exception('Confirmed hold cannot be cancelled');
            }

            // Only cancel if it's still in 'held' status
            if ($hold->status !== Hold::STATUS_HELD) {
                throw new \Exception('Hold cannot be cancelled');
            }

            // Update hold status
            $hold->update(['status' => Hold::STATUS_CANCELLED]);

            // Return slot - make sure it doesn't exceed capacity
            $slot = $hold->slot;
            if ($slot->remaining < $slot->capacity) {
                $slot->increment('remaining');
            }

            DB::commit();

            // Invalidate cache
            $this->invalidateCache();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
