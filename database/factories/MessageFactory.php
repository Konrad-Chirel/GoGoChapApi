<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Litige;
use App\Models\User;
use App\Models\Admin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        $senderType = $this->faker->randomElement(['user', 'admin']);

        return [
            'litige_id' => Litige::inRandomOrder()->first()?->id ?? Litige::factory(),
            'user_id' => $senderType === 'user' ? (User::inRandomOrder()->first()?->id ?? User::factory()) : null,
            'admin_id' => $senderType === 'admin' ? (Admin::inRandomOrder()->first()?->id ?? Admin::factory()) : null,
            'content' => $this->faker->paragraph,
            'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
