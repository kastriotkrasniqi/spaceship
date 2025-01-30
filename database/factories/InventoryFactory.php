<?php

namespace Database\Factories;

use App\Models\Planet;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planet_id' => Planet::factory(),
            'resource_id' => Resource::factory(),
            'quantity' => fake()->numberBetween(1, 100),
            'price'    =>  fake()->numberBetween(1000, 10000),
        ];
    }
}
