<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meal = \App\Models\Meal::inRandomOrder()->first() ?? \App\Models\Meal::factory()->create();
    
        return [
            'order_id' => \App\Models\Order::inRandomOrder()->first()?->id ?? \App\Models\Order::factory(),
            'meal_id' => $meal->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $meal->price,
        ];
    }
}
