<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\ProductDescriptionAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of active, non-soft-deleted products with filters, sorting, and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->when($request->query('category'), function ($q, $categorySlug) {
                $q->whereHas('category', function ($catQuery) use ($categorySlug) {
                    $catQuery->where('slug', $categorySlug);
                });
            })
            ->when($request->query('min_price'), function ($q, $minPrice) {
                $q->where('price', '>=', $minPrice);
            })
            ->when($request->query('max_price'), function ($q, $maxPrice) {
                $q->where('price', '<=', $maxPrice);
            })
            ->when($request->boolean('in_stock'), function ($q) {
                $q->where('stock', '>', 0);
            })
            ->when($request->query('search'), function ($q, $search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })
            ->when($request->query('sort'), function ($q, $sort) {
                if ($sort === '-price') {
                    $q->orderBy('price', 'desc');
                } elseif ($sort === 'price') {
                    $q->orderBy('price', 'asc');
                }
            })
            ->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = Storage::disk('public')->put('products', $request->file('image'));
            $data['image_path'] = $path;
        }
        unset($data['image']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(1000, 9999);
        }

        $product = Product::create($data);
        $product->load('category');

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): ProductResource
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load('category');

        return new ProductResource($product);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = Storage::disk('public')->put('products', $request->file('image'));
            $data['image_path'] = $path;
        } else {
            unset($data['image_path']);
        }
        unset($data['image']);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(1000, 9999);
        }

        $product->update($data);
        $product->load('category');

        return response()->json(new ProductResource($product));
    }

    /**
     * Remove the specified product from storage (soft delete).
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Generate an AI-suggested product description.
     */
    public function generateDescription(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'keywords' => 'required|string',
        ]);

        $promptText = "Product name: {$validated['name']}. Keywords: {$validated['keywords']}.";
        $response = (new ProductDescriptionAgent())->prompt($promptText);

        return response()->json([
            'description' => (string) $response,
        ]);
    }

    /**
     * Perform semantic search on products.
     */
    public function search(Request $request, ProductSearchService $searchService): AnonymousResourceCollection
    {
        $query = (string) $request->query('q', '');
        $limit = (int) $request->query('limit', 12);

        $products = $searchService->search($query, $limit);

        return ProductResource::collection($products);
    }
}
