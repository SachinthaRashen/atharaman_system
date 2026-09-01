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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Unified PostGIS Point
            $table->geography('coordinates', subtype: 'point', srid: 4326);
            $table->spatialIndex('coordinates'); // The key to fast searching

            $table->string('hotel_name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('contact_number');
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            
            // ML Bundle Constraints
            $table->enum('budget_tier', ['budget', 'mid_range', 'luxury']);
            // Is the price calculated per room or per person?
            $table->enum('pricing_model', ['per_room', 'per_person'])->default('per_person');
            // The starting price for that unit
            $table->decimal('base_price', 10, 2); 
            // The total maximum humans the entire property can hold
            $table->integer('max_total_capacity');
            $table->boolean('is_wheelchair_accessible')->default(false); // Accessibility filter
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
