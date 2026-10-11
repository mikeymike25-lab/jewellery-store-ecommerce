<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage,
  IonContent,
  IonInfiniteScroll,
  IonInfiniteScrollContent,
  IonRefresher,
  IonRefresherContent,
  onIonViewWillEnter,
} from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import FilterDrawer from '@/components/FilterDrawer.vue';
import SearchModal from '@/components/SearchModal.vue';
import QuickViewModal from '@/components/QuickViewModal.vue';
import SortModal from '@/components/SortModal.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useCatalogueStore } from '@/stores/catalogue.store';
import type { Product } from '@/types';
import { getProductImage } from '@/utils/product-image';

const route = useRoute();
const router = useRouter();
const wishlistStore = useWishlistStore();
const catalogueStore = useCatalogueStore();

// State
const contentRef = ref<InstanceType<typeof IonContent> | null>(null);
const products = ref<Product[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const loadError = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const hasMore = ref(true);

const filterAnimationKey = computed(() => {
  return `${selectedCategory.value}-${selectedColor.value}-${selectedMaterial.value}-${selectedGemstone.value}-${selectedDiamonds.value}-${selectedPrice.value}-${sortBy.value}`;
});

async function scrollToProductList() {
  const target = document.getElementById('product-grid-section');
  if (!target || !contentRef.value) return;
  const scrollEl = await contentRef.value.$el.getScrollElement();
  if (scrollEl) {
    const targetY = target.getBoundingClientRect().top + scrollEl.scrollTop - 64;
    contentRef.value.$el.scrollToPoint(0, Math.max(0, targetY), 700);
  }
}

const fallbackProducts: Product[] = [
  {
    id: 1,
    name: 'Lumière Engagement Ring in 14k gold with Mixed gemstones',
    slug: 'lumiere-engagement-ring-in-14k-gold-with-mixed-gemstones-10101',
    description: 'A bespoke jewellery piece from Éclore exclusive vault.',
    base_price: 60966,
    final_price: 60966,
    price: 60966,
    primary_image: '/assets/ring.webp',
    images: ['/assets/ring.webp'],
    category: { id: 1, name: 'Rings', slug: 'rings' } as any,
    is_active: true,
  } as any,
  {
    id: 2,
    name: 'Divine Engagement Ring in palladium with Amethyst',
    slug: 'divine-engagement-ring-in-palladium-with-amethyst-10102',
    description: 'Handcrafted engagement ring in palladium with amethyst.',
    base_price: 69725,
    final_price: 69725,
    price: 69725,
    primary_image: '/assets/necklace.webp',
    images: ['/assets/necklace.webp'],
    category: { id: 1, name: 'Rings', slug: 'rings' } as any,
    is_active: true,
  } as any,
  {
    id: 3,
    name: 'Lumière Solitaire Ring in vermeil with Topaz',
    slug: 'lumiere-engagement-ring-in-vermeil-with-topaz-10103',
    description: 'Fine jewellery craftsmanship in vermeil with topaz.',
    base_price: 70931,
    final_price: 70931,
    price: 70931,
    primary_image: '/assets/earrings.webp',
    images: ['/assets/earrings.webp'],
    category: { id: 1, name: 'Rings', slug: 'rings' } as any,
    is_active: true,
  } as any,
  {
    id: 4,
    name: 'Heritage Bangle Bracelet in Rose Gold',
    slug: 'heritage-bangle-bracelet-in-rose-gold',
    description: 'Timeless luxury bangle crafted in solid rose gold.',
    base_price: 88500,
    final_price: 88500,
    price: 88500,
    primary_image: '/assets/bracelet.webp',
    images: ['/assets/bracelet.webp'],
    category: { id: 2, name: 'Bracelets', slug: 'bracelets' } as any,
    is_active: true,
  } as any,
];

// Modals & Drawers
const isFilterDrawerOpen = ref(false);
const isSearchOpen = ref(false);
const isQuickViewOpen = ref(false);
const isSortModalOpen = ref(false);
const selectedProduct = ref<Product | null>(null);

// Filters State
const selectedColor = ref('');
const selectedMaterial = ref('');
const selectedGemstone = ref('');
const selectedDiamonds = ref('');
const selectedPrice = ref('');
const selectedCategory = ref((route.query.category as string) || '');
const sortBy = ref('popularity');

const sortLabels: Record<string, string> = {
  popularity: 'Relevance',
  'price-low': 'Price: Low to High',
  'price-high': 'Price: High to Low',
  newest: 'Newest Arrivals',
};

const currentSortLabel = computed(() => sortLabels[sortBy.value] || 'Relevance');

function handleSelectSort(val: string) {
  sortBy.value = val;
}

function toggleWishlist(prod: Product, e: Event) {
  e.stopPropagation();
  wishlistStore.toggleItem(prod).catch((err) => {
    console.error('Wishlist toggle error:', err);
  });
}

const activeFiltersCount = computed(() => {
  let count = 0;
  if (selectedColor.value) count++;
  if (selectedMaterial.value) count++;
  if (selectedGemstone.value) count++;
  if (selectedDiamonds.value) count++;
  if (selectedPrice.value) count++;
  if (selectedCategory.value) count++;
  return count;
});

// Dates strip
const dates = computed(() => {
  const list = [];
  const today = new Date();
  for (let i = -3; i <= 3; i++) {
    const d = new Date(today);
    d.setDate(today.getDate() + i);
    list.push({
      day: d.getDate().toString().padStart(2, '0'),
      isToday: i === 0,
    });
  }
  return list;
});

async function loadProducts(page = 1, append = false, forceRefresh = false) {
  if (products.value.length === 0) {
    loading.value = true;
  }
  loadError.value = false;
  try {
    const params: Record<string, any> = {
      page,
      sort: sortBy.value,
    };
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedColor.value) params.color = selectedColor.value;
    if (selectedMaterial.value) params.material = selectedMaterial.value;
    if (selectedGemstone.value) params.gemstone = selectedGemstone.value;
    if (selectedDiamonds.value) params.carat = selectedDiamonds.value;
    if (selectedPrice.value) params.price = selectedPrice.value;

    const { data: fetchedData, meta } = await catalogueStore.fetchProducts(params, { forceRefresh });

    if (append) {
      products.value = [...products.value, ...fetchedData];
    } else {
      products.value = fetchedData;
    }

    currentPage.value = page;
    if (meta) {
      totalPages.value = meta.last_page || 1;
      hasMore.value = page < totalPages.value;
    } else {
      hasMore.value = fetchedData.length >= 12;
    }
  } catch (err) {
    console.error('Failed to load catalogue products:', err);
    loadError.value = true;
    if (products.value.length === 0 && activeFiltersCount.value === 0) {
      products.value = fallbackProducts;
    }
  } finally {
    loading.value = false;
  }
}

