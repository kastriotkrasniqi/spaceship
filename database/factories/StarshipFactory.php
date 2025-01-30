<?php

namespace Database\Factories;

use App\Models\TradeRoute;
use App\Enums\StarshipStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Starship>
 */
class StarshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'cargo_capacity' => fake()->numberBetween(100, 5000),
            'status' => StarshipStatus::IDLE,
            'assigned_route_id' => TradeRoute::factory()
        ];
    }
}
