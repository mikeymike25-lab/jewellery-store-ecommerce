<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  IonPage,
  IonContent,
  IonRefresher,
  IonRefresherContent,
  onIonViewWillEnter,
} from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import EcloreModal from '@/components/EcloreModal.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import ReviewsSection from '@/components/ReviewsSection.vue';
import SearchModal from '@/components/SearchModal.vue';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useAuthStore } from '@/stores/auth.store';
import { useCatalogueStore } from '@/stores/catalogue.store';
import type { Product } from '@/types';
import { getProductGallery } from '@/utils/product-image';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const authStore = useAuthStore();
const catalogueStore = useCatalogueStore();

const isInWishlist = computed(() => product.value ? wishlistStore.isInWishlist(product.value.id) : false);

// State
const product = ref<Product | null>(null);
const relatedProducts = ref<Product[]>([]);
const loading = ref(true);
const addingToCart = ref(false);
const buyingNow = ref(false);
const isSearchOpen = ref(false);
const isSizeGuideOpen = ref(false);
const isLightboxOpen = ref(false);

// Active Selection
const activeImageIndex = ref(0);
const selectedMetal = ref('18K Yellow Gold');
const selectedSize = ref('7');
const quantity = ref(1);
const activeAccordion = ref<string>('specs');

const metalOptions = [
  '18K Yellow Gold',
  '18K White Gold',
  '18K Rose Gold',
  'Platinum 950',
];

const ringSizes = ['5', '5.5', '6', '6.5', '7', '7.5', '8', '8.5', '9', '10'];

const productImages = computed(() => {
  return getProductGallery(product.value);
});

const currentImage = computed(() => {
  return productImages.value[activeImageIndex.value] || '/assets/ring.webp';
});

// VAT breakdown (12% Philippine VAT inclusive)
const vatAmount = computed(() => {
  const price = Number(product.value?.final_price || product.value?.price || product.value?.base_price || 0);
  return Math.round((price * 12 / 112) * 100) / 100;
});

const netPrice = computed(() => {
  const price = Number(product.value?.final_price || product.value?.price || product.value?.base_price || 0);
  return Math.round((price - vatAmount.value) * 100) / 100;
});

async function loadProduct(forceRefresh = false) {
  const slug = route.params.slug as string;
  if (!slug) return;

  // Instant display if product is already in cache
  const cached = catalogueStore.getCachedProduct(slug);
  if (cached) {
    product.value = cached;
    loading.value = false;
  } else if (!product.value || product.value.slug !== slug) {
    loading.value = true;
  }

  try {
    const fetched = await catalogueStore.fetchProduct(slug, { forceRefresh });
    product.value = fetched;
    loading.value = false;

    // Load related items in background without blocking main view
    if (fetched?.category_id) {
      catalogueStore.fetchProducts({ category_id: fetched.category_id, limit: 4 }, { forceRefresh: false })
        .then(({ data }) => {
          relatedProducts.value = data.filter((p: Product) => p.id !== fetched?.id);
        })
        .catch((err) => console.warn('Related products load notice:', err));
    }
  } catch (err) {
    console.error('Failed to load product detail:', err);
    loading.value = false;
  }
}

  const addedSuccess = ref(false);

async function handleAddToCart() {
  if (!product.value) return;
  if (!authStore.isAuthenticated) {
    authStore.setIntendedRoute(route.fullPath);
    router.push('/tabs/account');
    return;
  }
  addingToCart.value = true;
  try {
    await cartStore.addToCart(product.value.id, quantity.value, {
      ring_size: selectedSize.value,
      metal_type: selectedMetal.value,
    });
    addedSuccess.value = true;
    setTimeout(() => {
      addedSuccess.value = false;
    }, 1500);
  } catch (err) {
    console.error('Failed to add to cart:', err);
  } finally {
    addingToCart.value = false;
  }
}

async function handleBuyNow() {
  if (!product.value) return;
  if (!authStore.isAuthenticated) {
    authStore.setIntendedRoute('/checkout');
    router.push('/tabs/account');
    return;
  }
  buyingNow.value = true;
  try {
    await cartStore.buyNow(product.value.id, quantity.value, {
      ring_size: selectedSize.value,
      metal_type: selectedMetal.value,
    });
    router.push('/checkout');
  } catch (err) {
    console.error('Buy Now error:', err);
  } finally {
    buyingNow.value = false;
  }
}

async function handleToggleWishlist() {
  if (!product.value) return;
  try {
    await wishlistStore.toggleItem(product.value);
  } catch (err) {
    console.error('Wishlist toggle error:', err);
  }
}

function toggleAccordion(id: string) {
  activeAccordion.value = activeAccordion.value === id ? '' : id;
}

onIonViewWillEnter(() => {
  // Load product leveraging cache for instant display; revalidate smoothly
  loadProduct(false);
  wishlistStore.fetchWishlist();
});

