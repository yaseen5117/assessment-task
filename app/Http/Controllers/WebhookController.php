<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Assuming webhook payload contains necessary data
        $data = $request->all();

        try {
            // Pass the data to the processOrder method in OrderService
            $this->orderService->processOrder($data);

            return response()->json(['message' => 'Order processed successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing order', 'error' => $e->getMessage()], 500);
        }
    }
}
