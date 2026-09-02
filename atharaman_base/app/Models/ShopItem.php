<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    protected $fillable = [
        'shop_id', 'item_name', 'item_category',
        'description', 'rental_price_per_day', 'stock_quantity'
    ];

    protected $casts = [
        'rental_price_per_day' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Product specific images
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}