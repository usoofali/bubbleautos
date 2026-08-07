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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('make')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->integer('year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('make')->nullable(false)->change();
            $table->string('model')->nullable(false)->change();
            $table->integer('year')->nullable(false)->change();
        });
    }
};
