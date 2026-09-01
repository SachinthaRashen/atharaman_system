<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInteraction extends Model
{
    protected $fillable = [
        'user_id', 
        'interactable_id', 
        'interactable_type', 
        'interaction_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relationship (e.g., clicked WhatsApp on a Guide, bookmarked a Hotel)
    public function interactable()
    {
        return $this->morphTo();
    }
}