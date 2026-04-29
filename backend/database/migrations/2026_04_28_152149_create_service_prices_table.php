<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create service_prices table for pricing management.
     */
    public function up(): void
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('service_type');
            $table->index('is_active');
        });

        // Seed default service prices
        Schema::disableForeignKeyConstraints();
        
        $servicePrices = [
            ['service_type' => 'regular_maintenance', 'name' => 'Service Berkala (Regular Maintenance)', 'base_price' => 500000, 'is_default' => true],
            ['service_type' => 'oil_change', 'name' => 'Ganti Oli (Oil Change)', 'base_price' => 350000, 'is_default' => true],
            ['service_type' => 'tire_service', 'name' => 'Ban & Velg (Tire & Wheel)', 'base_price' => 450000, 'is_default' => true],
            ['service_type' => 'brake_service', 'name' => 'Servis Rem (Brake Service)', 'base_price' => 600000, 'is_default' => true],
            ['service_type' => 'engine_diagnostic', 'name' => 'Diagnosa Mesin (Engine Diagnostic)', 'base_price' => 400000, 'is_default' => true],
            ['service_type' => 'transmission_service', 'name' => 'Servis Transmisi (Transmission Service)', 'base_price' => 800000, 'is_default' => true],
            ['service_type' => 'suspension_service', 'name' => 'Suspensi & Kemudi (Suspension & Steering)', 'base_price' => 700000, 'is_default' => true],
            ['service_type' => 'electrical_service', 'name' => 'Sistem Kelistrikan (Electrical System)', 'base_price' => 550000, 'is_default' => true],
            ['service_type' => 'ac_service', 'name' => 'Servis AC (Air Conditioning)', 'base_price' => 400000, 'is_default' => true],
            ['service_type' => 'body_repair', 'name' => 'Perbaikan Bodi (Body Repair)', 'base_price' => 1000000, 'is_default' => true],
            ['service_type' => 'paint_service', 'name' => 'Layanan Cat (Paint Service)', 'base_price' => 900000, 'is_default' => true],
            ['service_type' => 'interior_detailing', 'name' => 'Interior Detailing', 'base_price' => 350000, 'is_default' => true],
            ['service_type' => 'general_inspection', 'name' => 'Inspeksi Umum (General Inspection)', 'base_price' => 250000, 'is_default' => true],
            ['service_type' => 'custom_service', 'name' => 'Layanan Custom (Custom Service)', 'base_price' => 0, 'is_default' => false],
        ];

        DB::table('service_prices')->insert(array_map(function ($price) {
            return array_merge($price, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $servicePrices));

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
