<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('litiges', function (Blueprint $table) {
            $table->id(); // Laravel par défaut => colonne "id"
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description');
            $table->enum('status', ['urgent', 'moyen', 'faible']);
            $table->enum('priority', ['ouvert', 'fermé']);
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('litiges');
    }
};
