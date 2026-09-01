<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'user_id', 'hotel_name', 'description', 'address',
        'contact_number', 'whatsapp_number', 'email',
        'budget_tier', 'pricing_model', 'base_price',
        'max_total_capacity', 'is_wheelchair_accessible'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'max_total_capacity' => 'integer',
        'is_wheelchair_accessible' => 'boolean',
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