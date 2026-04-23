<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes to bookings table for better query performance.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Index for status filtering (frequently used in filters and stats)
            $table->index('status', 'bookings_status_index');

            // Index for payment_status filtering (used in revenue calculations)
            $table->index('payment_status', 'bookings_payment_status_index');

            // Index for preferred_date sorting and filtering
            $table->index('preferred_date', 'bookings_preferred_date_index');

            // Index for created_at (used in today/this week/this month queries)
            $table->index('created_at', 'bookings_created_at_index');

            // Composite index for common query patterns
            $table->index(['status', 'created_at'], 'bookings_status_created_at_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Index for status filtering
            $table->index('status', 'invoices_status_index');

            // Index for issued_at sorting
            $table->index('issued_at', 'invoices_issued_at_index');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            // Index for invoice relationship queries
            $table->index('invoice_id', 'invoice_items_invoice_id_index');

            // Index for service_item relationship queries
            $table->index('service_item_id', 'invoice_items_service_item_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_status_index');
            $table->dropIndex('bookings_payment_status_index');
            $table->dropIndex('bookings_preferred_date_index');
            $table->dropIndex('bookings_created_at_index');
            $table->dropIndex('bookings_status_created_at_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_status_index');
            $table->dropIndex('invoices_issued_at_index');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_invoice_id_index');
            $table->dropIndex('invoice_items_service_item_id_index');
        });
    }
};
