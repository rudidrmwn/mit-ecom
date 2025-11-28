<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'id_seller',
        'name',
        'description',
        'logo',
        'address',
        'status'
    ];
}
