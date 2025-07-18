<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_enterprises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address', 255);
            $table->string('phone_number', 20);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('logo')->nullable(); // logo de l'entreprise de livraison
            $table->decimal('solde', 10, 2)->default(0);
            $table->integer('numero_IFU');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_enterprises');
    }
};
