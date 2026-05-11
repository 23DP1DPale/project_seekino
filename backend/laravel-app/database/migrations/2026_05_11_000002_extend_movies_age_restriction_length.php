<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE movies MODIFY age_restriction VARCHAR(50) NOT NULL DEFAULT 'Bez ierobežojuma'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE movies MODIFY age_restriction VARCHAR(10) NOT NULL DEFAULT 'U'");
    }
};
