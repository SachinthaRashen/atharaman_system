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
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('item_name');
            $table->enum('item_category', [
                'camping_gear',     // Tents, sleeping bags, stoves
                'hiking_trekking',  // Boots, walking sticks, backpacks
                'water_sports',     // Surfboards, snorkeling gear
                'general_travel'    // Power banks, umbrellas
            ]);
            $table->text('description')->nullable();
            $table->decimal('rental_price_per_day', 8, 2);
            $table->integer('stock_quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};
