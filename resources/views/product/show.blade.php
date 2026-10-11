@extends('layouts.app')

@section('title', $product->name . ' - Éclore')

@section('content')
<style>
/* Product Details Styles – Editorial Edition */
:root {
    --brand-gold: #B6965D;
    --brand-black: #1a1a1a;
    --brand-gray: #666;
    --brand-light-gray: #f9f9f9;
    --brand-border: #f0f0f0;
    --brand-bg: white;
}

.product-details-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem 5rem;
    background-color: white;
}

body {
    background-color: white !important;
}

/* Typography Overrides */
.font-playfair { font-family: 'Playfair Display', serif; }
.font-azeret { font-family: 'Azeret Mono', monospace; }

.product-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 400;
    line-height: 1.1;
    color: var(--brand-black);
    margin: 0 0 1rem 0;
    letter-spacing: -0.02em;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 3rem 0;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: #999;
    background: transparent;
}

.breadcrumb a {
    color: #999;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: var(--brand-gold);
}

.breadcrumb .separator {
    color: #ddd;
}

.breadcrumb span:last-child {
    color: var(--brand-black);
    font-weight: 600;
}

.product-grid-layout {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 5rem;
    margin-bottom: 6rem;
}

@media (max-width: 1024px) {
    .product-grid-layout {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}

/* Image Gallery */
.image-gallery {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.main-image-container {
    position: relative;
    width: 100%;
    aspect-ratio: 4/5;
    background: var(--brand-light-gray);
    overflow: hidden;
    margin-bottom: 1.5rem;
    border: 1px solid var(--brand-border);
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.main-image:hover {
    transform: scale(1.03);
}

.badge-container {
    position: absolute;
    top: 1.5rem;
    left: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 10;
}

.badge {
    padding: 0.6rem 1.2rem;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    backdrop-filter: blur(10px);
}

.badge-sale {
    background: var(--brand-gold);
    color: white;
}

.badge-featured {
    background: var(--brand-black);
    color: white;
}

.thumbnail-container {
    position: relative;
    width: 100%;
    padding: 1.5rem 0;
    display: flex;
    justify-content: center;
}

.thumbnail-grid {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding-bottom: 0.5rem;
}

.thumbnail-grid::-webkit-scrollbar { display: none; }

.thumbnail {
    flex-shrink: 0;
    width: 90px;
    height: 110px;
    background: white;
    cursor: pointer;
    border: 1px solid var(--brand-border);
    transition: all 0.4s ease;
}

.thumbnail:hover, .thumbnail.active {
    opacity: 1;
    border-color: var(--brand-gold);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    background: white;
    border: 1px solid var(--brand-border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
    transition: all 0.3s ease;
}

.thumbnail-nav-btn:hover {
    border-color: var(--brand-gold);
    color: var(--brand-gold);
}

.thumbnail-nav-btn.prev { left: 0; }
.thumbnail-nav-btn.next { right: 0; }

.image-indicators {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.indicator-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ccc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.indicator-dot.active {
    background: var(--brand-gold);
    transform: scale(1.5);
}

/* Product Info */
.product-info {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}

.product-meta {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--brand-gray);
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stars {
    display: flex;
    gap: 0.15rem;
    color: var(--brand-gold);
}

.stars i { width: 14px; height: 14px; }

.price-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 2rem 0;
    border-top: 1px solid var(--brand-border);
    border-bottom: 1px solid var(--brand-border);
}

.current-price {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    color: var(--brand-black);
}

.original-price {
    font-family: 'Azeret Mono', monospace;
    font-size: 1.25rem;
    color: #bbb;
    text-decoration: line-through;
    letter-spacing: 0.05em;
}

.product-description-container h3 {
    font-family: 'Azeret Mono', monospace;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    color: var(--brand-gold);
    margin-bottom: 1.5rem;
}

.product-description {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    line-height: 1.8;
    color: #444;
}

/* Specifications */
.specifications {
    background: transparent;
    padding: 0;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    padding: 1.25rem 0;
    border-bottom: 1px solid var(--brand-border);
}

.spec-label {
    font-family: 'Azeret Mono', monospace;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--brand-gray);
}

.spec-value {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    color: var(--brand-black);
    text-align: right;
}

/* Actions */
.action-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-top: 1rem;
}

.quantity-selector {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--brand-border);
    margin-bottom: 1rem;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.quantity-label {
    font-family: 'Azeret Mono', monospace;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    font-weight: 700;
    color: var(--brand-gray);
}

.quantity-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
}

.quantity-btn {
    background: transparent;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    color: #999;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
}

.quantity-btn:hover { color: var(--brand-black); }

.quantity-input {
    width: 32px;
    text-align: center;
    border: none;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--brand-black);
    background: transparent;
}

