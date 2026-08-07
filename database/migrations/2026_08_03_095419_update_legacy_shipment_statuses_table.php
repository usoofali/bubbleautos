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
        DB::table('orders')
            ->whereIn('status', ['booked', 'loaded', 'dispatched', 'telex_released'])
            ->update(['status' => 'in_transit']);

        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'delivered']);

        DB::table('orders')
            ->whereNotIn('status', ['pending', 'in_transit', 'delivered'])
            ->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
