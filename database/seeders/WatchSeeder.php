<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\WatchProduct;

class WatchSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Brands
        $rolex = Brand::create(['name' => 'Rolex', 'slug' => 'rolex']);
        $patek = Brand::create(['name' => 'Patek Philippe', 'slug' => 'patek-philippe']);
        $ap    = Brand::create(['name' => 'Audemars Piguet', 'slug' => 'audemars-piguet']);
        $rm    = Brand::create(['name' => 'Richard Mille', 'slug' => 'richard-mille']);

        // 2. Seed Watch Products
        WatchProduct::create([
            'brand_id'        => $rolex->id,
            'model'           => "Submariner Date 'Kermit'",
            'reference'       => '126610LV',
            'sku'             => 'RLX-126610LV-2024',
            'condition'       => 'Unworn / New',
            'production_year' => 2024,
            'case_size'       => '41 mm',
            'case_material'   => 'Oystersteel',
            'movement'        => 'Automatic Caliber 3235',
            'box_papers'      => 'Full Set (Box & Card)',
            'price'           => 235000000.00,
            'currency'        => 'IDR',
            'availability'    => 'AVAILABLE',
            'image_url'       => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop',
        ]);

        WatchProduct::create([
            'brand_id'        => $patek->id,
            'model'           => 'Nautilus Blue Dial',
            'reference'       => '5711/1A-010',
            'sku'             => 'PP-5711-1A',
            'condition'       => 'Mint / Like New',
            'production_year' => 2021,
            'case_size'       => '40 mm',
            'case_material'   => 'Stainless Steel',
            'movement'        => 'Automatic Caliber 26-330 S C',
            'box_papers'      => 'Full Set',
            'price'           => 1450000000.00,
            'currency'        => 'IDR',
            'availability'    => 'SOURCED',
            'image_url'       => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=800&auto=format&fit=crop',
        ]);

        WatchProduct::create([
            'brand_id'        => $ap->id,
            'model'           => 'Royal Oak Chronograph',
            'reference'       => '26331ST.OO.1220ST.01',
            'sku'             => 'AP-26331ST-2022',
            'condition'       => 'Mint / Excellent',
            'production_year' => 2022,
            'case_size'       => '41 mm',
            'case_material'   => 'Stainless Steel',
            'movement'        => 'Automatic Caliber 2385',
            'box_papers'      => 'Full Set',
            'price'           => 680000000.00,
            'currency'        => 'IDR',
            'availability'    => 'RESERVED',
            'image_url'       => 'https://images.unsplash.com/photo-1508656966624-913217036495?q=80&w=800&auto=format&fit=crop',
        ]);

        WatchProduct::create([
            'brand_id'        => $rm->id,
            'model'           => 'RM 011 Felipe Massa',
            'reference'       => 'RM011-FM',
            'sku'             => 'RM-011FM-2019',
            'condition'       => 'Very Good',
            'production_year' => 2019,
            'case_size'       => '50 x 40 mm',
            'case_material'   => 'Titanium',
            'movement'        => 'Automatic Skeletonized',
            'box_papers'      => 'Watch & Papers Only',
            'price'           => null,
            'currency'        => 'IDR',
            'availability'    => 'AVAILABLE',
            'image_url'       => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?q=80&w=800&auto=format&fit=crop',
        ]);
    }
}
