<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        $affiliate = new Affiliate([
            'user_id' => $this->createUser($email, $name)->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->generateDiscountCode(),
        ]);

        try {
            $affiliate->save();

            // Notify the affiliate about their registration
            Mail::to($email)->send(new AffiliateCreated($affiliate));

            return $affiliate;
        } catch (\Exception $e) {
            // Handle any exceptions that might occur during affiliate creation
            throw new AffiliateCreateException('Error creating affiliate: ' . $e->getMessage());
        }
    }

    /**
     * Create a new user for the affiliate.
     *
     * @param  string $email
     * @param  string $name
     * @return User
     */
    protected function createUser(string $email, string $name): User
    {
        return User::create([
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * Generate a unique discount code for the affiliate.
     *
     * @return string
     */
    protected function generateDiscountCode(): string
    {
        return uniqid('DISCOUNT_', true);
    }
}
