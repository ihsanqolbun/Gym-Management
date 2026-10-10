<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('private_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('coach_id')->nullable()->constrained('coaches')->nullOnDelete();
        $table->date('session_date');
        $table->time('start_time');
        $table->enum('time_slot', ['private_siang', 'private_malam']);
        $table->enum('location_type', ['gym', 'rumah'])->default('gym');
        $table->string('address')->nullable();
        $table->unsignedInteger('price');
        $table->enum('status', ['requested', 'approved', 'rejected', 'cancelled'])->default('requested');
        $table->string('admin_note')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('private_sessions');
}
};
