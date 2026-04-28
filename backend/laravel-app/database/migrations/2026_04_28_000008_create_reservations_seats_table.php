<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations_seats', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id', true);
            $table->bigInteger('reservation');
            $table->bigInteger('seat');
            $table->bigInteger('screening');

            $table->foreign('reservation')->references('id')->on('reservations')->cascadeOnDelete();
            $table->foreign('seat')->references('id')->on('seats');
            $table->foreign('screening')->references('id')->on('screenings');
            $table->unique(['seat', 'screening']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations_seats');
    }
};
