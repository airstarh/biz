<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AvailabilityController extends Controller
{
    protected SlotService $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    public function index(Request $request)
    {
        try {
            $slots = $this->slotService->getAvailableSlots();
            
            return response()->json([
                'data' => $slots,
                'message' => 'Slots available',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get available slots',
                'message' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }
}
