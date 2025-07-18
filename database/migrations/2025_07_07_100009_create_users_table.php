<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('info')->nullable();
            $table->enum('role', ['client', 'partenaire', 'livreur', 'livreur_entreprise', 'admin'])->default('client');
            $table->boolean('is_blocked')->default(false);
            
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->unsignedBigInteger('delivery_person_id')->nullable();
            $table->unsignedBigInteger('delivery_enterprise_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('set null');
            $table->foreign('delivery_person_id')->references('id')->on('delivery_persons')->onDelete('set null');
            $table->foreign('delivery_enterprise_id')->references('id')->on('delivery_enterprises')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
