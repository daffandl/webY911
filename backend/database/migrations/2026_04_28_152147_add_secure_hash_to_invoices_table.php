<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add secure_hash column to invoices table for HMAC verification.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('secure_hash', 64)->nullable()->after('total')->comment('HMAC-SHA256 hash for verification');
        });
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('secure_hash');
        });
    }
};
