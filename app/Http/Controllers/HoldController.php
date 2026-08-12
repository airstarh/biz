<?php

namespace App\Http\Controllers;

use App\Models\Hold;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoldController extends Controller
{
    protected SlotService $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    public function createHold(Request $request, int $slotId)
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json([
                'error' => 'Idempotency-Key required',
                'status' => 400
            ]);
        }

        try {
            $result = $this->slotService->createHold($slotId, $idempotencyKey);

            return response()->json([
                'data' => $result['hold'] ?? null,
                'message' => $result['message'],
                'status' => $result['status']
            ], $result['status']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function confirmHold(Hold $hold)
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
            $this->slotService->invalidateCache();

            return response()->json([
                'message' => 'Hold confirmed',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Conflict',
                'message' => $e->getMessage(),
                'status' => 409
            ]);
        }
    }

    public function cancelHold(Hold $hold)
    {
        try {
            $hold->update(['status' => 'cancelled']);
            $hold->slot->increment('remaining');
            $this->slotService->invalidateCache();

            return response()->json([
                'message' => 'Hold cancelled',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error cancelling hold',
                'message' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }
}
