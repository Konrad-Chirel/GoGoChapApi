<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Restaurant::factory(10)->create();
        \App\Models\DeliveryEnterprise::factory(5)->create();
        \App\Models\DeliveryPerson::factory(15)->create();
        \App\Models\Customer::factory(20)->create();
        \App\Models\Admin::factory(3)->create();

        \App\Models\Meal::factory(50)->create();
        \App\Models\Order::factory(40)->create();
        \App\Models\OrderItem::factory(100)->create();
        \App\Models\Notification::factory(30)->create();
        \App\Models\Litige::factory(10)->create();
        \App\Models\Message::factory(20)->create();
    }
}
