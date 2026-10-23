<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('stay_seconds')->nullable()->after('captured_at');
        });

        $previous = null;
        foreach (DB::table('user_locations')->orderBy('user_id')
            ->orderBy('captured_at')->orderBy('id')
            ->get(['id', 'user_id', 'captured_at']) as $location) {
            if ($previous && $previous->user_id === $location->user_id) {
                DB::table('user_locations')->where('id', $previous->id)->update([
                    'stay_seconds' => max(0,
                        strtotime($location->captured_at) - strtotime($previous->captured_at)),
                ]);
            }
            $previous = $location;
        }
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropColumn('stay_seconds');
        });
    }
};
