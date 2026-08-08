<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('invoices')->where('status', 'unpaid')->update(['status' => 'pending']);
        DB::table('invoices')->where('status', 'partial')->update(['status' => 'partially_paid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invoices')->where('status', 'pending')->update(['status' => 'unpaid']);
        DB::table('invoices')->where('status', 'partially_paid')->update(['status' => 'partial']);
    }
};
