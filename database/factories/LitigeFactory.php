<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Litige>
 */
class LitigeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['urgent', 'moyen', 'faible']),
            'priority' => $this->faker->randomElement(['ouvert', 'fermé']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