/* Chrome, Safari, Edge, Opera */
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
.quantity-input[type=number] {
  -moz-appearance: textfield;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
}

.btn-add-cart {
    flex: 1;
    height: 56px;
    background: var(--brand-black);
    color: white;
    border: 1px solid transparent;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 0 !important;
}

.btn-add-cart:hover {
    background: var(--brand-gold);
}

.wishlist-btn {
    width: 56px;
    height: 56px;
    background: white;
    border: 1px solid var(--brand-border);
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 !important;
}

.btn-wishlist-round {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid #fff;
    color: #333;
}

.btn-wishlist-round:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.btn-wishlist-round.active {
    background: white;
    color: #ef4444;
}

.btn-wishlist-round.active svg {
    fill: currentColor;
}

.wishlist-btn:hover {
    border-color: var(--brand-gold);
    color: var(--brand-gold);
}

.wishlist-btn.active {
    color: #ef4444;
    border-color: #ef4444;
}

.wishlist-btn.active svg {
    fill: currentColor;
}

/* Product Tabs */
.product-tabs {
    margin-top: 5rem;
    border-bottom: 1px solid var(--brand-border);
}

.tab-buttons {
    display: flex;
    gap: 4rem;
    justify-content: center;
}

.tab-btn {
    padding: 1.5rem 0;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    font-family: 'Azeret Mono', monospace;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.3em;
    color: #999;
    cursor: pointer;
    transition: all 0.3s;
}

.tab-btn:hover, .tab-btn.active {
    color: var(--brand-black);
}

.tab-btn.active {
    border-bottom-color: var(--brand-gold);
}

/* Related Products Grid */
.related-products {
    margin-top: 8rem;
}

.related-products h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 4rem;
}

/* Scoped Recommendation Card Styles */
.rec-card {
    box-shadow: none !important;
    border: none !important;
    background: transparent !important;
}

.rec-card img {
    box-shadow: none !important;
    border: none !important;
}

.rec-card .img-container {
    border: none !important;
}

/* Animations */
@keyframes sparkleAnimation {
    0% { opacity: 0; transform: scale(0); }
    50% { opacity: 1; transform: scale(1.2); }
    100% { opacity: 0; transform: scale(0); }
}
</style>

