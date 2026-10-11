@extends('layouts.app')

@section('title', 'Éclore Jewellery Store - Crafted beyond time.')

@section('content')
<style>
/* Global text selections matching Éclore theme */
::selection { background-color: #B6965D; color: white; }

/* Custom utilities */
.font-playfair { font-family: 'Playfair Display', serif; }

/* Global Layout Utilities */
.section-padding { padding: 4rem 1.5rem; }
@media (min-width: 768px) { .section-padding { padding: 5rem 4rem; } }
@media (min-width: 1024px) { .section-padding { padding: 6rem 6rem; } }

/* Hero Section Base */
.hero-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.hero-section-1 {
    width: 100%;
    min-height: 120vh; /* Normalized from 200vh for better balance */
    background-image: url('{{ asset("frontend/assets/hero-bg.webp") }}');
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    flex-direction: column;
    padding-top: 154px; /* Space for navbar */
}

.hero-border {
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-border-b {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-border-r {
    border-right: 1px solid rgba(255, 255, 255, 0.2);
}

.date-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: transparent;
    margin-bottom: 8px;
}

.date-dot.active {
    background: #B6965D;
}

.product-arc {
    position: absolute;
    width: 200%;
    height: 200%;
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

/* Semi-transparent overlay for text readability */
/* If the image itself is sufficient, this can be removed or lightened */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.1); 
}

.hero-content {
    z-index: 10;
    color: white; /* Changed to white for better contrast with dark backgrounds */
    padding: 0 1.5rem;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 10vw, 8rem);
    font-weight: 300;
    line-height: 0.9;
    letter-spacing: -0.02em;
    margin-bottom: 2rem;
    color: #fff;
}

.hero-subtitle {
    letter-spacing: 0.25em;
    font-size: clamp(0.7rem, 2vw, 0.9rem);
    line-height: 2;
    font-weight: 400;
    margin-bottom: 3.5rem;
}

.btn-discover {
    display: inline-flex;
    align-items: center;
    color: white;
    background-color: transparent;
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);
    padding-bottom: 6px;
    font-size: 0.7rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.btn-discover:hover {
    color: #B6965D;
    border-color: #B6965D;
    transform: translateY(-2px);
}

/* Scroll indicator */
.scroll-indicator {
    position: absolute;
    bottom: 3rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    z-index: 20;
}

.scroll-line {
    width: 1px;
    height: 60px;
    background: linear-gradient(to bottom, transparent, white);
    animation: scrollLine 2s infinite ease-in-out;
}

@keyframes scrollLine {
    0% { transform: scaleY(0); transform-origin: top; }
    50% { transform: scaleY(1); transform-origin: top; }
    51% { transform: scaleY(1); transform-origin: bottom; }
    100% { transform: scaleY(0); transform-origin: bottom; }
}

