<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryPerson>
 */
class DeliveryPersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'files' => 'files/sample_identity.pdf',
            'solde' => $this->faker->randomFloat(2, 50, 3000),
            'position' => $this->faker->address,
            'status' => $this->faker->randomElement(['available', 'unavailable']),
            'delivery_enterprise_id' => \App\Models\DeliveryEnterprise::inRandomOrder()->first()?->id ?? \App\Models\DeliveryEnterprise::factory(),
        ];
    }
}
