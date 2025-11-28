<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductAttribute extends Model
{
    protected $fillable = [
        'id_product',
        'type',
        'name',
        'image',
        'path',
        'price_adjustment',
        'qty'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'qty' => 'decimal:2',
    ];

    // Accessor untuk mendapatkan full URL
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        return Storage::disk('public')->url($this->image);
    }
}