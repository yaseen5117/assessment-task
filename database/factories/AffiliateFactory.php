<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliate>
 */
class AffiliateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'discount_code' => $this->faker->uuid(),
            'commission_rate' => round(rand(1, 5) / 10, 1),
            'user_id' => User::factory(), // Set the user relationship
        ];
    }
}
