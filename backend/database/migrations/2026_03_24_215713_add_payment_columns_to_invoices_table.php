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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_token')->nullable()->after('invoice_number');
            $table->string('transaction_id')->nullable()->after('payment_token');
            $table->string('payment_url')->nullable()->after('transaction_id');
            $table->string('payment_method')->nullable()->after('payment_url');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('paid_at');
            
            // Add index for payment lookups
            $table->index('payment_token');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_token']);
            $table->dropIndex(['transaction_id']);
            $table->dropColumn([
                'payment_token',
                'transaction_id',
                'payment_url',
                'payment_method',
                'paid_at',
                'paid_amount',
            ]);
        });
    }
};
