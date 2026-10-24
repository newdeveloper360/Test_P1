<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        Schema::create('advice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 120);
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->string('admin_note', 2000)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('advice');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_active'));
    }
};
