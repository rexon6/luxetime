<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'brand_name',
        'model_reference',
        'sale_type',
        'box_papers',
        'expectation_price',
        'status',
    ];
}
