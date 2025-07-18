<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // facultatif si admin aussi
            $table->string('type'); // financier, statistique...
            $table->string('format'); // pdf, excel
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamp('generated_at')->useCurrent(); // date de génération
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
