<?php

namespace App\Services;

use App\Models\Slot;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Cache\LockTimeoutException;

class SlotService
{
    protected $cacheKey = 'slots:availability';
    protected $cacheDuration = 15; // в секундах
    protected $idempotencyCacheKey = 'idempotency:';
    protected $holdExpiration = 300; // 5 минут для холдов

    public function getAvailableSlots()
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            return Slot::with('holds')
                ->where('remaining', '>', 0)
                ->get();
        });
    }

    public function createHold(int $slotId, string $idempotencyKey): array
    {
        if (Cache::has($this->idempotencyCacheKey . $idempotencyKey)) {
            return ['message' => 'Request already processed', 'status' => 200];
        }

        try {
            $slot = Slot::lockForUpdate()->findOrFail($slotId);

            if ($slot->remaining <= 0) {
                throw new \Exception('No available slots');
            }

            $hold = Hold::create([
                'slot_id' => $slotId,
                'status' => 'held',
                'expires_at' => now()->addSeconds($this->holdExpiration)
            ]);

            Cache::put($this->idempotencyCacheKey . $idempotencyKey, true, 1);
            $this->invalidateCache();

            return ['hold' => $hold, 'status' => 201];
        } catch (QueryException $e) {
            return ['error' => 'Database error', 'status' => 500];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 409];
        }
    }

    public function confirmHold(Hold $hold): array
    {
        DB::beginTransaction();
        
        try {
            $slot = $hold->slot;

            if ($slot->remaining <= 0) {
                throw new \Exception('No available slots');
            }

            DB::updateOrFail(
                'UPDATE slots SET remaining = remaining - 1 WHERE id = ? AND remaining > 0',
                [$slot->id]
            );

            $hold->update(['status' => 'confirmed']);
            DB::commit();
            $this->invalidateCache();

            return ['message' => 'Hold confirmed', 'status' => 200];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => 'Conflict', 'status' => 409];
        }
    }

    public function cancelHold(Hold $hold): array
    {
        try {
            $hold->slot->increment('remaining');
            $hold->update(['status' => 'cancelled']);
            $this->invalidateCache();

            return ['message' => 'Hold cancelled', 'status' => 200];
        } catch (\Exception $e) {
            return ['error' => 'Error cancelling hold', 'status' => 500];
        }
    }

    public function invalidateCache()
    {
        Cache::forget($this->cacheKey);
    }

    public function processHold($slotId, $idempotencyKey, $request): array
    {
        if (Cache::has($this->idempotencyCacheKey . $idempotencyKey)) {
            return ['message' => 'Request already processed', 'status' => 200];
        }

        try {
            Cache::put($this->idempotencyCacheKey . $idempotencyKey, true, 1);
            return $this->createHold($slotId, $idempotencyKey);
        } catch (\Exception $e) {
            return ['error' => 'Internal server error', 'status' => 500];
        }
    }
}
