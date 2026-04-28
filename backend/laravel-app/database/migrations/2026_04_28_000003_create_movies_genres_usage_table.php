<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies_genres_usage', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id', true);
            $table->bigInteger('movie');
            $table->bigInteger('genre');
            $table->boolean('primary_genre')->default(false);

            $table->foreign('movie')->references('id')->on('movies')->cascadeOnDelete();
            $table->foreign('genre')->references('id')->on('genres')->cascadeOnDelete();
            $table->unique(['movie', 'genre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies_genres_usage');
    }
};
