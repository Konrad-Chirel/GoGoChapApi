<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone_number', 20);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('files');
            $table->decimal('solde', 10, 2)->default(0);
            $table->string('position');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->foreignId('delivery_enterprise_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_persons');
    }
};
