<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourcingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_phone',
        'reference_number',
        'target_budget',
        'status',
    ];
}
