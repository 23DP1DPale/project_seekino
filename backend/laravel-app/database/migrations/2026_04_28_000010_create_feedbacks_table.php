<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id', true);
            $table->decimal('rating', 2, 1);
            $table->text('comment');
            $table->dateTime('created_at')->useCurrent();
            $table->bigInteger('movie');
            $table->bigInteger('user');

            $table->foreign('movie')->references('id')->on('movies')->cascadeOnDelete();
            $table->foreign('user')->references('id')->on('users')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE feedbacks ADD CONSTRAINT feedbacks_rating_check CHECK (rating >= 1.0 AND rating <= 5.0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
