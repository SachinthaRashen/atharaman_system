<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'user_id', 'shop_name', 'address',
        'contact_number', 'whatsapp_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A shop owns multiple items
    public function items()
    {
        return $this->hasMany(ShopItem::class);
    }

    // For shop logo/storefront images
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}