async function handleLoadMore() {
  if (loading.value || loadingMore.value || !hasMore.value) return;
  loadingMore.value = true;
  try {
    await loadProducts(currentPage.value + 1, true);
  } finally {
    loadingMore.value = false;
  }
}

async function handleInfinite(event: CustomEvent) {
  if (!hasMore.value) {
    (event.target as any).complete();
    return;
  }
  await loadProducts(currentPage.value + 1, true);
  (event.target as any).complete();
}

function handleApplyDrawerFilters(filters: any) {
  selectedColor.value = filters.color || '';
  selectedMaterial.value = filters.material || '';
  selectedGemstone.value = filters.gemstone || '';
  selectedDiamonds.value = filters.diamonds || '';
  selectedPrice.value = filters.price || '';
  selectedCategory.value = filters.category || '';
  loadProducts(1, false);
}

function clearAllFilters() {
  selectedColor.value = '';
  selectedMaterial.value = '';
  selectedGemstone.value = '';
  selectedDiamonds.value = '';
  selectedPrice.value = '';
  selectedCategory.value = '';
  loadProducts(1, false);
}

function openQuickView(prod: Product, e: Event) {
  e.stopPropagation();
  selectedProduct.value = prod;
  isQuickViewOpen.value = true;
}

function openProductDetail(prod: Product) {
  router.push(`/products/${prod.slug || prod.id}`);
}

