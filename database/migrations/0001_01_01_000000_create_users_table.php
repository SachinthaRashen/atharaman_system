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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role-Based Access Control
            $table->enum('role', [
                'tourist', 
                'guide', 
                'hotel_owner', 
                'shop_owner', 
                'vehicle_owner', 
                'admin'
            ])->default('tourist');

            // Private Admin Verification Data (Nullable for normal tourists)
            $table->string('phone_number')->nullable();
            $table->string('nic_passport_number')->nullable();
            $table->text('residential_address')->nullable();

            // Static Demographics & Cold-Start Persona Attributes (For K-Means)
            $table->date('date_of_birth')->nullable();
            $table->string('country')->nullable();              // e.g., 'Sri Lanka', 'Germany'
            $table->boolean('prefers_guided_tours')->nullable();
            $table->boolean('requires_accessibility')->default(false);
            $table->string('native_language')->default('English'); // Guide language matching
            $table->enum('preferred_travel_style', [
                'adventure',        // Surfing, mountain hiking, extreme sports
                'cultural_historic',// Ancient cities, temples, colonial forts
                'nature_wildlife',  // Safaris, rainforests, botanical gardens
                'leisure_wellness'  // Beaches, Ayurveda retreats, luxury resorts
            ])->nullable();
            $table->enum('preferred_budget_tier', [
                'budget', 
                'mid_range', 
                'luxury'
            ])->nullable();

            $table->unsignedInteger('ml_cluster_id')->nullable()->index();
            
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
