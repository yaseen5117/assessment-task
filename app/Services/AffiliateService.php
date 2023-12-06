<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
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
        try {
            // Attempt to create a new user
            $user = $this->createUser($email, $name);

            // Attempt to create a new affiliate with a unique discount code
            $discountCodeData = $this->apiService->createDiscountCode($merchant);

            $affiliate = new Affiliate([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $commissionRate,
                'discount_code' => $discountCodeData['code'],
            ]);

            $affiliate->save();

            // Notify the affiliate about their registration
            Mail::to($email)->send(new AffiliateCreated($affiliate));

            return $affiliate;
        } catch (QueryException $e) {
            // Handle unique constraint violation (email already in use)
            if (str_contains($e->getMessage(), 'users_email_unique')) {
                throw new AffiliateCreateException('Error creating affiliate: Email is already in use.');
            } else {
                // Handle any other query exception
                throw new AffiliateCreateException('Error creating affiliate: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Handle any other exceptions
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
            'name' => $name,
            'type' => User::TYPE_MERCHANT,
        ]);
    }
}
