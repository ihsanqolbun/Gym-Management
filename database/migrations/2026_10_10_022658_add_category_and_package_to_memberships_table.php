<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->enum('category', ['pelajar', 'reguler', 'dewasa', 'private_siang', 'private_malam'])
                ->nullable()
                ->after('type');
            $table->enum('package', ['visit_1x', 'visit_10x', 'all_day_month'])
                ->nullable()
                ->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn(['category', 'package']);
        });
    }
};