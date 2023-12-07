<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // Use a database transaction to ensure data consistency
        DB::transaction(function () use ($apiService) {
            try {
                // Send payout using ApiService
                $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

                // Mark the order as paid in the database
                $this->order->update(['payout_status' => Order::STATUS_PAID]);
            } catch (RuntimeException $e) {
                // In case of exception, rollback and rethrow the exception
                DB::rollBack();
                throw $e;
            }
        });
    }
}
