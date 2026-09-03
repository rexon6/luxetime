<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Brand
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 2. Tabel Produk Jam Tangan (Inventory)
        Schema::create('watch_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('model');
            $table->string('reference');
            $table->string('sku')->unique()->nullable();
            $table->string('condition');
            $table->integer('production_year')->nullable();
            $table->string('case_size')->nullable();
            $table->string('case_material')->nullable();
            $table->string('movement')->nullable();
            $table->string('box_papers')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->enum('availability', [
                'AVAILABLE', 
                'RESERVED', 
                'SOLD', 
                'SOURCED', 
                'ARCHIVED'
            ])->default('AVAILABLE');

            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Penjualan / Valuasi dari Customer (Get My Offer)
        Schema::create('sell_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone');
            $table->string('brand_name');
            $table->string('model_reference');
            $table->enum('sale_type', ['DIRECT_SELL', 'CONSIGNMENT', 'TRADE_IN']);
            $table->string('box_papers')->nullable();
            $table->string('expectation_price')->nullable();
            $table->enum('status', [
                'PENDING', 
                'REVIEWING', 
                'OFFER_SENT', 
                'ACCEPTED', 
                'REJECTED'
            ])->default('PENDING');
            $table->timestamps();
        });

        // 4. Tabel LuxeSource (Permintaan Sourcing Jam Langka)
        Schema::create('sourcing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone');
            $table->string('reference_number');
            $table->string('target_budget')->nullable();
            $table->enum('status', ['PENDING', 'SEARCHING', 'FOUND', 'CANCELLED'])->default('PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sourcing_requests');
        Schema::dropIfExists('sell_requests');
        Schema::dropIfExists('watch_products');
        Schema::dropIfExists('brands');
    }
};
