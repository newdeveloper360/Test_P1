<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('invite_code', 6)->nullable()->unique()->after('is_blocked');
            $table->foreignId('invited_by_user_id')->nullable()->after('invite_code')
                ->constrained('users')->nullOnDelete();
        });

        foreach (DB::table('users')->where('role', 'user')->pluck('id') as $id) {
            do {
                $code = (string) random_int(100000, 999999);
            } while (DB::table('users')->where('invite_code', $code)->exists());
            DB::table('users')->where('id', $id)->update(['invite_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by_user_id');
            $table->dropUnique(['invite_code']);
            $table->dropColumn('invite_code');
        });
    }
};
