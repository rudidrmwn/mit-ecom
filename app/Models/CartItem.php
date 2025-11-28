<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'id_cart',
        'id_product',
        'id_store',
        'id_product_attribute',
        'product_name', 
        'product_attribute_name',
        'qty'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'id_cart');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'id_product_attribute');
    }
}