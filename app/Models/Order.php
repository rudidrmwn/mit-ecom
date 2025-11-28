<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['
        id_user, id_address, status, total_amount, discount_amount,tax_amount, final_amount
    '];
}