async function handleRefresh(event: CustomEvent) {
  try {
    await loadProduct(true);
  } finally {
    (event.target as any)?.complete();
  }
}
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content pulling-text="Pull to refresh creation..." refreshing-spinner="crescent" />
      </ion-refresher>
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader @open-search="isSearchOpen = true" />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
          <!-- Back Button & Breadcrumb Navigation -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <button
              type="button"
              class="inline-flex items-center gap-2 text-xs font-azeret uppercase tracking-[0.2em] text-stone-600 hover:text-[#B6965D] transition-colors py-1 cursor-pointer group w-max"
              @click="router.back()"
            >
              <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Back to Atelier</span>
            </button>

            <nav class="flex items-center space-x-2 text-[10px] uppercase font-azeret tracking-widest text-stone-400 overflow-x-auto no-scrollbar">
              <router-link to="/" class="hover:text-[#B6965D]">Atelier</router-link>
              <span>/</span>
              <router-link to="/catalogue" class="hover:text-[#B6965D]">Collections</router-link>
              <span>/</span>
              <span v-if="product?.category" class="text-stone-600">{{ product.category.name }}</span>
            </nav>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-32 font-azeret text-xs uppercase tracking-widest text-stone-500">
            Fetching atelier masterpiece...
          </div>

          <!-- Product Details Grid -->
          <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14">
            <!-- ============================================== -->
            <!-- LEFT: SINGLE AESTHETIC SHOWCASE (7 Columns)    -->
            <!-- ============================================== -->
            <div class="lg:col-span-7 flex justify-center">
              <!-- Main Showcase Image with Aesthetic Luxury Frame -->
              <div
                class="relative w-full aspect-[4/5] max-w-[560px] bg-gradient-to-b from-white to-[#FAF8F5] border border-[#B6965D]/30 rounded-2xl shadow-xl flex items-center justify-center p-6 sm:p-12 overflow-hidden group cursor-zoom-in ring-1 ring-[#B6965D]/20 transition-all duration-500 hover:shadow-2xl"
                @click="isLightboxOpen = true"
              >
                <img
                  :src="currentImage"
                  :alt="product.name"
                  class="max-h-full max-w-full object-contain transition-transform duration-700 group-hover:scale-105 drop-shadow-md"
                />

                <span class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-xs px-2.5 py-1 text-[9px] font-azeret uppercase tracking-wider text-stone-600 border border-stone-200 rounded">
                  Tap to Zoom
                </span>
              </div>
            </div>

            <!-- ============================================== -->
            <!-- RIGHT: ACQUISITION & DETAILS (5 Columns)       -->
            <!-- ============================================== -->
            <div class="lg:col-span-5 space-y-6">
              <!-- Category & Title -->
              <div>
                <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D] font-medium block">
                  {{ product.category?.name || 'Éclore Atelier Collection' }}
                </span>
                <h1 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mt-2 leading-tight">
                  {{ product.name }}
                </h1>
                <div class="mt-2 flex items-center gap-3 text-xs font-azeret text-stone-500">
                  <span>SKU: {{ product.sku }}</span>
                  <span>&bull;</span>
                  <span class="text-emerald-700 font-medium">
                    {{ product.stock_quantity > 0 ? 'In Stock &bull; Ready to Dispatch' : 'Made to Order' }}
                  </span>
                </div>
              </div>

              <!-- Price & VAT Disclosure -->
              <div class="p-4 bg-white border border-[#E2DDD5] space-y-1">
                <div class="flex items-baseline justify-between">
                  <CurrencyDisplay :amount="product.final_price || product.price || product.base_price" size="2xl" bold />
                  <span class="font-azeret text-[10px] uppercase tracking-wider text-stone-500">12% PH VAT Included</span>
                </div>
                <p class="font-azeret text-[10px] text-stone-400">
                  Net Amount: ₱{{ netPrice.toLocaleString() }} | Value Added Tax (12%): ₱{{ vatAmount.toLocaleString() }}
                </p>
              </div>

              <!-- Metal Type Selector -->
              <div>
                <label class="block font-azeret text-xs uppercase tracking-widest text-[#1A1A1A] font-semibold mb-2">
                  Precious Metal: <span class="text-[#B6965D] font-normal">{{ selectedMetal }}</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                  <button
                    v-for="metal in metalOptions"
                    :key="metal"
                    type="button"
                    class="py-2.5 px-3 border text-xs font-azeret tracking-wider uppercase transition-all text-center"
                    :class="selectedMetal === metal ? 'bg-[#1A1A1A] text-white border-[#1A1A1A]' : 'bg-white text-stone-800 border-[#E2DDD5] hover:border-[#B6965D]'"
                    @click="selectedMetal = metal"
                  >
                    {{ metal }}
                  </button>
                </div>
              </div>

              <!-- Ring Size Picker -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="font-azeret text-xs uppercase tracking-widest text-[#1A1A1A] font-semibold">
                    Ring Size: <span class="text-[#B6965D] font-normal">US {{ selectedSize }}</span>
                  </label>
                  <button
                    type="button"
                    class="font-azeret text-[10px] uppercase tracking-wider text-[#B6965D] hover:underline"
                    @click="isSizeGuideOpen = true"
                  >
                    Size Guide &rarr;
                  </button>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="size in ringSizes"
                    :key="size"
                    type="button"
                    class="w-10 h-10 border text-xs font-azeret transition-all"
                    :class="selectedSize === size ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow' : 'bg-white text-stone-800 border-[#E2DDD5] hover:border-black'"
                    @click="selectedSize = size"
                  >
                    {{ size }}
                  </button>
                </div>
              </div>

              <!-- Quantity Stepper & Actions -->
              <div class="space-y-3 pt-2">
                <div class="flex items-center gap-4">
                  <div class="flex items-center border border-[#E2DDD5] bg-white font-azeret text-xs">
                    <button
                      type="button"
                      class="px-3 py-2 text-stone-600 hover:text-black"
                      :disabled="quantity <= 1"
                      @click="quantity > 1 && quantity--"
                    >
                      &minus;
                    </button>
                    <span class="px-3 py-2 font-bold">{{ quantity }}</span>
                    <button
                      type="button"
                      class="px-3 py-2 text-stone-600 hover:text-black"
                      @click="quantity++"
                    >
                      &#43;
                    </button>
                  </div>

                  <div class="relative flex-grow">
                    <EcloreButton variant="primary" size="lg" block :loading="addingToCart" @click="handleAddToCart">
                      <span v-if="addedSuccess" class="inline-flex items-center gap-2 text-amber-200">
                        <span>Added to Bag</span>
                        <svg class="w-4 h-4 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                      </span>
                      <span v-else>Add To Shopping Bag</span>
                    </EcloreButton>

                    <!-- Sparkle Star Animation from Main Web -->
                    <div
                      v-if="addedSuccess"
                      class="absolute -top-3.5 -right-3 z-30 pointer-events-none animate-sparkle"
                    >
                      <svg class="w-7 h-7 text-[#B6965D] filter drop-shadow-[0_0_8px_rgba(182,150,93,0.9)]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0l2.8 8.4L23.2 11.2l-8.4 2.8L12 22.4l-2.8-8.4L0.8 11.2l8.4-2.8z" />
                      </svg>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="btn-wishlist-bounce min-h-[48px] w-14 px-3.5 flex items-center justify-center border border-[#E2DDD5] bg-white rounded text-stone-700 hover:border-[#B6965D] hover:text-[#B6965D] transition-colors shrink-0 shadow-sm"
                    :class="isInWishlist ? 'text-rose-500 border-rose-300' : ''"
                    aria-label="Save to favorites"
                    @click="handleToggleWishlist"
                  >
                    <svg class="w-5 h-5" :class="isInWishlist ? 'fill-rose-500 text-rose-500' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                  </button>
                </div>

                <EcloreButton variant="secondary" size="md" block :loading="buyingNow" @click="handleBuyNow">
                  Buy Now &bull; Express Checkout
                </EcloreButton>
              </div>

              <!-- Atelier Assurance Badges -->
              <div class="bg-white border border-[#E2DDD5] p-4 divide-y divide-stone-100 font-azeret text-xs text-stone-700">
                <div class="py-2 flex items-center space-x-3">
                  <svg class="w-4 h-4 text-[#B6965D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                  <span>Certified Ethically Sourced &bull; Hallmarked 18K Gold</span>
                </div>
                <div class="py-2 flex items-center space-x-3">
                  <svg class="w-4 h-4 text-[#B6965D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75a1.125 1.125 0 00-1.125-1.125H3.375A1.125 1.125 0 002.25 3.75v10.5m12 0h-12" /></svg>
                  <span>Complimentary Insured Armed Escort & Courier Nationwide</span>
                </div>
                <div class="py-2 flex items-center space-x-3">
                  <svg class="w-4 h-4 text-[#B6965D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m0-13.5H3.75m8.25 0h8.25" /></svg>
                  <span>Éclore Luxury Packaging with Presentation Box & Care Velvet</span>
                </div>
              </div>

              <!-- Accordions: Specifications, Shipping, Warranty -->
              <div class="border-t border-[#E2DDD5] divide-y divide-[#E2DDD5] font-azeret text-xs">
                <!-- Specifications -->
                <div>
                  <button
                    type="button"
                    class="w-full py-4 flex items-center justify-between text-left font-semibold uppercase tracking-wider text-[#1A1A1A] hover:text-[#B6965D]"
                    @click="toggleAccordion('specs')"
                  >
                    <span>Atelier Technical Specifications</span>
                    <span>{{ activeAccordion === 'specs' ? '&minus;' : '&#43;' }}</span>
                  </button>
                  <div v-if="activeAccordion === 'specs'" class="pb-4 text-stone-600 space-y-2">
                    <div class="grid grid-cols-2 py-1 border-b border-stone-100">
                      <span class="text-stone-400">Metal Type:</span>
                      <span class="font-medium text-stone-800">{{ product.metal_type || selectedMetal }}</span>
                    </div>
                    <div v-if="product.diamond_carat" class="grid grid-cols-2 py-1 border-b border-stone-100">
                      <span class="text-stone-400">Diamond Carat:</span>
                      <span class="font-medium text-stone-800">{{ product.diamond_carat }} ct</span>
                    </div>
                    <div v-if="product.gemstone" class="grid grid-cols-2 py-1 border-b border-stone-100">
                      <span class="text-stone-400">Gemstone:</span>
                      <span class="font-medium text-stone-800">{{ product.gemstone }}</span>
                    </div>
                    <div class="grid grid-cols-2 py-1 border-b border-stone-100">
                      <span class="text-stone-400">Origin / Atelier:</span>
                      <span class="font-medium text-stone-800">Manila, Philippines</span>
                    </div>
                    <div class="grid grid-cols-2 py-1">
                      <span class="text-stone-400">Certification:</span>
                      <span class="font-medium text-stone-800">Official Éclore Certificate of Authenticity</span>
                    </div>
                  </div>
                </div>

                <!-- Shipping Details -->
                <div>
                  <button
                    type="button"
                    class="w-full py-4 flex items-center justify-between text-left font-semibold uppercase tracking-wider text-[#1A1A1A] hover:text-[#B6965D]"
                    @click="toggleAccordion('shipping')"
                  >
                    <span>Shipping & Delivery Policy</span>
                    <span>{{ activeAccordion === 'shipping' ? '&minus;' : '&#43;' }}</span>
                  </button>
                  <div v-if="activeAccordion === 'shipping'" class="pb-4 text-stone-600 leading-relaxed space-y-2">
                    <p>Metro Manila: 2–3 business days via private armoured courier.</p>
                    <p>Luzon, Visayas & Mindanao: 3–5 business days via fully insured transit.</p>
                    <p>Recipient signature and valid government-issued ID required upon handover.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Customer Reviews Section -->
          <div v-if="product" class="mt-16">
            <ReviewsSection :product-id="product.id" :product-name="product.name" />
          </div>
        </main>

        <EcloreFooter />

        <!-- Size Guide Modal -->
        <EcloreModal :is-open="isSizeGuideOpen" title="Ring Sizing Guide" subtitle="Atelier Measurement" @close="isSizeGuideOpen = false">
          <div class="space-y-4 font-azeret text-xs text-stone-700">
            <p>Our rings follow standard US sizing. For precision fitting, we recommend having your finger sized at any fine jeweller.</p>
            <div class="border border-[#E2DDD5] bg-white divide-y divide-[#E2DDD5]">
              <div class="grid grid-cols-3 p-2 font-bold bg-[#FAF8F5]">
                <span>US Size</span>
                <span>Inside Diam.</span>
                <span>Inside Circ.</span>
              </div>
              <div class="grid grid-cols-3 p-2"><span>5</span><span>15.7 mm</span><span>49.3 mm</span></div>
              <div class="grid grid-cols-3 p-2"><span>6</span><span>16.5 mm</span><span>51.8 mm</span></div>
              <div class="grid grid-cols-3 p-2"><span>7</span><span>17.3 mm</span><span>54.4 mm</span></div>
              <div class="grid grid-cols-3 p-2"><span>8</span><span>18.1 mm</span><span>56.9 mm</span></div>
              <div class="grid grid-cols-3 p-2"><span>9</span><span>18.9 mm</span><span>59.5 mm</span></div>
            </div>
            <p class="text-[10px] text-stone-400">Complimentary resizing available within 30 days of acquisition.</p>
          </div>
        </EcloreModal>

        <!-- Image Lightbox Modal -->
        <EcloreModal :is-open="isLightboxOpen" :title="product?.name" subtitle="High Resolution View" max-width="max-w-4xl" @close="isLightboxOpen = false">
          <div class="flex items-center justify-center p-4 bg-white">
            <img :src="currentImage" :alt="product?.name" class="max-h-[75vh] w-auto object-contain" />
          </div>
        </EcloreModal>

        <!-- Search Modal -->
        <SearchModal :is-open="isSearchOpen" @close="isSearchOpen = false" />
      </div>
    </ion-content>
  </ion-page>
</template>
