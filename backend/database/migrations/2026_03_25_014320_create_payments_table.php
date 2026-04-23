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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->string('transaction_id')->unique();
            $table->string('payment_method')->nullable(); // bank_transfer, credit_card, gopay, etc.
            $table->string('payment_type')->nullable(); // qris, va, card, etc.
            $table->string('bank')->nullable(); // bca, mandiri, bni, etc.
            $table->string('va_number')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, success, failed, refunded
            $table->string('midtrans_status')->nullable(); // capture, settle, expire, cancel, refund
            $table->string('gross_amount')->nullable();
            $table->string('currency')->default('IDR');
            $table->text('payment_response')->nullable(); // JSON response from Midtrans
            $table->string('fraud_status')->nullable(); // accept, challenge, deny
            $table->string('status_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
