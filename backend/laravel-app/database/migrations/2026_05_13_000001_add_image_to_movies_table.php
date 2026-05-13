<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movies', 'image')) {
            Schema::table('movies', function (Blueprint $table): void {
                $table->string('image', 2048)->nullable()->after('director');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movies', 'image')) {
            Schema::table('movies', function (Blueprint $table): void {
                $table->dropColumn('image');
            });
        }
    }
};
