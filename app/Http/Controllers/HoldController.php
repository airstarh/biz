<?php

namespace App\Http\Controllers;

use App\Models\Hold;
use App\Services\SlotService;
use Illuminate\Http\Request;

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
                'message' => 'Idempotency-Key header is required',
                'status' => 400
            ], 400);
        }

        try {
            $result = $this->slotService->createHold($slotId, $idempotencyKey);

            return response()->json([
                'data' => $result['hold'],
                'message' => $result['message'],
                'status' => $result['status']
            ], $result['status']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Conflict',
                'message' => $e->getMessage(),
                'status' => 409
            ], 409);
        }
    }

    public function confirmHold(Hold $hold)
    {
        try {
            $this->slotService->confirmHold($hold);

            return response()->json([
                'message' => 'Hold confirmed successfully',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Conflict',
                'message' => $e->getMessage(),
                'status' => 409
            ], 409);
        }
    }

    public function cancelHold(Hold $hold)
    {
        try {
            $this->slotService->cancelHold($hold);

            return response()->json([
                'message' => 'Hold cancelled successfully',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error cancelling hold',
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
}
