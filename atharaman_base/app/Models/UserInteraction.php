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

    // Polymorphic relationship (e.g., viewed hidden contact no, viewed hidden email, clicked WhatsApp, bookmarked a favourite service)
    public function interactable()
    {
        return $this->morphTo();
    }
}