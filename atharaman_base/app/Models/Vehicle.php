<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id', 'vehicle_make_model', 'registration_number',
        'rental_type', 'driver_name', 'contact_number', 'whatsapp_number',
        'vehicle_type', 'terrain_capability', 'pricing_model',
        'rate_per_day', 'rate_per_km', 'passenger_capacity'
    ];

    protected $casts = [
        'rate_per_day' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'passenger_capacity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}