<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\HR\DepartureReasonService;
use Illuminate\Http\Request;

class DepartureReasonController extends Controller
{
     protected DepartureReasonService $departureReasonService;

    public function __construct(DepartureReasonService $departureReasonService)
    {
        $this->departureReasonService = $departureReasonService;
    }

    public function syncDepartureReasons(Request $request)
    {
        $this->departureReasonService->syncData($request);
        return response()->json(['message' => 'Departure Reason synced successfully']);
    }

    public function departureReasonDelete(Request $request)
    {
        $jobPosition = $request->json()->all();
        $this->departureReasonService->departureReasonDelete($jobPosition);
        return response()->json(['message' => 'Departure Reason deleted successfully']);
    }
}