/* Limited Edition Section */
.circular-text-wrapper {
    position: relative;
    width: 280px;
    height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.circular-text {
    position: absolute;
    width: 100%;
    height: 100%;
    animation: rotateText 20s linear infinite;
}

@keyframes rotateText {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.creation-card {
    position: relative;
    overflow: hidden;
    aspect-ratio: 4/5;
    cursor: pointer;
}

.creation-card img {
    transition: transform 1.2s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.creation-card:hover img {
    transform: scale(1.08);
}
</style>

    <!-- Section 1: Hero Redesign -->
    @php
        $today = \Carbon\Carbon::now();
        $dates = [];
        for ($i = -3; $i <= 3; $i++) {
            $dates[] = $today->copy()->addDays($i)->day;
        }

        // Fetch products for hero showcase - correctly referencing the model and attributes
        try {
            $heroProducts = \App\Models\Product::where('is_active', true)
                ->latest()
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $heroProducts = collect();
        }

        $leftProduct = $heroProducts->first();
        $carouselProducts = $heroProducts->skip(1);
    @endphp
    <section class="hero-section-1">
        <div class="hero-overlay !bg-black/20"></div>

        <!-- Top Header Part -->
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-6 pt-20 pb-60">
            <h1 class="hero-title !mb-6" data-aos="fade-up">
                Crafted Beyond Time
            </h1>
            <p class="text-[10px] md:text-base tracking-[0.3em] text-white/80 uppercase font-light mb-12" data-aos="fade-up" data-aos-delay="100">
                BEYOND TIME. BEYOND FORM. A WORLD WHERE LIGHT SHAPES<br class="hidden md:block">
                EMOTION, AND ELEGANCE BECOMES ETERNAL.
            </p>
            <div data-aos="fade-up" data-aos-delay="200">
                <a href="#featured" class="bg-white text-black px-8 py-3 text-sm tracking-[0.2em] uppercase font-bold flex items-center gap-3">
                    <span class="text-[6px]">●</span> DISCOVER COLLECTION
                </a>
            </div>
        </div>

        <!-- Date Bar -->
        <div class="relative z-10 hero-border-b w-full" data-aos="fade-in">
            <div class="container mx-auto px-6">
                <div class="flex justify-between items-end pb-8">
                    @foreach($dates as $index => $day)
                    <div class="flex flex-col items-center {{ $index === 3 ? 'text-white' : 'text-white/40' }}">
                        <div class="date-dot {{ $index === 3 ? 'active' : '' }}"></div>
                        <span class="text-[11px] font-azeret">{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Middle Description Section -->
        <div class="relative z-10 hero-border-b w-full bg-black/5 backdrop-blur-[2px]" data-aos="fade-up">
            <div class="container mx-auto px-0">
                <div class="flex flex-col md:flex-row">
                    <!-- Left: Label -->
                    <div class="md:w-1/3 hero-border-r p-12 lg:p-16 flex flex-col justify-between">
                        <div class="flex flex-col md:flex-row items-center gap-12 mt-10">
                            <a href="#collections" class="btn-discover text-white border-white/30 hover:border-white">
                                EXPLORE COLLECTIONS
                            </a>
                        </div>
                    </div>
                    <!-- Right: Text Content -->
                    <div class="md:w-2/3 p-12 lg:p-16">
                        <p class="text-[11px] lg:text-base font-azeret tracking-[0.1em] text-white/80 leading-loose uppercase">
                            LIMITED EDITION COLLECTION: EACH PIECE IN THIS COLLECTION CAPTURES THE QUIET BRILLIANCE OF TIMELESS LUXURY. CRAFTED IN LIMITED NUMBERS, THESE DESIGNS REFLECT PURITY, DEPTH, AND ELEGANCE - CREATED FOR THOSE WHO SEE BEAUTY IN SIMPLICITY.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Showcase Section -->
        <div class="relative z-10 flex-grow w-full" data-aos="fade-up">
            <div class="container mx-auto h-full px-0">
                <div class="grid grid-cols-1 md:grid-cols-2 h-full">
                    <!-- Product 1: Dynamic Left Slot -->
                    @if($leftProduct)
                    <div class="hero-border-r p-12 sm:p-20 flex flex-col items-center justify-center relative overflow-hidden group">
                        <div class="absolute top-12 right-12 z-30">
                            <button class="hero-wishlist-btn navbar-icon-btn p-4 rounded-full text-white hover:text-[#B6965D] hover:bg-white/10 transition-all cursor-pointer" data-product-id="{{ $leftProduct->id }}" id="hero-wish-{{ $leftProduct->id }}">
                                <i data-lucide="heart" class="w-6 h-6" id="hero-heart-{{ $leftProduct->id }}"></i>
                            </button>
                        </div>
                        <div class="relative z-10 w-64 h-64 rounded-3xl bg-gradient-to-b from-white/95 via-white/90 to-stone-100/90 border border-[#B6965D]/30 shadow-2xl p-6 flex items-center justify-center ring-1 ring-[#B6965D]/20 backdrop-blur-md overflow-hidden group-hover:shadow-[0_20px_50px_rgba(182,150,93,0.25)] transition-all duration-700">
                             @php $leftImg = (is_array($leftProduct->images) && count($leftProduct->images) > 0) ? $leftProduct->images[0] : 'products/default.webp'; @endphp
                             <img loading="lazy" src="{{ storage_url($leftImg) }}" alt="{{ $leftProduct->name }}" class="max-h-full max-w-full object-contain drop-shadow-xl transform group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="mt-8 text-center relative z-10">
                            <h3 class="font-playfair text-2xl text-white mb-2">{{ $leftProduct->name }}</h3>
                            <p class="text-[10px] font-azeret text-white/70 tracking-widest uppercase mb-6">[{{ number_format($leftProduct->price, 2) }}]</p>
                            <a href="{{ $leftProduct->slug ? route('products.show', $leftProduct->slug) : '#' }}" class="bg-white text-black px-7 py-3 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 mx-auto w-max hover:bg-stone-100 transition-all shadow-md active:scale-95">
                                <span class="text-[4px]">●</span> SEE DETAILS
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Product 2: Carousel Slot -->
                    <div class="p-0 flex flex-col items-center justify-center relative overflow-hidden group" id="heroCarouselWrapper">
                        <div id="heroCarousel" class="w-full h-full relative">
                            <div class="carousel-container flex h-full transition-transform duration-1000 ease-in-out" id="hero-slides">
                                @forelse($carouselProducts as $index => $prod)
                                <div class="min-w-full h-full flex flex-col items-center justify-center p-12 sm:p-20 relative">
                                    <div class="product-arc opacity-30"></div>
                                    <div class="absolute top-12 right-12 z-30">
                                        <button class="hero-wishlist-btn navbar-icon-btn p-4 rounded-full text-white hover:text-[#B6965D] hover:bg-white/10 transition-all cursor-pointer" data-product-id="{{ $prod->id }}" id="hero-wish-{{ $prod->id }}">
                                            <i data-lucide="heart" class="w-6 h-6" id="hero-heart-{{ $prod->id }}"></i>
                                        </button>
                                    </div>
                                    <div class="relative z-10 w-64 h-64 rounded-3xl bg-gradient-to-b from-white/95 via-white/90 to-stone-100/90 border border-[#B6965D]/30 shadow-2xl p-6 flex items-center justify-center ring-1 ring-[#B6965D]/20 backdrop-blur-md overflow-hidden group-hover:shadow-[0_20px_50px_rgba(182,150,93,0.25)] transition-all duration-700 slide-img">
                                     @php $prodImg = (is_array($prod->images) && count($prod->images) > 0) ? $prod->images[0] : 'products/default.webp'; @endphp
                                     <img src="{{ storage_url($prodImg) }}" alt="{{ $prod->name }}" class="max-h-full max-w-full object-contain drop-shadow-xl transform group-hover:scale-105 transition-transform duration-700">
                                    </div>
                                    <div class="mt-8 text-center relative z-10">
                                        <h3 class="font-playfair text-2xl text-white mb-2">{{ $prod->name }}</h3>
                                        <p class="text-[10px] font-azeret text-white/70 tracking-widest uppercase mb-6">[{{ number_format($prod->price, 2) }}]</p>
                                        <a href="{{ $prod->slug ? route('products.show', $prod->slug) : '#' }}" class="bg-white text-black px-7 py-3 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 mx-auto w-max hover:bg-stone-100 transition-all shadow-md active:scale-95">
                                            <span class="text-[4px]">●</span> SEE DETAILS
                                        </a>
                                    </div>
                                </div>
                                @empty
                                <div class="min-w-full h-full flex flex-col items-center justify-center p-20 relative">
                                    <h3 class="font-playfair text-2xl text-white mb-4 italic">Collections Coming Soon</h3>
                                </div>
                                @endforelse
                            </div>

                            @if($carouselProducts->count() > 1)
                            <!-- Carousel Nav Buttons -->
                            <div class="absolute inset-y-0 left-4 flex items-center z-30">
                                <button onclick="moveHeroCarousel(-1)" class="navbar-icon-btn p-3 bg-black/40 hover:bg-black/75 rounded-full border border-white/20 text-white/70 hover:text-white transition-all backdrop-blur-sm">
                                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                                </button>
                            </div>
                            <div class="absolute inset-y-0 right-4 flex items-center z-30">
                                <button onclick="moveHeroCarousel(1)" class="navbar-icon-btn p-3 bg-black/40 hover:bg-black/75 rounded-full border border-white/20 text-white/70 hover:text-white transition-all backdrop-blur-sm">
                                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <!-- Carousel Pagination Dots -->
                            <div class="absolute bottom-6 inset-x-0 flex items-center justify-center gap-2 z-30" id="heroCarouselDots">
                                @foreach($carouselProducts as $index => $prod)
                                <button type="button" onclick="goToHeroSlide({{ $index }})" class="h-1.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-6 bg-[#B6965D]' : 'w-2 bg-white/30' }}" aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // ── Hero Carousel Auto-slide & Nav ──
            let currentHeroSlide = 0;
            const totalHeroSlides = {{ $carouselProducts->count() }};
            const heroSlides = document.getElementById('hero-slides');
            const heroWrapper = document.getElementById('heroCarouselWrapper');
            let heroAutoSlideInterval = null;

            function updateHeroDots() {
                const dots = document.querySelectorAll('#heroCarouselDots button');
                dots.forEach((dot, idx) => {
                    if (idx === currentHeroSlide) {
                        dot.className = 'h-1.5 rounded-full transition-all duration-300 w-6 bg-[#B6965D]';
                    } else {
                        dot.className = 'h-1.5 rounded-full transition-all duration-300 w-2 bg-white/30 hover:bg-white/60';
                    }
                });
            }

            function goToHeroSlide(idx) {
                if(!heroSlides || totalHeroSlides <= 1) return;
                currentHeroSlide = idx;
                heroSlides.style.transform = `translateX(-${currentHeroSlide * 100}%)`;
                updateHeroDots();
                resetHeroAutoSlide();
            }

            function moveHeroCarousel(direction) {
                if(!heroSlides || totalHeroSlides <= 1) return;
                currentHeroSlide = (currentHeroSlide + direction + totalHeroSlides) % totalHeroSlides;
                heroSlides.style.transform = `translateX(-${currentHeroSlide * 100}%)`;
                updateHeroDots();
                resetHeroAutoSlide();
            }

            function startHeroAutoSlide() {
                if (totalHeroSlides <= 1) return;
                clearInterval(heroAutoSlideInterval);
                heroAutoSlideInterval = setInterval(() => {
                    moveHeroCarousel(1);
                }, 4500);
            }

            function resetHeroAutoSlide() {
                clearInterval(heroAutoSlideInterval);
                startHeroAutoSlide();
            }

            if (heroWrapper) {
                heroWrapper.addEventListener('mouseenter', () => clearInterval(heroAutoSlideInterval));
                heroWrapper.addEventListener('mouseleave', () => startHeroAutoSlide());
            }

            startHeroAutoSlide();

            // ── Wishlist Buttons (Hero & Icons) ──
            function setHeartState(productId, inWishlist, type = 'hero') {
                const prefix = type === 'hero' ? 'hero' : 'icon';
                const icons  = document.querySelectorAll(`[id^="${prefix}-heart-${productId}"]`);
                const btns   = document.querySelectorAll(`[id^="${prefix}-wish-${productId}"]`);
                
                icons.forEach(icon => {
                    if (inWishlist) {
                        icon.style.fill  = type === 'hero' ? '#B6965D' : '#ef4444';
                        icon.style.color = type === 'hero' ? '#B6965D' : '#ef4444';
                    } else {
                        icon.style.fill  = 'none';
                        icon.style.color = '';
                    }
                });
                
                btns.forEach(btn => {
                    if (inWishlist) btn.classList.add('wishlist-active');
                    else btn.classList.remove('wishlist-active');
                });
            }

            async function initHomepageWishlist() {
                if (!window.api) return;
                
                // Select all wishlist buttons (Hero & Icons)
                const btns = document.querySelectorAll('.hero-wishlist-btn, .icon-wishlist-btn');
                
                btns.forEach(async (btn) => {
                    const productId = btn.getAttribute('data-product-id');
                    const isIcon = btn.classList.contains('icon-wishlist-btn');
                    if (!productId) return;

                    // Initial state check
                    try {
                        const res = await window.api.checkWishlist(productId);
                        setHeartState(productId, res.in_wishlist, isIcon ? 'icon' : 'hero');
                    } catch(e) {}

                    // Click handler
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault(); e.stopPropagation();
                        try {
                            const res = await window.api.toggleWishlist(productId);
                            if (res && res.success !== false) {
                                const added = res.was_added ?? (res.action === 'added');
                                setHeartState(productId, added, isIcon ? 'icon' : 'hero');

                                if (typeof updateWishlistOffcanvas === 'function') updateWishlistOffcanvas();
                                if (typeof updateWishlistCount === 'function')    updateWishlistCount();
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        } catch(err) {
                            console.error('Wishlist toggle failed:', err);
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initHomepageWishlist);
            } else {
                setTimeout(initHomepageWishlist, 300);
            }
        </script>

        <div class="scroll-indicator !bottom-12">
            <span class="text-[9px] uppercase tracking-[0.3em] font-light text-white/60">Scroll</span>
            <div class="scroll-line"></div>
        </div>
    </section>

    <!-- Section 2: Limited Edition -->
    <section class="relative min-h-screen flex flex-col lg:flex-row bg-white overflow-hidden" id="limited-edition">
        <!-- Left Side: Content -->
        <div class="lg:w-1/2 relative bg-white flex flex-col justify-between py-20 px-12 md:px-24">
            <!-- Top Date Strip -->
            <div class="flex justify-between items-center w-full border-b border-black/5 pb-6 opacity-60">
                @for($i = -3; $i <= 3; $i++)
                    @php $d = now()->addDays($i)->format('d'); @endphp
                    <span class="font-azeret text-[10px] tracking-widest {{ $i == 0 ? 'text-black font-bold scale-110' : 'text-black/40' }}">{{ $d }}</span>
                @endfor
            </div>

            <div class="flex-grow flex flex-col items-center justify-center text-center max-w-xl mx-auto py-12" data-aos="fade-up">
                <h2 class="font-playfair text-5xl md:text-7xl text-[#1a1a1a] mb-12">Limited Edition</h2>
                <p class="text-[#1a1a1a] font-azeret text-[11px] md:text-[13px] leading-relaxed uppercase tracking-[0.2em] mb-12">
                    RARE AND TIMELESS, OUR ICONIC CREATIONS EMBODY THE SPIRIT OF ETERNITY. EACH LIMITED PIECE TELLS A STORY OF HERITAGE, PASSION, AND EXCEPTIONAL ARTISTRY.
                </p>
                <a href="{{ route('boutiques') }}" class="border border-black/10 px-10 py-5 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-4 hover:bg-black hover:text-white transition-all">
                    <span class="text-[6px]">●</span> PRIVATE APPOINTMENT
                </a>
            </div>

            <!-- Bottom Date Strip -->
            <div class="flex justify-between items-center w-full border-t border-black/5 pt-6 opacity-60">
                @for($i = -3; $i <= 3; $i++)
                    @php $d = now()->addDays($i)->format('d'); @endphp
                    <span class="font-azeret text-[10px] tracking-widest {{ $i == 0 ? 'text-black font-bold scale-110' : 'text-black/40' }}">{{ $d }}</span>
                @endfor
            </div>
            
            <!-- Horizontal central line from mockup -->
            <div class="absolute left-0 top-1/2 w-full h-[1px] bg-black/5"></div>
        </div>

        <!-- Right Side: Full-Bleed Image -->
        <div class="lg:w-1/2 h-64 lg:h-auto overflow-hidden">
            <img loading="lazy" src="{{ asset('frontend/assets/limited-hero.webp') }}" alt="Limited Edition" class="w-full h-full object-cover grayscale-[0.2] hover:grayscale-0 transition-all duration-1000">
        </div>
    </section>
    <section class="bg-white">
        <!-- Featured Icons (The Éclore Icons) -->
        <div class="pt-32" id="featured">
            <div class="container mx-auto px-6 mb-16">
                <div class="text-center">
                    <h2 class="font-playfair text-5xl md:text-7xl text-[#1a1a1a] flex items-center justify-center gap-6">
                        · The Éclore Icons ·
                    </h2>
                </div>
            </div>
            
            <div class="relative group w-full overflow-hidden">
                <!-- Carousel Container -->
                <div id="icons-carousel" class="flex transition-transform duration-700 ease-in-out cursor-pointer w-full">
                    @php
                        try {
                            $iconProducts = \App\Models\Product::where('is_active', true)
                                ->latest()
                                ->take(8)
                                ->get();
                        } catch (\Exception $e) {
                            $iconProducts = collect();
                        }
                        // Duplicate for scroll loop effect (only if we have items)
                        $displayIconProducts = $iconProducts->count() > 0
                            ? $iconProducts->concat($iconProducts)
                            : collect();
                    @endphp

                    @forelse($displayIconProducts as $index => $iconProd)
                    @php
                        $iconImg1 = (is_array($iconProd->images) && count($iconProd->images) > 0) ? storage_url($iconProd->images[0]) : asset('frontend/assets/ring.webp');
                        $iconImg2 = (is_array($iconProd->images) && count($iconProd->images) > 1) ? storage_url($iconProd->images[1]) : $iconImg1;
                        $iconImagesJson = json_encode([$iconImg1, $iconImg2]);
                        $uniqueKey = 'icon-' . $iconProd->id . '-' . $index;
                    @endphp
                    <div class="min-w-full md:min-w-[33.33%] lg:min-w-[25%]">
                        <div class="bg-[#f9f9f9] aspect-[4/5] relative group/card flex flex-col items-center justify-center p-12 overflow-hidden border-r border-gray-200">
                            <!-- Wishlist Heart -->
                            <button class="icon-wishlist-btn absolute top-6 left-6 opacity-0 group-hover/card:opacity-100 transition-all duration-300 z-20 hover:scale-110 p-2 rounded-full"
                                data-product-id="{{ $iconProd->id }}"
                                id="icon-wish-{{ $uniqueKey }}">
                                <i data-lucide="heart" class="w-5 h-5 text-gray-400 transition-all" id="icon-heart-{{ $uniqueKey }}"></i>
                            </button>

                            <!-- Internal Image Browsing -->
                            <div class="relative w-full h-full flex items-center justify-center">
                                <button onclick="changeInternalImg(this, -1, event)" class="absolute left-0 top-1/2 -translate-y-1/2 opacity-0 group-hover/card:opacity-40 hover:opacity-100 transition-opacity z-20">
                                    <i data-lucide="chevron-left" class="w-4 h-4 text-black"></i>
                                </button>

                                <img data-images="{{ $iconImagesJson }}" data-current="0" src="{{ $iconImg1 }}" alt="{{ $iconProd->name }}" class="icon-main-img w-3/4 h-3/4 object-contain transition-all duration-500">

                                <button onclick="changeInternalImg(this, 1, event)" class="absolute right-0 top-1/2 -translate-y-1/2 opacity-0 group-hover/card:opacity-40 hover:opacity-100 transition-opacity z-20">
                                    <i data-lucide="chevron-right" class="w-4 h-4 text-black"></i>
                                </button>
                            </div>

                            <!-- Text Content -->
                            <div class="absolute bottom-10 left-0 w-full text-center px-4">
                                <h3 class="font-playfair text-2xl text-[#1a1a1a] mb-1">{{ $iconProd->name }}</h3>
                                <span class="text-gray-400 text-[10px] tracking-[0.2em] font-azeret uppercase">[{{ number_format($iconProd->sale_price ?? $iconProd->price, 2) }}]</span>
                                <div class="mt-3">
                                    <a href="{{ $iconProd->slug ? route('products.show', $iconProd->slug) : '#' }}"
                                       class="text-[9px] tracking-[0.2em] uppercase font-azeret text-gray-400 hover:text-[#B6965D] transition-colors">
                                        SEE DETAILS →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <!-- Fallback static cards if no products in DB -->
                    @php
                        $fallbackIcons = [
                            ['name' => 'Ondine', 'price' => '$28,000', 'img' => asset('frontend/assets/earrings.webp')],
                            ['name' => 'Éternité', 'price' => '$24,000', 'img' => asset('frontend/assets/ring.webp')],
                            ['name' => 'Clarté', 'price' => '$18,000', 'img' => asset('frontend/assets/necklace.webp')],
                            ['name' => 'Lueur D\'Or', 'price' => '$16,000', 'img' => asset('frontend/assets/bracelet.webp')],
                        ];
                    @endphp
                    @foreach($fallbackIcons as $fb)
                    <div class="min-w-full md:min-w-[33.33%] lg:min-w-[25%]">
                        <div class="bg-[#f9f9f9] aspect-[4/5] relative group/card flex flex-col items-center justify-center p-12 overflow-hidden border-r border-gray-200">
                            <div class="relative w-full h-full flex items-center justify-center">
                                <img src="{{ $fb['img'] }}" alt="{{ $fb['name'] }}" class="w-3/4 h-3/4 object-contain">
                            </div>
                            <div class="absolute bottom-10 left-0 w-full text-center">
                                <h3 class="font-playfair text-2xl text-[#1a1a1a] mb-1">{{ $fb['name'] }}</h3>
                                <span class="text-gray-400 text-[10px] tracking-[0.2em] font-azeret uppercase">[{{ $fb['price'] }}]</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endforelse
                </div>

                <!-- Side Nav Overlays (Click zones for main carousel) -->
                <div onclick="moveCarousel(-1)" class="absolute left-0 top-0 w-24 h-full z-10 cursor-alias"></div>
                <div onclick="moveCarousel(1)" class="absolute right-0 top-0 w-24 h-full z-10 cursor-alias"></div>
            </div>

            <div class="container mx-auto px-6 text-center mt-10 pb-20">
                <a href="#products" class="bg-black text-white px-10 py-4 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-4 inline-flex hover:bg-gray-900 transition-colors">
                    <span class="text-[6px]">●</span> DISCOVER COLLECTION
                </a>
            </div>
        </div>

        <script>
            let currentIconSlide = 0;
            const carousel = document.getElementById('icons-carousel');
            
            function moveCarousel(direction) {
                const cardWidth = carousel.querySelector('div').offsetWidth;
                currentIconSlide += direction;
                
                // Basic loop logic
                if (currentIconSlide < 0) currentIconSlide = 4;
                if (currentIconSlide > 4) currentIconSlide = 0;
                
                carousel.style.transform = `translateX(-${currentIconSlide * cardWidth}px)`;
            }

            function changeInternalImg(btn, direction, event) {
                event.stopPropagation();
                const container = btn.closest('.group\\/card');
                const img = container.querySelector('.icon-main-img');
                const images = JSON.parse(img.dataset.images);
                let current = parseInt(img.dataset.current);
                
                current = (current + direction + images.length) % images.length;
                
                img.style.opacity = '0';
                setTimeout(() => {
                    img.src = images[current];
                    img.dataset.current = current;
                    img.style.opacity = '1';
                }, 300);
            }
        </script>

        <style>
            .cursor-alias { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12h14'/%3E%3Cpath d='m12 5 7 7-7 7'/%3E%3C/svg%3E"), auto; }
        </style>
    </section>
    <!-- Section 3: Our Creations -->
    <section class="bg-white py-24" id="creations">
        <div class="container mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="font-playfair text-5xl md:text-7xl text-[#1a1a1a] flex items-center justify-center gap-6">
                    · Our Creations ·
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php
                    $creations = [
                        ['label' => 'Bracelet', 'img' => asset('frontend/assets/category-bracelet.webp'), 'link' => route('catalogue', ['category' => 'bracelets-bangles'])],
                        ['label' => 'Earrings', 'img' => asset('frontend/assets/category-earrings.webp'), 'link' => route('catalogue', ['category' => 'earrings'])],
                        ['label' => 'Rings', 'img' => asset('frontend/assets/category-rings.webp'), 'link' => route('catalogue', ['category' => 'rings'])]
                    ];
                @endphp

                @foreach($creations as $index => $item)
                <div class="relative aspect-[4/5] group overflow-hidden" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 50 }}">
                    <img loading="lazy" src="{{ $item['img'] }}" alt="{{ $item['label'] }}" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105 will-change-transform">
                    
                    <!-- Overlay Content -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center bg-black/5 group-hover:bg-black/20 transition-colors duration-500">
                        <h3 class="font-playfair text-4xl md:text-6xl mb-6">{{ $item['label'] }}</h3>
                        <a href="{{ $item['link'] }}" class="border border-white/40 bg-white/5 backdrop-blur-sm px-6 py-2 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 hover:bg-white hover:text-black transition-all duration-300">
                            <span class="text-[6px]">●</span> SEE DETAILS
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Bottom Date Strip -->
            <div class="flex justify-between items-center w-full border-t border-black/5 pt-12 opacity-60 mt-20">
                @for($i = -3; $i <= 3; $i++)
                    @php $d = now()->addDays($i)->format('d'); @endphp
                    <span class="font-azeret text-[10px] tracking-widest {{ $i == 0 ? 'text-black font-bold scale-110' : 'text-black/40' }}">{{ $d }}</span>
                @endfor
            </div>
        </div>
    </section>
</div>

<!-- Section 4: Legacy In Every Detail -->
<section class="relative min-h-screen flex bg-[#f8f8f8] overflow-hidden pt-20" id="legacy">
    <!-- Background Image (Grid effect) -->
    <div class="absolute inset-0 z-0">
        <img loading="lazy" src="{{ asset('frontend/assets/legacy-section.png') }}" alt="Legacy Grid" class="w-full h-full object-cover object-top">
    </div>

    <div class="container mx-auto px-6 relative z-10 w-full h-full flex flex-col justify-between">
        <div class="flex flex-col lg:flex-row items-start justify-between flex-grow pt-12">
            <!-- Left: Title & History Tag -->
            <div class="lg:w-1/2" data-aos="fade-right">
                <h2 class="font-playfair text-6xl md:text-8xl text-[#1a1a1a] mb-8 leading-[1.1]">
                    Legacy In Every<br>Detail
                </h2>
                <div class="flex items-center gap-4">
                    <div class="flex gap-1">
                        <span class="w-3 h-3 bg-black rounded-full"></span>
                        <span class="w-3 h-3 bg-black rounded-full"></span>
                    </div>
                    <span class="text-[10px] uppercase tracking-[0.4em] text-gray-400 font-azeret">[HISTORY]</span>
                </div>
            </div>

            <!-- Right: Description & CTA -->
            <div class="lg:w-1/3 mt-20 lg:mt-0" data-aos="fade-left">
                <p class="text-[#1a1a1a] font-azeret text-[11px] md:text-[12px] leading-relaxed uppercase tracking-[0.2em] mb-12">
                    EACH PIECE IS CREATED WITH RESPECT FOR TIME AND TRADITION. OUR ARTISANS PRESERVE THE ESSENCE OF FINE JEWELRY CRAFTSMANSHIP, BLENDING HERITAGE TECHNIQUES WITH MODERN PRECISION. SUSTAINABILITY IS AT THE HEART OF EVERY DESIGN A QUIET PROMISE OF BEAUTY MADE TO LAST.
                </p>
                <a href="{{ route('about') }}" class="bg-black text-white px-10 py-4 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-4 inline-flex hover:bg-gray-900 transition-colors">
                    <span class="text-[6px]">●</span> ABOUT Éclore
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section 5: Your Story Your Masterpiece -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-black" id="masterpiece">
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <img loading="lazy" src="{{ asset('frontend/assets/story-section.webp') }}" alt="Background" class="w-full h-full object-cover grayscale-[0.2] opacity-80">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <!-- Content -->
    <div class="container mx-auto px-6 relative z-10 w-full min-h-screen flex flex-col justify-between py-20">
        <!-- Top Date Strip -->
        <div class="flex justify-between items-center w-full border-b border-white/10 pb-6 opacity-40 mb-12">
            @for($i = -3; $i <= 3; $i++)
                @php $d = now()->addDays($i)->format('d'); @endphp
                <span class="font-azeret text-[10px] tracking-widest {{ $i == 0 ? 'text-white font-bold scale-110' : 'text-white/40' }}">{{ $d }}</span>
            @endfor
        </div>

        <div class="flex flex-col lg:flex-row items-center justify-between flex-grow">
            <!-- Left: Interactive Menu -->
            <div class="lg:w-1/2" data-aos="fade-right">
                <span class="text-[10px] uppercase tracking-[0.4em] text-white/60 mb-6 block">[DESIGN YOUR JEWELLERY]</span>
                <h2 class="font-playfair text-5xl md:text-7xl text-white mb-16 leading-tight">
                    Your Story.<br>Your Masterpiece.
                </h2>
                
                <div class="flex flex-col w-full">
                    @php
                        $options = [
                            ['id' => 'engravings', 'label' => 'Engravinds', 'img' => asset('frontend/assets/ring.webp'), 'pos' => 'translate(10%, -10%)'],
                            ['id' => 'straps', 'label' => 'Straps & Colors', 'img' => asset('frontend/assets/bracelet.webp'), 'pos' => 'translate(10%, 15%)'],
                            ['id' => 'stones', 'label' => 'Stones & Details', 'img' => asset('frontend/assets/necklace.webp'), 'pos' => 'translate(10%, 40%)'],
                            ['id' => 'finishing', 'label' => 'Finishind Touches', 'img' => asset('frontend/assets/earrings.webp'), 'pos' => 'translate(10%, 65%)']
                        ];
                    @endphp

                    @foreach($options as $index => $opt)
                        <button 
                            onclick="updateMasterpiece('{{ $opt['img'] }}', '{{ $opt['pos'] }}', this)"
                            class="masterpiece-btn text-left group border-t border-white/10 py-8 w-full transition-all duration-500 {{ $index === 0 ? 'active-m' : '' }} {{ $index === count($options)-1 ? 'border-b' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-playfair text-3xl md:text-5xl text-white/50 group-hover:text-white transition-all duration-500">
                                    {{ $opt['label'] }}
                                </span>
                                <i data-lucide="arrow-right" class="w-8 h-8 text-white/0 group-hover:text-white/80 transition-all duration-500 translate-x-[-20px] group-hover:translate-x-0"></i>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right: Floating Circle -->
            <div class="lg:w-1/2 relative flex justify-center lg:justify-end mt-20 lg:mt-0">
                <div id="masterpiece-circle" class="relative w-80 h-80 md:w-[450px] md:h-[450px] rounded-full bg-white shadow-2xl flex items-center justify-center transition-all duration-1000 ease-in-out p-12 overflow-hidden" 
                     style="transform: translate(10%, -10%);">
                    
                    <!-- Inner Decoration Dots -->
                    <div class="absolute top-8 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-black rounded-full opacity-80"></div>
                    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-black rounded-full opacity-80"></div>
                    <div class="absolute left-8 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-black rounded-full opacity-80"></div>
                    <div class="absolute right-8 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-black rounded-full opacity-80"></div>

                    <!-- Side Arrow Decor -->
                    <div class="absolute -right-16 top-1/2 -translate-y-1/2 text-white/40 hidden lg:block">
                        <i data-lucide="arrow-right" class="w-10 h-10"></i>
                    </div>

                    <img loading="lazy" id="masterpiece-img" src="{{ asset('frontend/assets/ring.webp') }}" alt="Preview" class="w-full h-full object-contain transition-opacity duration-500">
                </div>
            </div>
        </div>

        <!-- Bottom Button (Moved inside relative container if needed, but keeping separate for now) -->
        <div class="flex justify-center mt-12">
            <a href="{{ route('vip-club') }}" class="bg-white text-black px-8 py-3 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-4 hover:bg-gray-100 transition-colors">
                <span class="text-[6px]">●</span> CREATE YOUR DESIGN
            </a>
        </div>

        <!-- Bottom Date Strip -->
        <div class="flex justify-between items-center w-full border-t border-white/10 pt-6 opacity-40 mt-12">
            @for($i = -3; $i <= 3; $i++)
                @php $d = now()->addDays($i)->format('d'); @endphp
                <span class="font-azeret text-[10px] tracking-widest {{ $i == 0 ? 'text-white font-bold scale-110' : 'text-white/40' }}">{{ $d }}</span>
            @endfor
        </div>
    </div>
</section>

<script>
function updateMasterpiece(imgUrl, pos, element) {
    const circle = document.getElementById('masterpiece-circle');
    const img = document.getElementById('masterpiece-img');
    const btns = document.querySelectorAll('.masterpiece-btn');

    // Update active button
    btns.forEach(btn => btn.classList.remove('active-m'));
    element.classList.add('active-m');

    // Animate circle move
    circle.style.transform = pos;

    // Fade and swap image
    img.style.opacity = '0';
    setTimeout(() => {
        img.src = imgUrl;
        img.style.opacity = '1';
    }, 400);
}

// Preload masterpiece images
document.addEventListener('DOMContentLoaded', () => {
    const masterpieceImages = [
        '{{ asset('frontend/assets/ring.webp') }}',
        '{{ asset('frontend/assets/bracelet.webp') }}',
        '{{ asset('frontend/assets/necklace.webp') }}',
        '{{ asset('frontend/assets/earrings.webp') }}'
    ];
    masterpieceImages.forEach(src => {
        const img = new Image();
        img.src = src;
    });
});
</script>

<style>
.masterpiece-btn.active-m span {
    color: white !important;
    transform: translateX(20px);
}
.masterpiece-btn span {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
#masterpiece-circle {
    will-change: transform;
}
</style>

<!-- Section 6: Newsroom -->
@php
    $newsPages = \App\Models\CmsPage::published()
        ->where('type', 'blog')
        ->orderBy('is_featured', 'desc')
        ->orderBy('published_at', 'desc')
        ->take(2)
        ->get();
@endphp
<section class="bg-[#f8f8f8] py-32" id="newsroom">
    <div class="container mx-auto px-6 text-center">
        <h2 class="font-playfair text-5xl md:text-7xl mb-8 text-[#1a1a1a]">Newsroom</h2>
        <p class="text-[10px] md:text-[11px] font-azeret text-gray-500 tracking-[0.3em] uppercase leading-relaxed max-w-2xl mx-auto mb-20">
            ÉCLORE IS NOT ONLY TIMELESS, BUT ALIVE AND EVOLVING. DISCOVER OUR LATEST CREATIONS, GLOBAL EXHIBITIONS, AND CULTURAL COLLABORATIONS THAT SHAPE THE PRESENT AND FUTURE OF LUXURY.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 px-12">
            @foreach($newsPages as $index => $page)
            <div class="group cursor-pointer" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                <div class="aspect-[5/3] overflow-hidden mb-10">
                    <img loading="lazy" src="{{ $page->featured_image_url ?: asset('frontend/assets/story-' . ($index + 1) . '.webp') }}" alt="{{ $page->title }}" class="w-full h-full object-cover grayscale-[0.3] group-hover:grayscale-0 transition-all duration-1000 group-hover:scale-105">
                </div>
                <h3 class="font-playfair text-3xl mb-4 border-b border-gray-200 inline-block pb-1">{{ $page->title }}</h3>
                <p class="text-[10px] md:text-[11px] font-azeret text-gray-400 tracking-[0.2em] leading-relaxed uppercase mb-10 max-w-sm mx-auto">
                    {{ $page->meta_description ?: $page->excerpt }}
                </p>
                <div class="flex justify-center">
                    <a href="{{ frontend_route('cms.show', $page->slug) }}" class="bg-black text-white px-8 py-3 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3">
                        <span class="text-[6px]">●</span> EXPLORE {{ strtoupper($page->category ?: 'EVENTS') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Section 7: Experience -->
<section class="bg-[#fcfcfc] border-y border-gray-100 py-32" id="experience">
    <div class="container mx-auto px-6">
        <h2 class="font-playfair text-5xl md:text-6xl text-center mb-24 text-[#1a1a1a] tracking-tight">· Éclore Experience ·</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 lg:gap-24">
            <!-- Book An Appointment -->
            <div class="flex flex-col items-center text-center px-4" data-aos="fade-up" data-aos-delay="100">
                <div class="w-32 h-32 mb-10 overflow-hidden">
                    <img loading="lazy" src="{{ asset('frontend/assets/appointment.webp') }}" alt="Book An Appointment" class="w-full h-full object-contain">
                </div>
                <h3 class="font-playfair text-3xl mb-6 text-[#1a1a1a]">Book An Appointment</h3>
                <p class="text-[10px] md:text-[11px] font-azeret text-gray-500 tracking-[0.2em] leading-relaxed uppercase mb-10 max-w-[270px]">
                    DISCOVER HOW TO MAINTAIN AND PRESERVE YOUR CREATIONS FOR YEARS TO COME.
                </p>
                <a href="{{ route('boutiques') }}" class="bg-white border border-gray-100 text-black px-6 py-2 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[4px]">●</span> BOOK NOW
                </a>
            </div>

            <!-- Shipping & Returns -->
            <div class="flex flex-col items-center text-center px-4" data-aos="fade-up" data-aos-delay="200">
                <div class="w-32 h-32 mb-10 overflow-hidden">
                    <img loading="lazy" src="{{ asset('frontend/assets/shipping.webp') }}" alt="Shipping & Returns" class="w-full h-full object-contain">
                </div>
                <h3 class="font-playfair text-3xl mb-6 text-[#1a1a1a]">Shipping & Returns</h3>
                <p class="text-[10px] md:text-[11px] font-azeret text-gray-500 tracking-[0.2em] leading-relaxed uppercase mb-10 max-w-[270px]">
                    ENJOY EXCLUSIVE PREVIEWS, PRIVATE EVENTS, AND TIMELESS PRIVILEGES.
                </p>
                <a href="{{ route('help') }}" class="bg-white border border-gray-100 text-black px-6 py-2 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[4px]">●</span> LEARN MORE
                </a>
            </div>

            <!-- At Your Service -->
            <div class="flex flex-col items-center text-center px-4" data-aos="fade-up" data-aos-delay="300">
                <div class="w-32 h-32 mb-10 overflow-hidden">
                    <img loading="lazy" src="{{ asset('frontend/assets/service.webp') }}" alt="At Your Service" class="w-full h-full object-contain">
                </div>
                <h3 class="font-playfair text-3xl mb-6 text-[#1a1a1a]">At Your Service</h3>
                <p class="text-[10px] md:text-[11px] font-azeret text-gray-500 tracking-[0.2em] leading-relaxed uppercase mb-10 max-w-[270px]">
                    PERSONALIZE STRAPS, DETAILS, OR ENGRAVINGS TO MAKE EACH PIECE UNIQUE.
                </p>
                <a href="{{ route('contact') }}" class="bg-white border border-gray-100 text-black px-6 py-2 text-[10px] tracking-[0.2em] uppercase font-bold flex items-center gap-3 shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[4px]">●</span> CONTACT US
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section 8: Partners & Friends -->
<section class="bg-[#0f110e] py-32 overflow-hidden border-t border-white/5">
    <div class="container mx-auto px-6">
        <!-- Tagline -->
        <div class="text-center mb-24" data-aos="fade-up">
            <p class="text-[10px] md:text-[11px] font-azeret text-white/40 tracking-[0.4em] uppercase leading-relaxed max-w-2xl mx-auto">
                OUR PARTNERS ARE PART OF THE SAME QUIET ELEGANCE WE EMBODY. TOGETHER, WE SHAPE THE LANGUAGE OF MODERN LUXURY.
            </p>
        </div>

        <!-- Split Title & Image -->
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16 mb-24">
            <h2 class="font-playfair text-5xl md:text-7xl text-white tracking-tight" data-aos="fade-right">Partners</h2>
            
            <div class="relative w-full max-w-md aspect-[16/8] overflow-hidden" data-aos="zoom-in">
                <img loading="lazy" id="partner-display-img" src="{{ asset('frontend/assets/partner-bottega-veneta.webp') }}" alt="Boutique" class="w-full h-full object-cover grayscale-[0.2] hover:grayscale-0 transition-all duration-1000">
            </div>

            <h2 class="font-playfair text-5xl md:text-7xl text-white tracking-tight" data-aos="fade-left">& Friends</h2>
        </div>

        <!-- Logo Grid Row -->
        <div class="flex flex-wrap justify-center items-center gap-6 mt-12" data-aos="fade-up" data-aos-delay="200">
            <!-- Bottega Veneta -->
            <button onclick="switchPartner('{{ asset('frontend/assets/partner-bottega-veneta.webp') }}', this)" class="partner-btn w-24 h-24 md:w-32 md:h-32 border border-white/10 flex items-center justify-center p-6 grayscale hover:grayscale-0 hover:border-white/30 transition-all bg-black/20 active-partner">
                <img loading="lazy" src="{{ asset('frontend/assets/partner-bottega-veneta-logo.webp') }}" alt="Bottega Veneta" class="w-full h-auto object-contain opacity-60">
            </button>
            <!-- Givenchy -->
            <button onclick="switchPartner('{{ asset('frontend/assets/partner-givenchy.webp') }}', this)" class="partner-btn w-24 h-24 md:w-32 md:h-32 border border-white/10 flex items-center justify-center p-6 grayscale hover:grayscale-0 hover:border-white/30 transition-all bg-black/20">
                <img loading="lazy" src="{{ asset('frontend/assets/partner-givenchy-logo.webp') }}" alt="Givenchy" class="w-full h-auto object-contain opacity-60">
            </button>
            <!-- Hermes -->
            <button onclick="switchPartner('{{ asset('frontend/assets/partner-hermes.webp') }}', this)" class="partner-btn w-24 h-24 md:w-32 md:h-32 border border-white/10 flex items-center justify-center p-6 grayscale hover:grayscale-0 hover:border-white/30 transition-all bg-black/20">
                <img loading="lazy" src="{{ asset('frontend/assets/partner-hermes-logo.webp') }}" alt="Hermes" class="w-full h-auto object-contain opacity-60">
            </button>
            <!-- Versace -->
            <button onclick="switchPartner('{{ asset('frontend/assets/partner-versace.webp') }}', this)" class="partner-btn w-24 h-24 md:w-32 md:h-32 border border-white/10 flex items-center justify-center p-6 grayscale hover:grayscale-0 hover:border-white/30 transition-all bg-black/20">
                <img loading="lazy" src="{{ asset('frontend/assets/partner-versace-logo.webp') }}" alt="Versace" class="w-full h-auto object-contain opacity-60">
            </button>
            <!-- Gucci -->
            <button onclick="switchPartner('{{ asset('frontend/assets/partner-gucci.webp') }}', this)" class="partner-btn w-24 h-24 md:w-32 md:h-32 border border-white/10 flex items-center justify-center p-6 grayscale hover:grayscale-0 hover:border-white/30 transition-all bg-black/20">
                <img loading="lazy" src="{{ asset('frontend/assets/partner-gucci-logo.webp') }}" alt="Gucci" class="w-full h-auto object-contain opacity-60">
            </button>
        </div>
    </div>
</section>

<script>
// Preload partner images for instant switching
document.addEventListener('DOMContentLoaded', () => {
    const imagesToPreload = [
        '{{ asset('frontend/assets/partner-bottega-veneta.webp') }}',
        '{{ asset('frontend/assets/partner-givenchy.webp') }}',
        '{{ asset('frontend/assets/partner-hermes.webp') }}',
        '{{ asset('frontend/assets/partner-versace.webp') }}',
        '{{ asset('frontend/assets/partner-gucci.webp') }}'
    ];
    
    imagesToPreload.forEach(url => {
        const img = new Image();
        img.src = url;
    });
});

function switchPartner(imageUrl, element) {
    const mainImg = document.getElementById('partner-display-img');
    const buttons = document.querySelectorAll('.partner-btn');
    
    if (!mainImg || mainImg.src === imageUrl) return;
    
    // Smooth fade out
    mainImg.style.opacity = '0';
    mainImg.style.transform = 'scale(1.05)';
    
    // Change src after a short fade
    setTimeout(() => {
        const newImg = new Image();
        newImg.src = imageUrl;
        
        newImg.onload = () => {
            mainImg.src = imageUrl;
            mainImg.style.opacity = '1';
            mainImg.style.transform = 'scale(1)';
        };
    }, 200);
    
    // Update active state
    buttons.forEach(btn => {
        btn.classList.remove('active-partner', 'grayscale-0', 'border-white/30');
        btn.classList.add('grayscale', 'border-white/10');
    });
    
    element.classList.add('active-partner', 'grayscale-0', 'border-white/30');
    element.classList.remove('grayscale', 'border-white/10');
}
</script>

<style>
#partner-display-img {
    transition: opacity 0.3s ease-in-out, transform 0.6s ease-out;
    will-change: opacity, transform;
}
.partner-btn {
    outline: none !important;
}
.active-partner {
    border-color: rgba(255, 255, 255, 0.4) !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    opacity: 1 !important;
}
.active-partner img {
    opacity: 1 !important;
}
</style>
@endsection

@push('scripts')
<script>
    // Check if we should show login modal (from /login redirect)
    @if(session('show_login_modal'))
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a moment for the page to fully load
            setTimeout(function() {
                const loginModal = document.getElementById('login-modal');
                if (loginModal) {
                    loginModal.classList.remove('hidden');
                    loginModal.classList.add('flex');
                }
            }, 500);
        });
    @endif
</script>
@endpush
