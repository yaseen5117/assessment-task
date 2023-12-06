<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Merchant;
use App\Models\Affiliate;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {        
		return [
            'subtotal' => $subtotal = round(rand(100, 999) / 3, 2),
            'commission_owed' => round($subtotal * 0.1, 2),
            'merchant_id' => Merchant::factory(), // Set the merchant relationship
            'affiliate_id' => Affiliate::factory()->for(User::factory()), // Set the affiliate and user relationships
        ];
    }
}
