<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\DeliveryPerson;
use App\Models\Order;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // On choisit aléatoirement le type d'utilisateur
        $userTypes = ['admin' => Admin::class, 'client' => Customer::class, 'entreprise' => Restaurant::class, 'livreur' => DeliveryPerson::class];
        $userType = $this->faker->randomElement(array_keys($userTypes));
        $userModel = $userTypes[$userType];

        // On choisit un ID valide à partir du modèle correspondant
        $userId = $userModel::inRandomOrder()->first()?->id;

        return [
            'user_id' => $userId,
            'user_type' => $userType,
            'type' => $this->faker->randomElement(['commande', 'alerte', 'note', 'plaintes']),
            'message' => $this->faker->sentence,
            'order_id' => Order::inRandomOrder()->first()?->id,
            'read' => $this->faker->boolean(30),
        ];
    }
}