<div class="product-details-container">
    <!-- Back Button -->
    <div style="padding-top: 1.5rem; margin-bottom: -1.5rem;">
        <a href="javascript:history.back()" style="display: inline-flex; align-items: center; gap: 0.5rem; font-family: 'Azeret Mono', monospace; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.2em; color: #666; text-decoration: none; transition: color 0.3s ease;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Atelier</span>
        </a>
    </div>

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span class="separator">/</span>
        <a href="{{ route('catalogue') }}">Catalogue</a>
        <span class="separator">/</span>
        @if($product->category)
            <a href="{{ route('catalogue', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            <span class="separator">/</span>
        @endif
        <span>{{ $product->name }}</span>
    </nav>

    <!-- Product Grid -->
    <div class="product-grid-layout">
        <!-- Image Gallery -->
        <div class="image-gallery">
            <div class="main-image-container">
                @php
                    $firstImg = ($product->images && count($product->images) > 0) ? $product->images[0] : null;
                    $mainImage = $firstImg 
                        ? (str_starts_with($firstImg, 'http') ? $firstImg : storage_url($firstImg)) 
                        : asset('frontend/assets/placeholder.jpg');
                @endphp
                <img 
                    src="{{ $mainImage }}" 
                    alt="{{ $product->name }}" 
                    class="main-image" 
                    id="mainImage"
                >
                
                <!-- Badges -->
                <div class="badge-container">
                    @if($product->sale_price && $product->sale_price < $product->price)
                        <span class="badge badge-sale">
                            -{{ $product->discount_percentage }}%
                        </span>
                    @endif
                    
                    @if($product->featured)
                        <span class="badge badge-featured">Signature</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Product Info -->
        <div class="product-info">
            <div>
                <div class="product-meta">
                    @if($product->category)
                        <span>{{ $product->category->name }}</span>
                    @endif
                    <div class="product-rating">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($product->average_rating))
                                    <i data-lucide="star" class="fill-current"></i>
                                @elseif($i - 0.5 <= $product->average_rating)
                                    <i data-lucide="star-half" class="fill-current"></i>
                                @else
                                    <i data-lucide="star"></i>
                                @endif
                            @endfor
                        </div>
                        <span>({{ $product->reviews_count }})</span>
                    </div>
                </div>
                <h1 class="product-title">{{ $product->name }}</h1>
            </div>

            <!-- Price -->
            <div class="price-section">
                @if($product->sale_price && $product->sale_price < $product->price)
                    <span class="original-price">₱{{ number_format($product->price, 2) }}</span>
                @endif
                <span class="current-price">₱{{ number_format($product->current_price, 2) }}</span>
            </div>

            <!-- Description -->
            <div class="product-description-container">
                <h3>Description</h3>
                <div class="product-description">
                    <p>{{ $product->description }}</p>
                </div>
            </div>

            <!-- Specifications -->
            <div class="specifications">
                @if($product->sku)
                    <div class="spec-item">
                        <span class="spec-label">Reference</span>
                        <span class="spec-value">{{ $product->sku }}</span>
                    </div>
                @endif
                
                @if($product->material)
                    <div class="spec-item">
                        <span class="spec-label">Metal</span>
                        <span class="spec-value">{{ $product->material }}</span>
                    </div>
                @endif
                
                @if($product->dimensions)
                    <div class="spec-item">
                        <span class="spec-label">Dimensions</span>
                        <span class="spec-value">{{ $product->dimensions }}</span>
                    </div>
                @endif
                
                @if($product->weight)
                    <div class="spec-item">
                        <span class="spec-label">Weight</span>
                        <span class="spec-value">{{ $product->weight }}</span>
                    </div>
                @endif
                
                <div class="spec-item">
                    <span class="spec-label">Availability</span>
                    <span class="spec-value">
                        @if($product->stock_quantity > 0)
                            In Stock ({{ $product->stock_quantity }})
                        @else
                            Available by Request
                        @endif
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="action-section">
                @if($product->stock_quantity > 0)
                    <div class="quantity-selector" style="padding: 1.25rem 2rem;">
                        <span class="quantity-label">Quantity</span>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="decrementQuantity()">
                                <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                            </button>
                            <input type="number" class="quantity-input" id="productQuantity" value="1" min="1" max="{{ $product->stock_quantity }}" readonly>
                            <button type="button" class="quantity-btn" onclick="incrementQuantity()">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="action-buttons">
                    <button 
                        class="btn-add-cart" 
                        id="addToCartBtn"
                        data-product-id="{{ $product->id }}"
                        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}
                    >
                        <span>{{ $product->stock_quantity > 0 ? 'Add to Bag' : 'Out of Stock' }}</span>
                    </button>
                    
                    <button 
                        class="wishlist-btn" 
                        id="wishlistBtn-{{ $product->id }}"
                        data-product-id="{{ $product->id }}"
                        title="Add to Wishlist"
                    >
                        <i data-lucide="heart" class="w-6 h-6" id="heart-icon-{{ $product->id }}"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    </div> <!-- End product-details-container -->

    <!-- Customer Reviews Section -->
    <div class="reviews-section" style="margin-bottom: 0; background-color: white; padding: 8rem 0; width: 100%;">
        <div class="container mx-auto px-6" style="max-width: 1400px; margin: 0 auto;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; text-align: center; margin-bottom: 4rem;">Client Testimonials</h2>
            
            <!-- Reviews Summary -->
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 4rem; margin-bottom: 5rem; border: 1px solid var(--brand-border); padding: 3rem; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
            <!-- Overall Rating -->
            <div style="text-align: center; border-right: 1px solid var(--brand-border); padding-right: 2rem; display: flex; flex-direction: column; justify-content: center;">
                <div style="font-family: 'Playfair Display', serif; font-size: 4rem; font-weight: 400; color: var(--brand-black);">{{ number_format($product->average_rating, 1) }}</div>
                <div class="stars" style="display: flex; justify-content: center; gap: 0.25rem; margin: 1rem 0;">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($product->average_rating))
                            <i data-lucide="star" style="fill: currentColor;"></i>
                        @elseif($i - 0.5 <= $product->average_rating)
                            <i data-lucide="star-half" style="fill: currentColor;"></i>
                        @else
                            <i data-lucide="star"></i>
                        @endif
                    @endfor
                </div>
                <p style="font-family: 'Azeret Mono', monospace; color: #999; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em;">Based on {{ $product->reviews_count }} reviews</p>
            </div>
            
            <!-- Rating Distribution -->
            <div style="display: flex; flex-direction: column; justify-content: center;">
                @foreach([5,4,3,2,1] as $rating)
                    @php
                        $count = $ratingDistribution[$rating];
                        $percentage = $product->reviews_count > 0 ? ($count / $product->reviews_count) * 100 : 0;
                    @endphp
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem;">
                        <span style="width: 80px; font-family: 'Azeret Mono', monospace; font-size: 0.65rem; color: #666; text-transform: uppercase;">{{ $rating }} Star</span>
                        <div style="flex: 1; height: 1px; background: var(--brand-border);">
                            <div style="height: 100%; background: var(--brand-gold); width: {{ $percentage }}%;"></div>
                        </div>
                        <span style="width: 40px; text-align: right; font-family: 'Azeret Mono', monospace; font-size: 0.65rem; color: #999;">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Write Review Button -->
        @auth
            <div style="margin-bottom: 4rem; text-align: center;">
                <button 
                    id="writeReviewBtn" 
                    style="padding: 1.25rem 3rem; background: white; color: var(--brand-black); border: 1px solid var(--brand-black); font-family: 'Azeret Mono', monospace; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3em; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.02);"
                    onclick="showReviewForm()"
                >
                    Share Your Experience
                </button>
            </div>

            <!-- Review Form -->
            <div id="reviewForm" style="display: none; border: 1px solid var(--brand-black); padding: 4rem; margin-bottom: 5rem;">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 2.5rem; text-align: center;">Submit Your Review</h3>
                <form id="submitReviewForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div style="margin-bottom: 2.5rem;">
                        <label style="display: block; font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #999; margin-bottom: 1rem;">Rating</label>
                        <div class="star-rating" style="display: flex; gap: 1rem; font-size: 2rem; color: #ddd; cursor: pointer; justify-content: center;">
                            <i data-lucide="star" class="rating-star" data-rating="1"></i>
                            <i data-lucide="star" class="rating-star" data-rating="2"></i>
                            <i data-lucide="star" class="rating-star" data-rating="3"></i>
                            <i data-lucide="star" class="rating-star" data-rating="4"></i>
                            <i data-lucide="star" class="rating-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="selectedRating" required>
                    </div>

                    <div style="margin-bottom: 2.5rem;">
                        <label style="display: block; font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #999; margin-bottom: 1rem;">Title</label>
                        <input type="text" name="title" style="width: 100%; padding: 1rem 0; border: none; border-bottom: 1px solid var(--brand-border); font-family: 'Playfair Display', serif; font-size: 1.25rem; outline: none; background: transparent;" placeholder="GIVE YOUR REVIEW A TITLE">
                    </div>

                    <div style="margin-bottom: 2.5rem;">
                        <label style="display: block; font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #999; margin-bottom: 1rem;">Review</label>
                        <textarea name="review" rows="5" style="width: 100%; padding: 1rem 0; border: none; border-bottom: 1px solid var(--brand-border); font-family: 'Playfair Display', serif; font-size: 1.15rem; outline: none; background: transparent; resize: none;" placeholder="DESCRIBE YOUR PIECE" required minlength="10"></textarea>
                    </div>

                    <div style="margin-bottom: 4rem;">
                        <label style="display: block; font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #999; margin-bottom: 1rem;">Select Order</label>
                        <select name="order_id" style="width: 100%; padding: 1rem 0; border: none; border-bottom: 1px solid var(--brand-border); font-family: 'Azeret Mono', monospace; font-size: 0.8rem; outline: none; background: transparent; text-transform: uppercase; letter-spacing: 0.1em;" required>
                            <option value="">Reference your purchase</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 2rem; justify-content: center;">
                        <button type="submit" style="padding: 1.25rem 4rem; background: var(--brand-black); color: white; border: none; font-family: 'Azeret Mono', monospace; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3em; cursor: pointer;">Post Feedback</button>
                        <button type="button" onclick="hideReviewForm()" style="padding: 1.25rem 4rem; background: transparent; color: #999; border: 1px solid #ddd; font-family: 'Azeret Mono', monospace; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3em; cursor: pointer;">Cancel</button>
                    </div>
                </form>
            </div>
        @else
            <div style="margin-bottom: 4rem; text-align: center; font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #999;">
                <p>Please <a href="#" onclick="showmodallogin(); return false;" style="color: var(--brand-black); font-weight: 700; text-decoration: underline;">log in</a> to share your experience with this piece.</p>
            </div>
        @endauth

        <!-- Reviews List -->
        <div class="reviews-list" style="background: white; padding: 2rem 4rem; border: 1px solid var(--brand-border); box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
            @forelse($reviews as $review)
                <div style="padding: 3rem 0; border-bottom: 1px solid var(--brand-border);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div class="stars" style="color: var(--brand-gold);">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <i data-lucide="star" style="fill: currentColor; width: 14px; height: 14px;"></i>
                                        @else
                                            <i data-lucide="star" style="width: 14px; height: 14px;"></i>
                                        @endif
                                    @endfor
                                </div>
                                @if($review->is_verified_purchase)
                                    <span style="font-family: 'Azeret Mono', monospace; color: var(--brand-gold); font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700;">Verified Collector</span>
                                @endif
                            </div>
                            @if($review->title)
                                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 0.75rem;">"{{ $review->title }}"</h4>
                            @endif
                            <p style="font-family: 'Azeret Mono', monospace; color: #999; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">{{ $review->user->name }} — {{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <p style="font-family: 'Playfair Display', serif; font-size: 1.1rem; line-height: 1.8; color: #555; max-width: 800px;">{{ $review->review }}</p>
                </div>
            @empty
                <div style="text-align: center; padding: 5rem 0; color: #ccc;">
                    <p style="font-family: 'Azeret Mono', monospace; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.3em;">No testimonials recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div> <!-- End reviews-section -->

<div class="product-details-container">
    <!-- Related Products -->
    @if($relatedProducts && count($relatedProducts) > 0)
        <div class="related-products">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; text-align: center; margin-bottom: 4rem;">Our Recommendations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12" id="related-products-grid">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="rec-card group">
                        <div class="relative mb-6 h-64" style="background: transparent; border: none !important;">
                            @php
                                $firstRelImg = ($relatedProduct->images && is_array($relatedProduct->images) && count($relatedProduct->images) > 0) ? $relatedProduct->images[0] : null;
                                $relatedImage = $firstRelImg 
                                    ? (str_starts_with($firstRelImg, 'http') ? $firstRelImg : storage_url($firstRelImg)) 
                                    : asset('frontend/assets/placeholder.jpg');
                            @endphp
                            <a href="{{ route('products.show', $relatedProduct->slug) }}">
                                <img src="{{ $relatedImage }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="{{ $relatedProduct->name }}">
                            </a>
                             <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="btn-wishlist-round" data-product-id="{{ $relatedProduct->id }}">
                                    <i data-lucide="heart" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-center">
                            <span style="font-family: 'Azeret Mono', monospace; font-size: 0.65rem; color: #999; text-transform: uppercase; letter-spacing: 0.2em; display: block; margin-bottom: 0.5rem;">{{ $relatedProduct->category->name ?? 'Collection' }}</span>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 0.75rem;">
                                <a href="{{ route('products.show', $relatedProduct->slug) }}" class="hover:text-var(--brand-gold)">{{ $relatedProduct->name }}</a>
                            </h3>
                            <div style="font-family: 'Azeret Mono', monospace; font-size: 0.85rem; font-weight: 600; color: var(--brand-black);">₱{{ number_format($relatedProduct->current_price, 0) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div> <!-- End product-details-container -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reinitialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Refresh Lucide specifically for the heart icons if they are empty
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);

    // Initialize wishlist button state
    if (typeof initWishlistButton === 'function') {
        initWishlistButton();
    }
    
    // Initialize add to cart button
    initAddToCartButton();

    // Initialize related products buttons
    initRelatedProductsButtons();
    
    // Initialize thumbnail navigation
    initThumbnailNavigation();
});

// Thumbnail Navigation
function initThumbnailNavigation() {
    const thumbnailGrid = document.getElementById('thumbnailGrid');
    const prevBtn = document.getElementById('thumbnailPrevBtn');
    const nextBtn = document.getElementById('thumbnailNextBtn');
    
    if (!thumbnailGrid || !prevBtn || !nextBtn) return;
    
    function checkScrollButtons() {
        const canScrollLeft = thumbnailGrid.scrollLeft > 0;
        const canScrollRight = thumbnailGrid.scrollLeft < (thumbnailGrid.scrollWidth - thumbnailGrid.clientWidth - 1);
        
        prevBtn.style.display = canScrollLeft ? 'flex' : 'none';
        nextBtn.style.display = canScrollRight ? 'flex' : 'none';
    }
    
    // Check on load and resize
    checkScrollButtons();
    window.addEventListener('resize', checkScrollButtons);
    thumbnailGrid.addEventListener('scroll', checkScrollButtons);
}

function scrollThumbnails(direction) {
    const thumbnailGrid = document.getElementById('thumbnailGrid');
    if (!thumbnailGrid) return;
    
    const scrollAmount = 100; // pixels to scroll
    thumbnailGrid.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Image Gallery
function changeImage(imageUrl, thumbnail, index) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = imageUrl;
    
    // Update active thumbnail
    if (thumbnail) {
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    }
    
    // Update dots
    if (typeof index !== 'undefined') {
        document.querySelectorAll('.indicator-dot').forEach((dot, idx) => {
            if (idx === index) dot.classList.add('active');
            else dot.classList.remove('active');
        });
    }
}

// Quantity Controls
function incrementQuantity() {
    const input = document.getElementById('productQuantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    
    if (current < max) {
        input.value = current + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('productQuantity');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    
    if (current > min) {
        input.value = current - 1;
    }
}

// Wishlist
async function initWishlistButton() {
    const productId = {{ $product->id }};
    const wishlistBtn = document.getElementById('wishlistBtn-' + productId);
    const icon = document.getElementById('heart-icon-' + productId);
    
    if (!wishlistBtn || !icon) return;
    
    // Check if product is in wishlist
    try {
        const response = await fetch(`/api/wishlist/check/${productId}`);
        const data = await response.json();
        
        if (data.in_wishlist) {
            wishlistBtn.classList.add('active');
            icon.style.fill = 'currentColor';
            icon.style.color = 'var(--brand-gold)';
        }
    } catch (error) {
        console.error('Error checking wishlist status:', error);
    }
    
    // Add click handler
    wishlistBtn.addEventListener('click', async function() {
        try {
            const response = await fetch('/api/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ product_id: productId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.classList.toggle('active');
                if (data.action === 'added') {
                    icon.style.fill = 'currentColor';
                    icon.style.color = 'var(--brand-gold)';
                    showNotification('Added to Collection', 'success');
                } else {
                    icon.style.fill = 'none';
                    icon.style.color = 'inherit';
                    showNotification('Removed from Collection', 'info');
                }
                
                // Reinitialize icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
        }
    });
}

// Add to Cart
function initAddToCartButton() {
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (!addToCartBtn) return;
    
    addToCartBtn.addEventListener('click', async function() {
        const productId = this.getAttribute('data-product-id');
        const quantityInput = document.getElementById('productQuantity');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        
        // Disable button during request
        this.disabled = true;
        const originalText = this.querySelector('span').textContent;
        this.querySelector('span').textContent = 'PLACING IN COLLECTION...';
        
        try {
            const response = await window.api.addToCart(productId, quantity);
            
            if (response.success) {
                await animateButtonSuccess(this);
                if (typeof updateCartCount === 'function') { await updateCartCount(); }
                this.disabled = false;
            } else {
                this.querySelector('span').textContent = 'UNAVAILABLE';
                setTimeout(() => {
                    this.querySelector('span').textContent = originalText;
                    this.disabled = false;
                }, 2000);
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.querySelector('span').textContent = 'ERROR';
            setTimeout(() => {
                this.querySelector('span').textContent = originalText;
                this.disabled = false;
            }, 2000);
        }
    });
}

// ── Button Success Animation ──
async function animateButtonSuccess(button) {
    const originalText = button.querySelector('span').textContent;
    button.querySelector('span').textContent = 'IN COLLECTION';
    button.classList.add('btn-success');
    
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    button.querySelector('span').textContent = originalText;
    button.classList.remove('btn-success');
}

// Initialize Related Products Buttons
async function initRelatedProductsButtons() {
    const wishlistBtns = document.querySelectorAll('.btn-wishlist-round');
    
    wishlistBtns.forEach(async (btn) => {
        const productId = btn.getAttribute('data-product-id');
        const icon = btn.querySelector('svg');
        
        // Initial state check
        try {
            const response = await fetch(`/api/wishlist/check/${productId}`);
            const data = await response.json();
            if (data.in_wishlist) {
                btn.classList.add('active');
            }
        } catch (e) {}
        
        // Click handler
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            try {
                const response = await fetch('/api/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('active');
                    if (data.action === 'added') {
                        showNotification('Added to Collection', 'success');
                    } else {
                        showNotification('Removed from Collection', 'info');
                    }
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
            }
        });
    });
}

// Review Form Functions
function showReviewForm() {
    document.getElementById('reviewForm').style.display = 'block';
    document.getElementById('writeReviewBtn').style.display = 'none';
    
    // Load user's orders that contain this product
    loadUserOrders();
    
    // Reinitialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function hideReviewForm() {
    document.getElementById('reviewForm').style.display = 'none';
    document.getElementById('writeReviewBtn').style.display = 'flex';
    
    // Reset form
    document.getElementById('submitReviewForm').reset();
    document.getElementById('selectedRating').value = '';
    
    // Reset stars
    document.querySelectorAll('.rating-star').forEach(star => {
        star.style.fill = 'none';
        star.style.color = '#d1d5db';
    });
}

// Star Rating Interaction
document.addEventListener('DOMContentLoaded', function() {
    const ratingStars = document.querySelectorAll('.rating-star');
    
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            document.getElementById('selectedRating').value = rating;
            
            // Update star colors
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#B6965D';
                } else {
                    s.style.color = '#d1d5db';
                }
            });
            
            // Reinitialize icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#B6965D';
                }
            });
        });
    });
    
    document.querySelector('.star-rating')?.addEventListener('mouseleave', function() {
        const selectedRating = parseInt(document.getElementById('selectedRating')?.value || 0);
        ratingStars.forEach((s, index) => {
            if (index < selectedRating) {
                s.style.color = '#B6965D';
            } else {
                s.style.color = '#d1d5db';
            }
        });
    });
});

