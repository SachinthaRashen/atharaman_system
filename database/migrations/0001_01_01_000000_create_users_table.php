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

            // Static Demographics & Cold-Start Persona Attributes (For K-Means)
            $table->string('country')->nullable();              // e.g., 'Sri Lanka', 'Germany'
            $table->string('native_language')->default('English'); // Guide language matching
            $table->enum('preferred_travel_style', [
                'adventure', 
                'cultural', 
                'nature', 
                'leisure'
            ])->nullable();
            $table->enum('preferred_budget_tier', [
                'budget', 
                'mid_range', 
                'luxury'
            ])->nullable();
            
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
