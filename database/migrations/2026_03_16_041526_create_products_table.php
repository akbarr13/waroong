<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("category_id")
                ->constrained("categories")
                ->cascadeOnDelete();
            $table->string("name");
            $table->string("sku")->nullable();
            $table->integer("purchase_price");
            $table->integer("selling_price");
            $table->integer("stock")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("products");
    }
};
