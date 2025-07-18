<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement(['client', 'livreur', 'partenaire', 'entreprise_livraison', 'admin']);
    
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => $role,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'avatar' => null,
            'info' => fake()->sentence(),
            'customer_id' => $role === 'client' ? \App\Models\Customer::factory() : null,
            'restaurant_id' => $role === 'partenaire' ? \App\Models\Restaurant::factory() : null,
            'delivery_person_id' => $role === 'livreur' ? \App\Models\DeliveryPerson::factory() : null,
            'delivery_enterprise_id' => $role === 'livreur_entreprise' ? \App\Models\DeliveryEnterprise::factory() : null,
            'admin_id' => $role === 'admin' ? \App\Models\Admin::factory() : null,
            'is_blocked' => false,
        ];
    }
    
    

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
