<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create service_items table — catalog of spare parts and services with prices.
     */
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Nama sparepart/jasa
            $table->enum('type', ['sparepart', 'jasa']);     // Tipe item
            $table->text('description')->nullable();         // Deskripsi opsional
            $table->decimal('price', 15, 2)->default(0);    // Harga satuan
            $table->string('unit', 50)->nullable();          // Satuan: pcs, liter, jam, dll
            $table->boolean('is_active')->default(true);     // Aktif/nonaktif di katalog
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number', 50)->unique();  // INV-YYYYMMDD-NNN
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);   // PPN %
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');                          // Nama item (copied or custom)
            $table->enum('type', ['sparepart', 'jasa'])->default('jasa');
            $table->text('description')->nullable();
            $table->decimal('qty', 10, 2)->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);  // qty * unit_price
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('service_items');
    }
};
