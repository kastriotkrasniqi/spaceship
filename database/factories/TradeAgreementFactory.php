<?php

namespace Database\Factories;

use App\Models\Planet;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TradeAgreement>
 */
class TradeAgreementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'origin_id' => Planet::factory(),
            'destination_id' => Planet::factory(),
            'resource_id' => Resource::factory(),
            'quantity' => fake()->numberBetween(100, 1000),
            'frequency' => fake()->numberBetween(1, 10),
            'next_delivery' => now()->addDays(fake()->numberBetween(1, 30)),
        ];
    }
}
