<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchProduct;
use App\Models\Brand;
use App\Models\SellRequest;
use App\Models\SourcingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WatchCatalogController extends Controller
{
    // Fetch Katalog Jam Tangan + Search & Filter
    public function index(Request $request)
    {
        $query = WatchProduct::with('brand');

        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', Str::slug($request->brand))
                  ->orWhere('name', 'LIKE', "%{$request->brand}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('availability')) {
            $query->where('availability', $request->availability);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->latest()->get());
    }

    // Detail Produk Single (PDP)
    public function show($id)
    {
        $watch = WatchProduct::with('brand')->find($id);

        if (!$watch) {
            return response()->json(['message' => 'Watch not found'], 404);
        }

        return response()->json($watch);
    }

    // Simpan Form Sell Offer dari Customer
    public function storeSellOffer(Request $request)
    {
        $validated = $request->validate([
            'brand_name'       => 'required|string',
            'model_reference'  => 'required|string',
            'sale_type'        => 'required|in:DIRECT_SELL,CONSIGNMENT,TRADE_IN',
            'box_papers'       => 'nullable|string',
            'expectation_price'=> 'nullable|numeric',
            'customer_phone'   => 'required|string',
        ]);

        $offer = SellRequest::create($validated);

        return response()->json(['message' => 'Sell request submitted successfully', 'data' => $offer], 201);
    }

    // Simpan Form Sourcing Request dari Customer
    public function storeSourcingRequest(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => 'required|string',
            'target_budget'    => 'nullable|numeric',
            'customer_phone'   => 'required|string',
        ]);

        $sourcing = SourcingRequest::create($validated);

        return response()->json(['message' => 'Sourcing request submitted successfully', 'data' => $sourcing], 201);
    }

    // Admin: Login Verification
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($credentials['username'] === 'admin' && $credentials['password'] === 'admin123') {
            return response()->json([
                'success' => true,
                'message' => 'Login Admin Berhasil!',
                'token'   => bin2hex(random_bytes(16)),
                'admin'   => ['username' => 'admin', 'email' => 'admin@luxetime.com', 'role' => 'Administrator']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau Password Admin salah!'
        ], 401);
    }

    // Admin: Upload / Create New Watch Product (Supports File Upload & URL)
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'brand_name'      => 'required|string',
            'model'           => 'required|string',
            'reference'       => 'required|string',
            'sku'             => 'nullable|string',
            'condition'       => 'required|string',
            'production_year' => 'nullable|integer',
            'case_size'       => 'nullable|string',
            'case_material'   => 'nullable|string',
            'movement'        => 'nullable|string',
            'box_papers'      => 'nullable|string',
            'price'           => 'nullable|numeric',
            'currency'        => 'nullable|string',
            'availability'    => 'required|in:AVAILABLE,RESERVED,SOLD,SOURCED,ARCHIVED',
            'image_url'       => 'nullable|string',
            'image_file'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        // Handle File Upload if file is provided
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $cleanModel = Str::slug($validated['model']) ?: 'watch';
            $filename = time() . '_' . $cleanModel . '_' . Str::random(5) . '.' . $ext;
            $destinationPath = public_path('uploads/watches');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);
            $imageUrl = '/uploads/watches/' . $filename;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop';
        }

        // Case-insensitive & slug-based brand resolution to prevent unique constraint violation
        $rawBrandName = trim($validated['brand_name']);
        $brandSlug = Str::slug($rawBrandName);

        $brand = Brand::where('slug', $brandSlug)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($rawBrandName)])
            ->first();

        if (!$brand) {
            $brand = Brand::create([
                'name' => ucwords($rawBrandName),
                'slug' => $brandSlug ?: Str::random(8),
            ]);
        }

        $productData = $validated;
        unset($productData['brand_name'], $productData['image_file']);
        $productData['brand_id'] = $brand->id;
        $productData['image_url'] = $imageUrl;
        if (empty($productData['currency'])) {
            $productData['currency'] = 'IDR';
        }

        // Auto-generate unique SKU if empty
        if (empty($productData['sku'])) {
            $prefix = strtoupper(substr($brand->slug, 0, 3));
            $refSlug = strtoupper(Str::slug($validated['reference']) ?: 'REF');
            $productData['sku'] = $prefix . '-' . $refSlug . '-' . rand(1000, 9999);
        }

        $watch = WatchProduct::create($productData);

        return response()->json([
            'message' => 'Produk jam tangan berhasil ditambahkan!',
            'data'    => $watch->load('brand')
        ], 201);
    }

    // Admin: Edit / Update Watch Product
    public function updateProduct(Request $request, $id)
    {
        $watch = WatchProduct::find($id);
        if (!$watch) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'brand_name'      => 'sometimes|string',
            'model'           => 'sometimes|string',
            'reference'       => 'sometimes|string',
            'sku'             => 'nullable|string',
            'condition'       => 'sometimes|string',
            'production_year' => 'nullable|integer',
            'case_size'       => 'nullable|string',
            'case_material'   => 'nullable|string',
            'movement'        => 'nullable|string',
            'box_papers'      => 'nullable|string',
            'price'           => 'nullable|numeric',
            'currency'        => 'nullable|string',
            'availability'    => 'sometimes|in:AVAILABLE,RESERVED,SOLD,SOURCED,ARCHIVED',
            'image_url'       => 'nullable|string',
        ]);

        if (isset($validated['brand_name'])) {
            $rawBrandName = trim($validated['brand_name']);
            $brandSlug = Str::slug($rawBrandName);

            $brand = Brand::where('slug', $brandSlug)
                ->orWhereRaw('LOWER(name) = ?', [strtolower($rawBrandName)])
                ->first();

            if (!$brand) {
                $brand = Brand::create([
                    'name' => ucwords($rawBrandName),
                    'slug' => $brandSlug ?: Str::random(8),
                ]);
            }
            $validated['brand_id'] = $brand->id;
            unset($validated['brand_name']);
        }

        $watch->update($validated);

        return response()->json([
            'message' => 'Produk jam tangan berhasil diperbarui!',
            'data'    => $watch->load('brand')
        ]);
    }

    // Admin: Delete Watch Product
    public function destroyProduct($id)
    {
        $watch = WatchProduct::find($id);
        if (!$watch) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $watch->delete();

        return response()->json(['message' => 'Produk jam tangan berhasil dihapus!']);
    }

    // Admin: Get All Sell Requests (Valuasi Customer)
    public function getSellOffers()
    {
        return response()->json(SellRequest::latest()->get());
    }

    // Admin: Get All Sourcing Requests
    public function getSourcingRequests()
    {
        return response()->json(SourcingRequest::latest()->get());
    }
}
