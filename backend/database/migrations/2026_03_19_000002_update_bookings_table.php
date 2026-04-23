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
        Schema::table('bookings', function (Blueprint $table) {
            // Add booking_code (unique identifier like YNG-20240321-001)
            $table->string('booking_code', 30)->unique()->nullable()->after('id');

            // Add vehicle_info (detailed vehicle info)
            $table->string('vehicle_info', 500)->nullable()->after('car_model');

            // Add scheduled_at (datetime for scheduled service)
            $table->dateTime('scheduled_at')->nullable()->after('preferred_date');

            // Add admin_notes (optional notes from admin, e.g. rejection reason)
            $table->text('admin_notes')->nullable()->after('notes');

            // Update status to support 'rejected'
            // We change the column to allow 'rejected' value
            $table->string('status', 50)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_code', 'vehicle_info', 'scheduled_at', 'admin_notes']);
        });
    }
};
