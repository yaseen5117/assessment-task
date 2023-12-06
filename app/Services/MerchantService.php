<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
	{
		// Create a new user with the provided data
		$user = User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => $data['api_key'], // Store the API key directly (no bcrypt here)
			'type' => User::TYPE_MERCHANT,
		]);

		// Create a new merchant associated with the user
		$merchant = new Merchant([
			'user_id' => $user->id,
			'domain' => $data['domain'],
			'display_name' => $data['name'], // Set the display_name column in the merchants table
		]);

		// Save the merchant
		$merchant->save();

		return $merchant;
	}




    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
{
    // Update the user details
    $user->update([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['api_key']), // API key is used as the password
    ]);

    // Find and update the associated merchant details
    $merchant = Merchant::where('user_id', $user->id)->first();
    if ($merchant) {
        $merchant->update(['domain' => $data['domain'], 'display_name' => $data['name']]);
    }
}


    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user by email and check if it's a merchant
        $user = User::where('email', $email)
            ->where('type', User::TYPE_MERCHANT)
            ->first();

        // If user not found, return null
        if (!$user) {
            return null;
        }

        // Find and return the associated merchant
        return Merchant::where('user_id', $user->id)->first();
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // Get all unpaid orders for the affiliate
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
            ->where('payout_status', Order::STATUS_UNPAID)
            ->get();

        // Dispatch a PayoutOrderJob for each unpaid order
        foreach ($unpaidOrders as $order) {
            PayoutOrderJob::dispatch($order);
        }
    }
}
