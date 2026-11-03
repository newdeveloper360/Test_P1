<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where(function ($query) {
                $query->whereNull('android_version_code')
                    ->orWhere('android_version_code', '<', 5);
            })
            ->update(['android_version_code' => 5]);
    }

    public function down(): void
    {
        // A rollback must not prompt users to install an older APK.
    }
};
