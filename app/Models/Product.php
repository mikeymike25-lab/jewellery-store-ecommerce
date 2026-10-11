<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Retrieve the model for route model binding.
     * Only allows active products to be accessed by customers.
     * Admins can view inactive products.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        $query = $this->where($field, $value);

        // Allow admins to view and edit inactive products
        // Check if user is authenticated as admin, on an admin route, or on admin subdomain
        $isAdmin = auth()->guard('admin')->check() ||
                   request()->is('admin/*') ||
                   str_starts_with(request()->path(), 'admin/') ||
                   str_contains(request()->getHost(), 'admin.');

        if (! $isAdmin) {
            // For customers, only show active products
            $query->where('is_active', true);
        }

        return $query->first();
    }

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'cost_price',
        'sale_price',
        'sku',
        'barcode',
        'stock_quantity',
        'low_stock_threshold',
        'manage_stock',
        'in_stock',
        'tax_class',
        'material',
        'color',
        'gemstone',
        'diamonds',
        'images',
        'gallery',
        'featured',
        'is_active',
        'view_count',
        'sort_order',
        'meta_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'in_stock' => 'boolean',
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'gallery' => 'array',
        'meta_data' => 'array',
    ];

    protected $appends = [
        'average_rating',
        'reviews_count',
        'primary_image',
        'image_url',
        'base_price',
        'final_price',
        'is_on_sale',
        'current_price',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function getBasePriceAttribute(): float
    {
        return (float) ($this->price ?? 0);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price ?? 0);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->sale_price) && (float) $this->sale_price < (float) $this->price;
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price ?? 0);
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->sale_price && (float) $this->sale_price < (float) $this->price && (float) $this->price > 0) {
            return (int) round((((float) $this->price - (float) $this->sale_price) / (float) $this->price) * 100);
        }

        return 0;
    }

    /**
     * Generate a custom product ID based on category and product sequence
     * Format: A-BB-CC where A=main_category, BB=sub_category, CC=product_number.
     */
    public function generateCustomProductId($mainCategoryId, $subCategoryId, $productNumber)
    {
        return sprintf('%d%02d%02d', $mainCategoryId, $subCategoryId, $productNumber);
    }

    /**
     * Parse custom product ID to extract components.
     */
    public function parseCustomProductId($customId)
    {
        if (strlen($customId) != 5) {
            return;
        }

        return [
            'main_category' => (int) substr($customId, 0, 1),
            'sub_category' => (int) substr($customId, 1, 2),
            'product_number' => (int) substr($customId, 3, 2),
        ];
    }

    /**
     * Validate that subcategory_id is valid for the given category_id.
     */
    public function validateSubcategory()
    {
        if (! $this->subcategory_id || ! $this->category_id) {
            return true; // Allow null subcategory
        }

        $subcategory = Category::find($this->subcategory_id);

        return $subcategory && $subcategory->parent_id == $this->category_id;
    }

    /**
     * Get valid subcategories for the current category.
     */
    public function getValidSubcategories()
    {
        if (! $this->category_id) {
            return collect();
        }

        return Category::where('parent_id', $this->category_id)->get();
    }

    // Inventory relationships
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Inventory helper methods
    public function isLowStock($threshold = null): bool
    {
        $threshold = $threshold ?? $this->low_stock_threshold;

        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'text-red-600',
            'low_stock' => 'text-yellow-600',
            'in_stock' => 'text-green-600',
            default => 'text-gray-600',
        };
    }

    public function getStockStatusBadgeColorAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'low_stock' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'in_stock' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    // Scopes for inventory management
    public function scopeLowStock($query, $threshold = null)
    {
        if ($threshold === null) {
            return $query->whereRaw('stock_quantity <= low_stock_threshold')->where('stock_quantity', '>', 0);
        }

        return $query->where('stock_quantity', '<=', $threshold)->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Reviews relationship
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    // Get average rating
    public function getAverageRatingAttribute(): float
    {
        if (array_key_exists('average_rating', $this->attributes)) {
            return round((float) ($this->attributes['average_rating'] ?? 5.0), 1);
        }

        return round((float) ($this->approvedReviews()->avg('rating') ?? 5.0), 1);
    }

    // Get total reviews count
    public function getReviewsCountAttribute(): int
    {
        if (array_key_exists('reviews_count', $this->attributes) && $this->attributes['reviews_count'] !== null) {
            return (int) $this->attributes['reviews_count'];
        }

        return (int) $this->approvedReviews()->count();
    }

    // Get primary image URL
    public function getImageUrlAttribute(): ?string
    {
        if (is_array($this->images) && count($this->images) > 0) {
            $img = $this->images[0];
            if (!empty($img) && !str_contains($img, 'placehold.co')) {
                return $img;
            }
        }

        if (is_array($this->gallery) && count($this->gallery) > 0) {
            $img = $this->gallery[0];
            if (!empty($img) && !str_contains($img, 'placehold.co')) {
                return $img;
            }
        }

        $prefixMap = [
            1 => '/assets/products/ring-1.jpg',
            2 => '/assets/products/necklace-1.jpg',
            3 => '/assets/products/earring-1.jpg',
            4 => '/assets/products/bracelet-1.jpg',
            5 => '/assets/products/timepiece-1.jpg',
        ];

        return $prefixMap[$this->category_id] ?? '/assets/ring.webp';
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->image_url;
    }
}
