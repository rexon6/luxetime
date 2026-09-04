<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\SellRequest;
use App\Models\SourcingRequest;
use App\Models\WatchProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WatchCatalogController extends Controller
{
    // ─────────────────────────────────────────────
    //  PUBLIC CATALOG
    // ─────────────────────────────────────────────

    /** GET /api/watches — Katalog + Search & Filter */
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

    /** GET /api/watches/{id} — Detail Produk */
    public function show($id)
    {
        $watch = WatchProduct::with('brand')->find($id);

        if (!$watch) {
            return response()->json(['message' => 'Watch not found'], 404);
        }

        return response()->json($watch);
    }

    // ─────────────────────────────────────────────
    //  CUSTOMER FORMS
    // ─────────────────────────────────────────────

    /** POST /api/sell-offer — Kirim Form Valuasi */
    public function storeSellOffer(Request $request)
    {
        $validated = $request->validate([
            'brand_name'        => 'required|string',
            'model_reference'   => 'required|string',
            'sale_type'         => 'required|in:DIRECT_SELL,CONSIGNMENT,TRADE_IN',
            'box_papers'        => 'nullable|string',
            'expectation_price' => 'nullable|numeric',
            'customer_phone'    => 'required|string',
        ]);

        $offer = SellRequest::create($validated);

        return response()->json([
            'message' => 'Sell request submitted successfully',
            'data'    => $offer,
        ], 201);
    }

    /** POST /api/sourcing-request — Kirim Form Sourcing */
    public function storeSourcingRequest(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => 'required|string',
            'target_budget'    => 'nullable|numeric',
            'customer_phone'   => 'required|string',
        ]);

        $sourcing = SourcingRequest::create($validated);

        return response()->json([
            'message' => 'Sourcing request submitted successfully',
            'data'    => $sourcing,
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  ADMIN
    // ─────────────────────────────────────────────

    /** POST /api/admin/login — Autentikasi Admin */
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
                'admin'   => ['username' => 'admin', 'email' => 'admin@luxetime.com', 'role' => 'Administrator'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau Password Admin salah!',
        ], 401);
    }

    /** POST /api/watches — Tambah Produk Baru */
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

        // Resolve image: file upload takes priority over URL
        $imageUrl = $this->resolveImageUrl($request, $validated);

        $brand = $this->resolveOrCreateBrand($validated['brand_name']);

        $productData = collect($validated)
            ->except(['brand_name', 'image_file'])
            ->merge([
                'brand_id'  => $brand->id,
                'image_url' => $imageUrl,
                'currency'  => $validated['currency'] ?? 'IDR',
                'sku'       => $validated['sku'] ?? $this->generateSku($brand, $validated['reference']),
            ])
            ->toArray();

        $watch = WatchProduct::create($productData);

        return response()->json([
            'message' => 'Produk jam tangan berhasil ditambahkan!',
            'data'    => $watch->load('brand'),
        ], 201);
    }

    /** PUT /api/watches/{id} — Update Produk */
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
            $brand = $this->resolveOrCreateBrand($validated['brand_name']);
            $validated['brand_id'] = $brand->id;
            unset($validated['brand_name']);
        }

        $watch->update($validated);

        return response()->json([
            'message' => 'Produk jam tangan berhasil diperbarui!',
            'data'    => $watch->load('brand'),
        ]);
    }

    /** DELETE /api/watches/{id} — Hapus Produk */
    public function destroyProduct($id)
    {
        $watch = WatchProduct::find($id);
        if (!$watch) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $watch->delete();

        return response()->json(['message' => 'Produk jam tangan berhasil dihapus!']);
    }

    /** GET /api/sell-offers — Daftar Valuasi Customer */
    public function getSellOffers()
    {
        return response()->json(SellRequest::latest()->get());
    }

    /** GET /api/sourcing-requests — Daftar Sourcing Request */
    public function getSourcingRequests()
    {
        return response()->json(SourcingRequest::latest()->get());
    }

    // ─────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────

    /**
     * Resolve brand by slug / name (case-insensitive), or create it if not found.
     */
    private function resolveOrCreateBrand(string $rawName): Brand
    {
        $rawName  = trim($rawName);
        $brandSlug = Str::slug($rawName);

        $brand = Brand::where('slug', $brandSlug)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($rawName)])
            ->first();

        return $brand ?? Brand::create([
            'name' => ucwords($rawName),
            'slug' => $brandSlug ?: Str::random(8),
        ]);
    }

    /**
     * Resolve final image URL: uploaded file → provided URL → default placeholder.
     */
    private function resolveImageUrl(Request $request, array $validated): string
    {
        if ($request->hasFile('image_file')) {
            $file            = $request->file('image_file');
            $ext             = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $cleanModel      = Str::slug($validated['model']) ?: 'watch';
            $filename        = time() . '_' . $cleanModel . '_' . Str::random(5) . '.' . $ext;
            $destinationPath = public_path('uploads/watches');

            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);

            return '/uploads/watches/' . $filename;
        }

        return $validated['image_url']
            ?? 'https://cdn.shopify.com/s/files/1/0682/2009/2547/files/rolex-sprite-indonesia-_2_3a4e5004-aba5-4c4d-be95-2841b1e32f45.jpg?v=1788519463';
    }

    /**
     * Generate a unique SKU from brand slug + reference.
     */
    private function generateSku(Brand $brand, string $reference): string
    {
        $prefix  = strtoupper(substr($brand->slug, 0, 3));
        $refSlug = strtoupper(Str::slug($reference) ?: 'REF');

        return $prefix . '-' . $refSlug . '-' . rand(1000, 9999);
    }
}
