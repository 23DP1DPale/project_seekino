<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id', true);
            $table->dateTime('reservation_date')->useCurrent();
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->dateTime('expiration_date');
            $table->bigInteger('user');
            $table->bigInteger('screening');

            $table->foreign('user')->references('id')->on('users');
            $table->foreign('screening')->references('id')->on('screenings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
