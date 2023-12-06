<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
		
        $fromDate = $request->from;
        $toDate = $request->to;
 
        // Calculate the total number of orders in the date range
        $orderCount = Order::whereBetween('created_at', [$fromDate, $toDate])->count();

        // Calculate the sum of order subtotals
        $revenue = Order::whereBetween('created_at', [$fromDate, $toDate])->sum('subtotal');

        // Calculate the amount of unpaid commissions for orders with an affiliate
        $commissionOwed = Order::whereBetween('created_at', [$fromDate, $toDate])->whereHas('affiliate')->sum('commission_owed');

        // Return the results
        return response()->json([
            'count' => $orderCount,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}
