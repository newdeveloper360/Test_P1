<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL/MariaDB may give the first TIMESTAMP column an implicit
        // ON UPDATE CURRENT_TIMESTAMP rule. A location fix must never move in time.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("SET time_zone = '+00:00'");
        }
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dateTime('captured_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->timestamp('captured_at')->change();
        });
    }
};
