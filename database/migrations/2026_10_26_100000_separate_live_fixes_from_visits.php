<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_locations', function (Blueprint $t) {
            $t->timestamp('last_seen_at')->nullable();
            $t->string('coverage_status', 20)->default('unknown');
        });
        Schema::create('location_fixes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->uuid('client_fix_id');
            $t->foreignId('visit_id')->nullable()->constrained('user_locations')->nullOnDelete();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->float('accuracy_meters')->nullable();
            $t->text('full_address')->nullable();
            foreach (['place', 'street', 'city', 'district', 'state', 'country', 'device_name'] as $f) {
                $t->string($f)->nullable();
            }
            $t->string('postal_code', 20)->nullable();
            $t->timestamp('captured_at', 6);
            $t->timestamp('received_at', 6);
            $t->boolean('record_visit')->default(false);
            $t->string('quality_status', 20);
            $t->string('address_status', 20);
            $t->timestamps();
            $t->unique(['user_id', 'client_fix_id']);
            $t->index(['user_id', 'captured_at', 'id']);
            $t->index(['user_id', 'record_visit', 'captured_at', 'id'], 'location_fixes_visit_order');
        });
        DB::table('user_locations')->orderBy('id')->chunkById(250, function ($rows) {
            foreach ($rows as $row) {
                $data = (array) $row;
                unset($data['id'],$data['stay_seconds'],$data['last_seen_at'],$data['coverage_status']);
                $data['client_fix_id'] = $row->client_fix_id ?: (string) Str::uuid();
                $data['visit_id'] = $row->id;
                $data['received_at'] = $row->created_at ?: $row->captured_at;
                $data['record_visit'] = true;
                $data['quality_status'] = 'legacy';
                $data['address_status'] = 'legacy';
                DB::table('location_fixes')->insert($data);
                DB::table('user_locations')->where('id', $row->id)->update(['last_seen_at' => $row->captured_at]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_fixes');
        Schema::table('user_locations', fn (Blueprint $t) => $t->dropColumn(['last_seen_at', 'coverage_status']));
    }
};
