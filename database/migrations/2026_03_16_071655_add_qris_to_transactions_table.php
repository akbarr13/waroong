<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash', 'debt', 'qris') DEFAULT 'cash'");

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_proof');
        });

        DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash', 'debt') DEFAULT 'cash'");
    }
};
