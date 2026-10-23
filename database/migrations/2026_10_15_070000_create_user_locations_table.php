<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_fix_id')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy_meters')->nullable();
            $table->text('full_address')->nullable();
            $table->string('place')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();
            $table->index(['user_id', 'captured_at']);
            $table->unique(['user_id', 'client_fix_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
