<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // Check if the order_id already exists, ignore duplicates
        if (Order::where('id', $data['order_id'])->exists()) {
            return;
        }

        $merchant = $this->findOrCreateMerchant($data['merchant_domain']);
        $user = $this->findOrCreateUser($data['customer_email'], $data['customer_name']);
        $affiliate = $this->findOrCreateAffiliate($user, $merchant, $data['discount_code']);

        // Calculate commission based on the default commission rate of the merchant
        $commissionRate = $merchant->default_commission_rate;
        $commissionAmount = $data['subtotal_price'] * $commissionRate;

        // Create the order
        $order = Order::create([
            'id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'commission_owed' => $commissionAmount,
            'discount_code' => $data['discount_code'],
        ]);
    }

    protected function findOrCreateMerchant(string $domain): Merchant
    {
        return Merchant::firstOrCreate(
            ['domain' => $domain],
            [
                'user_id' => auth()->id(),
                'display_name' => 'Merchant Display Name',
                'turn_customers_into_affiliates' => true,
                'default_commission_rate' => 0.1,
            ]
        );
    }

    protected function findOrCreateUser(string $email, string $name): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'type' => 'customer',
                'password' => bcrypt('test1234'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );
    }

    protected function findOrCreateAffiliate(User $user, Merchant $merchant, string $discountCode): Affiliate
    {
        return Affiliate::firstOrCreate([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
        ], [
            'commission_rate' => 0.0, // Set the default commission rate
            'discount_code' => $discountCode,
        ]);
    }
}
