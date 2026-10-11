<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get paginated products with filtering and sorting (GET /api/v1/products).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query()
                ->with(['category'])
                ->withCount('approvedReviews as reviews_count')
                ->withAvg('approvedReviews as average_rating', 'rating')
                ->where('is_active', true);

            // Filter by category_id directly or by category slug
            if ($request->filled('category_id')) {
                $query->where('category_id', (int) $request->get('category_id'));
            } elseif ($request->has('category') && $request->get('category') !== '' && $request->get('category') !== 'all') {
                $categorySlug = $request->get('category');
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            // Jewellery-specific attribute filters
            foreach (['color', 'material', 'gemstone', 'diamonds'] as $filter) {
                if ($request->filled($filter) && $request->get($filter) !== 'all') {
                    $query->where($filter, $request->get($filter));
                }
            }

            if ($request->filled('carat') && ! $request->filled('diamonds') && $request->get('carat') !== 'all') {
                $query->where('diamonds', $request->get('carat'));
            }

            // Price range filter
            if ($request->filled('price')) {
                $priceRange = $request->get('price');
                match ($priceRange) {
                    'under-30k' => $query->where('price', '<', 30000),
                    '30k-60k' => $query->whereBetween('price', [30000, 60000]),
                    '60k-100k' => $query->whereBetween('price', [60000, 100000]),
                    'over-100k' => $query->where('price', '>', 100000),
                    default => null,
                };
            }

            // Search query
            if ($request->filled('q') || $request->filled('search')) {
                $term = trim($request->get('q', $request->get('search')));
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%")
                        ->orWhere('material', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            }

            // Sorting
            match ($request->get('sort')) {
                'price-low' => $query->orderBy('price', 'asc'),
                'price-high' => $query->orderBy('price', 'desc'),
                'newest' => $query->orderBy('created_at', 'desc'),
                default => $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc'),
            };

            $perPage = min((int) $request->get('limit', $request->get('per_page', 12)), 48);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ],
            ])->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=120');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single product by ID or slug (GET /api/v1/products/{idOrSlug}).
     */
    public function show(string|int $idOrSlug): JsonResponse
    {
        try {
            $query = Product::where('is_active', true)
                ->with(['category', 'approvedReviews.user'])
                ->withCount('approvedReviews as reviews_count')
                ->withAvg('approvedReviews as average_rating', 'rating');

            if (is_numeric($idOrSlug)) {
                $product = $query->where('id', (int) $idOrSlug)->first();
            } else {
                $product = $query->where('slug', $idOrSlug)->first();
            }

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
            ])->header('Cache-Control', 'public, max-age=120, stale-while-revalidate=300');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active categories list (GET /api/v1/categories).
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'slug', 'description', 'image']);

            return response()->json([
                'success' => true,
                'data' => $categories,
            ])->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=600');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products (GET /api/v1/search).
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = trim($request->get('q', ''));
            if (empty($term)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                ]);
            }

            $products = Product::where('is_active', true)
                ->with(['category'])
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%")
                        ->orWhere('material', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products,
                'total' => $products->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching products: '.$e->getMessage(),
            ], 500);
        }
    }
}
