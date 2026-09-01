<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $fillable = [
        'user_id', 'contact_number', 'whatsapp_number', 
        'bio', 'specialty', 'languages_spoken', 
        'daily_rate', 'experience_years'
    ];

    protected $casts = [
        'languages_spoken' => 'array',
        'daily_rate' => 'decimal:2',
        'experience_years' => 'integer',
    ];

    // Belongs to one User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Many-to-Many Relationship with Locations (Pivot)
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'guide_location');
    }

    // Polymorphic Reviews
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Polymorphic Images (Profile pictures, ID cards, etc.)
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}