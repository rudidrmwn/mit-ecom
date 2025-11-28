<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    // Helper method untuk hitung total produk
    public function getTotalProductsAttribute(): int
    {
        return $this->products()->count();
    }
}
