<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name', 'description', 'province', 'district', 
        'location_type', 'terrain_difficulty', 
        'requires_4x4', 'requires_guide', 'elevation_meters'
    ];

    protected $casts = [
        'requires_4x4' => 'boolean',
        'requires_guide' => 'boolean',
        'elevation_meters' => 'integer',
    ];

    // Many-to-Many Relationship with Guides (The Pivot Table)
    public function guides()
    {
        return $this->belongsToMany(Guide::class, 'guide_location');
    }

    // Polymorphic Relationships
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}