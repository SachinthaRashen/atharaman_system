<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_id',
        'reviewable_type',
        'rating',
        'comment'
    ];

    // Links back to the User who wrote it
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relationship (Can be a Hotel, Guide, Location, etc.)
    public function reviewable()
    {
        return $this->morphTo();
    }
}