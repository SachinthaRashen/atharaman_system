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
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');

            // Unified PostGIS Point
            $table->geography('coordinates', subtype: 'point', srid: 4326);
            $table->spatialIndex('coordinates'); // The key to fast searching
            
            $table->string('contact_number');
            $table->string('whatsapp_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialty');                         // e.g., 'Trekking', 'Bird Watching'
            $table->json('languages_spoken');                     // JSON array: ["English", "German", "Sinhala"]
            $table->decimal('daily_rate', 10, 2);                // Budget calculation
            $table->integer('experience_years')->default(1);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
