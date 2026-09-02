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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Unified PostGIS Point
            $table->geography('coordinates', subtype: 'point', srid: 4326);
            $table->spatialIndex('coordinates'); // The key to fast searching

            $table->string('vehicle_make_model'); // e.g., "Honda Dio", "Toyota Hiace", "Bajaj RE"
            $table->string('registration_number')->unique();
            $table->enum('rental_type', ['self_drive', 'with_driver'])->default('with_driver');
            $table->string('driver_name')->nullable();
            $table->string('contact_number');
            $table->string('whatsapp_number')->nullable();
            
            // ML Terrain & Capacity Constraints
            $table->enum('vehicle_type', [
                'scooter',          // Highly popular for beach towns
                'motorbike', 
                'tuk_tuk',          // Iconic self-drive option
                'sedan_car', 
                'safari_jeep',
                'suv_4x4', 
                'passenger_van', 
                'mini_bus'          // For large groups
            ]);
            $table->enum('terrain_capability', ['standard_road', 'off_road_4x4'])->default('standard_road');
            $table->enum('pricing_model', ['per_day', 'per_km', 'both'])->default('per_day');
            $table->decimal('rate_per_day', 10, 2)->nullable();
            $table->decimal('rate_per_km', 8, 2)->nullable();
            $table->integer('passenger_capacity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