// Load User Orders
async function loadUserOrders() {
    try {
        const response = await fetch('/api/account/orders', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const select = document.querySelector('select[name="order_id"]');
            select.innerHTML = '<option value="">Select an order</option>';
            
            // Filter orders that contain this product
            const productId = {{ $product->id }};
            const relevantOrders = data.orders.filter(order => 
                order.order_items.some(item => item.product_id === productId)
            );
            
            if (relevantOrders.length === 0) {
                select.innerHTML = '<option value="">You haven\'t purchased this product yet</option>';
                select.disabled = true;
            } else {
                relevantOrders.forEach(order => {
                    const option = document.createElement('option');
                    option.value = order.id;
                    option.textContent = `Order #${order.order_number} - ${new Date(order.created_at).toLocaleDateString()}`;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading orders:', error);
    }
}

// Submit Review
document.getElementById('submitReviewForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Validate rating
    if (!data.rating) {
        alert('Please select a rating');
        return;
    }
    
    try {
        const response = await fetch('/api/reviews/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            hideReviewForm();
            // Optionally reload the page to show the new review (after approval)
            // location.reload();
        } else {
            alert(result.message || 'Error submitting review');
        }
    } catch (error) {
        console.error('Error submitting review:', error);
        alert('Error submitting review. Please try again.');
    }
});

// Initialize Wishlist Button
async function initializeWishlistButton() {
    const productId = {{ $product->id }};
    const wishlistBtn = document.getElementById('wishlistBtn-' + productId);
    const heartIcon = document.getElementById('heart-icon-' + productId);
    
    if (!wishlistBtn || !heartIcon) {
        console.warn('Wishlist button or icon not found');
        return;
    }
    
    try {
        // Check if product is in wishlist
        const response = await window.api.checkWishlist(productId);
        const isInWishlist = response.in_wishlist;
        
        // Update icon state
        if (isInWishlist) {
            heartIcon.classList.add('active');
            heartIcon.setAttribute('fill', 'currentColor');
            heartIcon.setAttribute('stroke', 'none');
            heartIcon.style.fill = '#ef4444';
            heartIcon.style.color = '#ef4444';
            wishlistBtn.classList.add('active');
        } else {
            heartIcon.classList.remove('active');
            heartIcon.setAttribute('fill', 'none');
            heartIcon.setAttribute('stroke', 'currentColor');
            heartIcon.style.fill = 'none';
            heartIcon.style.color = '';
            wishlistBtn.classList.remove('active');
        }
        
        // Reinitialize lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } catch (error) {
        console.warn('Failed to check wishlist status:', error);
    }
}


@endpush
@endsection

