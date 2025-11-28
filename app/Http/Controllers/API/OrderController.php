<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function generateOrderNumber2($prefix = 'INV') {
        $date = date('Ymd'); // Format: 20241128
        $random = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . '-' . $date . '-' . $random;
    }
}
