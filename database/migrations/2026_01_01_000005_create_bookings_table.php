<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('class_schedule_id')
                ->constrained('class_schedules')
                ->cascadeOnDelete();
            $table->date('booking_date');
            $table->enum('status', ['booked', 'cancelled', 'attended'])->default('booked');
            $table->timestamp('checked_in_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            // cegah double booking: 1 user gak bisa booking kelas & tanggal yang sama 2x
            $table->unique(['user_id', 'class_schedule_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
