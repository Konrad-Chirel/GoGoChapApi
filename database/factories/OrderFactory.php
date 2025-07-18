<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\DeliveryPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? Customer::factory(),
            'restaurant_id' => Restaurant::inRandomOrder()->first()?->id ?? Restaurant::factory(),
            'delivery_person_id' => DeliveryPerson::inRandomOrder()->first()?->id ?? null,
            'order_status' => $this->faker->randomElement(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled']),
            'total_price' => $this->faker->randomFloat(2, 1000, 10000),
            'delivery_fee' => $this->faker->randomElement([500, 600, 700]), // nouveau champ
            'order_date' => $this->faker->dateTimeBetween('-30 days'),
            'delivery_date' => $this->faker->optional()->dateTimeBetween('now', '+5 days'),
            'notes' => $this->faker->optional()->sentence,
            'status' => $this->faker->randomElement(['available', 'unavailable']),
        ];
    }
}