watch([selectedColor, selectedMaterial, selectedGemstone, selectedDiamonds, selectedPrice, selectedCategory, sortBy], () => {
  loadProducts(1, false);
});

watch(() => route.query.category, (newCat) => {
  selectedCategory.value = (newCat as string) || '';
});

onIonViewWillEnter(() => {
  // Load catalogue products leveraging cache for instant display; revalidate smoothly
  loadProducts(1, false, false);
  wishlistStore.fetchWishlist();
});

async function handleRefresh(event: CustomEvent) {
  try {
    catalogueStore.clearCache();
    await loadProducts(1, false, true);
  } finally {
    (event.target as any)?.complete();
  }
}
</script>

<template>
  <ion-page>
    <ion-content ref="contentRef" :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content pulling-text="Pull to refresh creations..." refreshing-spinner="crescent" />
      </ion-refresher>
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader @open-search="isSearchOpen = true" />

        <main class="flex-grow">
          <!-- ============================================== -->
          <!-- HERO SECTION (Jewellery Catalogue)            -->
          <!-- ============================================== -->
          <section class="relative w-full min-h-[50vh] bg-cover bg-center flex flex-col justify-between pt-12 text-white" style="background-image: url('/assets/category-earrings.webp')">
            <div class="absolute inset-0 bg-black/45 backdrop-blur-[1px]"></div>

            <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 py-16">
              <span class="font-azeret text-[10px] md:text-xs uppercase tracking-[0.4em] text-[#B6965D] mb-3">
                Curated Haute Joaillerie
              </span>
              <h1 class="font-playfair text-4xl sm:text-6xl md:text-7xl font-light text-white">
                Jewellery Catalogue
              </h1>
            </div>

            <!-- Date Bar -->
            <div class="relative z-10 border-b border-white/20 w-full py-4 bg-black/10 backdrop-blur-xs">
              <div class="max-w-6xl mx-auto px-6 flex justify-between items-end">
                <div
                  v-for="d in dates"
                  :key="d.day"
                  class="flex flex-col items-center"
                  :class="d.isToday ? 'text-white' : 'text-white/40'"
                >
                  <div class="w-1.5 h-1.5 rounded-full mb-2" :class="d.isToday ? 'bg-[#B6965D]' : 'bg-transparent'"></div>
                  <span class="text-[11px] font-azeret font-medium tracking-wider">{{ d.day }}</span>
                </div>
              </div>
            </div>

            <!-- Description & Smooth Scroll CTA Strip -->
            <div class="relative z-10 border-b border-white/20 w-full bg-black/25 backdrop-blur-[2px]">
              <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center">
                <!-- Left: Explore Collections Smooth Scroll Button -->
                <div class="w-full md:w-1/3 md:border-r border-white/15 p-6 md:p-8 flex flex-col justify-center items-center md:items-start text-center md:text-left shrink-0">
                  <button
                    type="button"
                    class="inline-flex items-center gap-3 text-white border-b border-white/40 hover:border-[#B6965D] hover:text-[#B6965D] pb-1.5 text-xs font-azeret uppercase tracking-[0.22em] font-semibold transition-all group cursor-pointer active:scale-95"
                    @click="scrollToProductList"
                  >
                    <span>EXPLORE COLLECTIONS</span>
                    <svg class="w-4 h-4 text-[#B6965D] group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </div>
                <!-- Right: Text Content -->
                <div class="w-full md:w-2/3 p-6 md:p-8 border-t md:border-t-0 border-white/15">
                  <p class="font-azeret text-[10px] md:text-xs tracking-wider text-white/90 uppercase leading-loose text-center md:text-left">
                    OUR CURATED SELECTIONS: DISCOVER THE ESSENCE OF ETERNAL ELEGANCE IN EVERY PIECE. FROM RARE GEMSTONES TO MASTERFULLY CRAFTED PRECIOUS METALS, EACH CREATION IS A TESTAMENT TO OUR HERITAGE.
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- STICKY FILTER & SORT BAR                      -->
          <!-- ============================================== -->
          <div class="sticky top-16 z-30 bg-[#F3EFE7]/95 backdrop-blur-md border-b border-[#E2DDD5]">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
              <!-- Left Side: Mobile & Desktop Filter Buttons (No highlight box, has icon, no bottom line) -->
              <div class="flex items-center gap-3">
                <!-- Mobile Filter Trigger -->
                <button
                  type="button"
                  class="lg:hidden inline-flex items-center gap-2.5 py-2 min-h-[44px] bg-transparent text-xs font-azeret uppercase tracking-[0.18em] font-bold text-[#1A1A1A] hover:text-[#B6965D] transition-colors active:scale-95 group"
                  @click="isFilterDrawerOpen = true"
                >
                  <svg class="w-4 h-4 text-[#B6965D] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                  </svg>
                  <span>Filters</span>
                  <span
                    v-if="activeFiltersCount > 0"
                    class="bg-[#B6965D] text-white rounded-full px-2 py-0.5 text-[10px] font-bold flex items-center justify-center min-w-[20px]"
                  >
                    {{ activeFiltersCount }}
                  </span>
                </button>

                <!-- Desktop Filter Trigger -->
                <button
                  type="button"
                  class="hidden lg:inline-flex items-center gap-2.5 py-2 min-h-[44px] bg-transparent text-xs font-azeret uppercase tracking-[0.18em] font-bold text-[#1A1A1A] hover:text-[#B6965D] transition-colors active:scale-95 group"
                  @click="isFilterDrawerOpen = true"
                >
                  <svg class="w-4 h-4 text-[#B6965D] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                  </svg>
                  <span>Refine Selections</span>
                  <span
                    v-if="activeFiltersCount > 0"
                    class="bg-[#B6965D] text-white rounded-full px-2 py-0.5 text-[10px] font-bold flex items-center justify-center min-w-[20px]"
                  >
                    {{ activeFiltersCount }}
                  </span>
                </button>
              </div>

              <!-- Right Side: Relevance / Sort Trigger (No highlight wrap, aesthetic line below) -->
              <div class="flex items-center font-azeret">
                <button
                  type="button"
                  class="inline-flex items-center justify-center gap-2 pb-1 min-h-[40px] bg-transparent border-b border-[#B6965D] hover:border-[#1A1A1A] text-xs font-azeret uppercase tracking-[0.15em] font-bold text-[#1A1A1A] active:opacity-70 transition-all group"
                  @click="isSortModalOpen = true"
                >
                  <span class="text-stone-400 text-[10px] uppercase tracking-wider hidden sm:inline">Sort:</span>
                  <span class="text-[#B6965D] group-hover:text-[#1A1A1A] transition-colors">{{ currentSortLabel }}</span>
                  <svg class="w-3.5 h-3.5 text-[#B6965D] group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- ============================================== -->
          <!-- PRODUCT GRID                                   -->
          <!-- ============================================== -->
          <div id="product-grid-section" class="max-w-7xl mx-auto px-4 py-12">
            <!-- Luxury Skeleton Shimmer Loading State -->
            <div v-if="loading && products.length === 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 animate-pulse">
              <div v-for="n in 8" :key="n" class="bg-white border border-[#E2DDD5] p-4 flex flex-col justify-between">
                <div class="aspect-square mb-4 bg-stone-100 rounded-sm"></div>
                <div class="space-y-2 mt-2">
                  <div class="h-2.5 bg-stone-200 rounded w-1/3"></div>
                  <div class="h-4 bg-stone-200 rounded w-4/5"></div>
                  <div class="h-3 bg-stone-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>

            <!-- Network / Connection Error State (When no creations could be shown) -->
            <div v-else-if="loadError && products.length === 0" class="text-center py-20 bg-white border border-[#E2DDD5] p-8 sm:p-12 space-y-6">
              <div class="w-12 h-12 mx-auto text-[#B6965D]">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
              </div>
              <h3 class="font-playfair text-2xl text-[#1A1A1A]">Unable to Connect to Atelier Vault</h3>
              <p class="font-azeret text-xs text-stone-500 max-w-md mx-auto">Please check your connection or ensure the development server is accessible.</p>
              <button
                type="button"
                class="eclore-cta-btn bg-[#B6965D] hover:bg-[#9A7E4D] text-white px-8 py-3.5 min-h-[48px] text-xs font-azeret uppercase tracking-[0.2em] font-bold transition-all shadow-md active:scale-95 inline-flex items-center justify-center gap-3"
                @click="loadProducts(1, false)"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                <span>Retry Connection</span>
              </button>
            </div>

            <!-- Empty Filtered State (ONLY shown when filters are actively applied!) -->
            <div v-else-if="products.length === 0 && activeFiltersCount > 0" class="text-center py-24 bg-white border border-[#E2DDD5] p-12 space-y-6">
              <h3 class="font-playfair text-2xl text-[#1A1A1A]">No Creations Match Your Criteria</h3>
              <p class="font-azeret text-xs text-stone-500">Try adjusting your filters or search keywords.</p>
              <button
                type="button"
                class="eclore-cta-btn bg-[#B6965D] hover:bg-[#9A7E4D] text-white px-8 py-3.5 min-h-[48px] text-xs font-azeret uppercase tracking-[0.2em] font-bold transition-all shadow-md active:scale-95 inline-flex items-center justify-center gap-3"
                @click="clearAllFilters"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                <span>Clear All Filters</span>
              </button>
            </div>

            <!-- Empty Catalogue State (No filters active) -->
            <div v-else-if="products.length === 0" class="text-center py-24 bg-white border border-[#E2DDD5] p-12 space-y-6">
              <h3 class="font-playfair text-2xl text-[#1A1A1A]">Atelier Vault Is Updating</h3>
              <p class="font-azeret text-xs text-stone-500">New handcrafted creations will be showcased shortly.</p>
              <button
                type="button"
                class="eclore-cta-btn bg-[#B6965D] hover:bg-[#9A7E4D] text-white px-8 py-3.5 min-h-[48px] text-xs font-azeret uppercase tracking-[0.2em] font-bold transition-all shadow-md active:scale-95 inline-flex items-center justify-center gap-3"
                @click="loadProducts(1, false)"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                <span>Refresh Catalogue</span>
              </button>
            </div>

            <div
              v-else
              :key="filterAnimationKey"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 transition-opacity duration-300"
              :class="loading ? 'opacity-40' : 'opacity-100'"
            >
              <div
                v-for="(prod, index) in products"
                :key="prod.id"
                v-scroll-reveal="(index % 4) * 60"
                class="product-card-scroll group bg-white border border-[#E2DDD5] hover:border-[#B6965D]/60 p-4 flex flex-col justify-between hover:shadow-xl transition-all duration-500 cursor-pointer relative"
                @click="openProductDetail(prod)"
              >
                <!-- Wishlist Toggle -->
                <button
                  type="button"
                  class="btn-wishlist-bounce absolute top-3 right-3 z-20 p-2 text-stone-400 hover:text-[#B6965D] transition-colors"
                  aria-label="Save to wishlist"
                  @click="(e) => toggleWishlist(prod, e)"
                >
                  <svg class="w-5 h-5 transition-transform active:scale-125" :class="wishlistStore.isInWishlist(prod.id) ? 'fill-[#B6965D] text-[#B6965D]' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                  </svg>
                </button>

                <!-- Product Image with Luxury Zoom on Hover -->
                <div class="aspect-square mb-4 bg-stone-50 flex items-center justify-center overflow-hidden relative">
                  <img
                    :src="getProductImage(prod)"
                    :alt="prod.name"
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-contain group-hover:scale-108 transition-transform duration-700 ease-out p-2 will-change-transform"
                  />
                  <!-- Quick View Button Slide-Up Overlay -->
                  <div class="absolute inset-x-2 bottom-2 translate-y-full group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-400 ease-out hidden sm:block z-10">
                    <button
                      type="button"
                      class="w-full bg-[#1A1A1A]/95 hover:bg-[#B6965D] text-white py-2.5 text-[10px] font-azeret uppercase tracking-widest font-semibold backdrop-blur-md shadow-md transition-colors"
                      @click="(e) => openQuickView(prod, e)"
                    >
                      Quick View
                    </button>
                  </div>
                </div>

                <!-- Text & Price Info -->
                <div>
                  <span class="font-azeret text-[9px] uppercase tracking-widest text-[#B6965D] block mb-1">
                    {{ prod.category?.name || 'Fine Jewellery' }}
                  </span>
                  <h3 class="font-playfair text-base sm:text-lg text-[#1A1A1A] group-hover:text-[#B6965D] transition-colors line-clamp-1">
                    {{ prod.name }}
                  </h3>
                  <div class="mt-2 flex items-center justify-between">
                    <CurrencyDisplay :amount="prod.final_price || prod.price || prod.base_price" bold />
                    <span v-if="prod.is_on_sale" class="text-[9px] font-azeret text-red-700 uppercase font-semibold">Sale</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Explicit Load More Button -->
            <div v-if="hasMore && products.length > 0" class="mt-12 mb-6 flex flex-col items-center justify-center">
              <button
                type="button"
                :disabled="loading || loadingMore"
                class="border border-[#B6965D] bg-[#FAF8F5] hover:bg-[#B6965D] text-[#B6965D] hover:text-white px-8 py-3.5 min-h-[46px] text-xs font-azeret uppercase tracking-[0.2em] font-bold transition-all shadow-sm active:scale-95 inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                @click="handleLoadMore"
              >
                <span v-if="loadingMore" class="w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                <span>{{ loadingMore ? 'Gathering Vault Creations...' : 'Load More Creations' }}</span>
              </button>
              <p class="font-azeret text-[10px] text-stone-500 uppercase tracking-widest mt-2">
                Showing page {{ currentPage }} of {{ totalPages }}
              </p>
            </div>

            <!-- Ionic Infinite Scroll for Mobile/Touch -->
            <ion-infinite-scroll @ionInfinite="handleInfinite" :disabled="!hasMore">
              <ion-infinite-scroll-content loading-spinner="bubbles" loading-text="Loading more fine jewellery..."></ion-infinite-scroll-content>
            </ion-infinite-scroll>
          </div>
        </main>

        <EcloreFooter />

        <!-- Filter Drawer -->
        <FilterDrawer
          :is-open="isFilterDrawerOpen"
          :active-filters="{
            color: selectedColor,
            material: selectedMaterial,
            gemstone: selectedGemstone,
            diamonds: selectedDiamonds,
            price: selectedPrice,
            category: selectedCategory,
          }"
          @close="isFilterDrawerOpen = false"
          @apply="handleApplyDrawerFilters"
          @reset="clearAllFilters"
        />

        <!-- Search Modal -->
        <SearchModal :is-open="isSearchOpen" @close="isSearchOpen = false" />

        <!-- Quick View Modal -->
        <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />

        <!-- Sort Modal -->
        <SortModal
          :is-open="isSortModalOpen"
          :selected-sort="sortBy"
          @close="isSortModalOpen = false"
          @select="handleSelectSort"
        />
      </div>
    </ion-content>
  </ion-page>
</template>
