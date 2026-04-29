<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add service_price column to bookings table for anti-fraud tracking.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('service_price', 15, 2)->nullable()->after('service_type')->comment('Service price for anti-fraud tracking');
        });
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('service_price');
        });
    }
};
