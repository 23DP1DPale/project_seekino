<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations_seats', function (Blueprint $table) {
            $table->index('seat', 'reservations_seats_seat_index');
            $table->index('screening', 'reservations_seats_screening_index');
        });

        Schema::table('reservations_seats', function (Blueprint $table) {
            $table->dropUnique(['seat', 'screening']);
        });
    }

    public function down(): void
    {
        Schema::table('reservations_seats', function (Blueprint $table) {
            $table->unique(['seat', 'screening']);
        });

        Schema::table('reservations_seats', function (Blueprint $table) {
            $table->dropIndex('reservations_seats_seat_index');
            $table->dropIndex('reservations_seats_screening_index');
        });
    }
};
