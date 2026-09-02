<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationLog extends Model
{
    protected $fillable = [
        'user_id', 
        'location_id', 
        'generated_bundle_ids', 
        'environmental_context'
    ];

    protected $casts = [
        // Automatically converts JSON to PHP Arrays when reading, and vice versa when saving
        'generated_bundle_ids' => 'array',
        'environmental_context' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}