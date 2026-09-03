<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'model',
        'reference',
        'sku',
        'condition',
        'production_year',
        'case_size',
        'case_material',
        'movement',
        'box_papers',
        'price',
        'currency',
        'availability',
        'image_url',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
