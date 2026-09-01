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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Unified PostGIS Point
            $table->geography('coordinates', subtype: 'point', srid: 4326);
            $table->spatialIndex('coordinates'); // The key to fast searching

            $table->string('province');
            $table->string('district');

            $table->enum('location_type', [
                'mountain_trek',     // Ella Rock, Adam's Peak
                'waterfall',         // Bambarakanda, Diyaluma
                'tea_estate',        // Nuwara Eliya, Lipton's Seat
                'lake_reservoir',    // Gregory Lake, Parakrama Samudra
                'beach_coastal',     // Mirissa, Unawatuna, Nilaveli
                'wildlife_safari',   // Yala, Udawalawe, Minneriya
                'rainforest',        // Sinharaja, Kanneliya
                'ancient_ruins',     // Sigiriya, Polonnaruwa, Anuradhapura
                'religious_site',    // Temple of the Tooth, Nallur Kandaswamy
                'botanical_garden',  // Peradeniya, Hakgala
                'urban_city',        // Colombo, Kandy City, Galle Fort
                'campsite',          // Haritha Kanda, Narangala, Wangedigala
                'village_getaway'    // Meemure, Mandaramnuwara, Ulapane
            ]);
            
            // ML Contextual & Terrain Variables (For Random Forest)
            $table->enum('terrain_difficulty', ['easy', 'moderate', 'challenging', 'extreme'])->default('moderate');
            $table->boolean('requires_4x4')->default(false);      // Vehicle bundle constraint
            $table->boolean('requires_guide')->default(false);    // Guide bundle constraint
            $table->integer('elevation_meters')->nullable();      // Weather/temperature correlation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
