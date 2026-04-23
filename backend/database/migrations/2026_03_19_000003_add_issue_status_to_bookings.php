<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'issue' status support to bookings table.
     * The status column is already a varchar(50), so no structural change is needed.
     * This migration serves as documentation that 'issue' is a valid status value.
     *
     * Valid statuses: pending, confirmed, rejected, in_progress, issue, completed, cancelled
     */
    public function up(): void
    {
        // The status column is varchar(50) — no DDL change needed.
        // This migration documents the addition of the 'issue' status.
        // If you use an ENUM column, alter it here instead.
    }

    public function down(): void
    {
        // No structural change to revert.
    }
};
