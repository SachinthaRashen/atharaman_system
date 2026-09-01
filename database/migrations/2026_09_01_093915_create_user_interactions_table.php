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
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Polymorphic Columns: interactable_id & interactable_type
            $table->morphs('interactable'); 
            
            $table->enum('interaction_type', [
                'bookmarked', 
                'whatsapp_clicked', 
                'phone_revealed', 
                'email_clicked'
            ]);
            $table->timestamps();

            // Prevent duplicate spam from polluting ML training matrices
            $table->unique(['user_id', 'interactable_id', 'interactable_type', 'interaction_type'], 'user_interaction_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interactions');
    }
};
