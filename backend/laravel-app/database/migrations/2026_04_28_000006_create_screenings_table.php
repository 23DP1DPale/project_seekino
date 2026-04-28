<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screenings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id', true);
            $table->date('screening_date');
            $table->time('screening_time');
            $table->decimal('cost', 8, 2);
            $table->bigInteger('hall');
            $table->bigInteger('movie');

            $table->foreign('hall')->references('id')->on('halls');
            $table->foreign('movie')->references('id')->on('movies');
            $table->unique(['screening_date', 'screening_time', 'hall']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
