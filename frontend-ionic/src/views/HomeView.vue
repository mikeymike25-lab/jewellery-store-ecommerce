<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import SearchModal from '@/components/SearchModal.vue';
import QuickViewModal from '@/components/QuickViewModal.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import type { Product } from '@/types';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useCatalogueStore } from '@/stores/catalogue.store';
import { getProductImage } from '@/utils/product-image';

const router = useRouter();
const wishlistStore = useWishlistStore();
const catalogueStore = useCatalogueStore();

// Modals
const isSearchOpen = ref(false);
const isQuickViewOpen = ref(false);
const selectedProduct = ref<Product | null>(null);

// Products data
const heroProducts = ref<Product[]>([]);
const leftProduct = computed(() => heroProducts.value[0] || null);
const carouselProducts = computed(() => heroProducts.value.slice(1));
const currentHeroSlide = ref(0);
const isHoveringCarousel = ref(false);
let autoSlideTimer: any = null;

function startAutoSlide() {
  stopAutoSlide();
  autoSlideTimer = setInterval(() => {
    if (!isHoveringCarousel.value && carouselProducts.value.length > 1) {
      nextHeroSlide();
    }
  }, 4500);
}

function stopAutoSlide() {
  if (autoSlideTimer) {
    clearInterval(autoSlideTimer);
    autoSlideTimer = null;
  }
}

function nextHeroSlide() {
  if (carouselProducts.value.length === 0) return;
  currentHeroSlide.value = (currentHeroSlide.value + 1) % carouselProducts.value.length;
}

function prevHeroSlide() {
  if (carouselProducts.value.length === 0) return;
  currentHeroSlide.value = (currentHeroSlide.value - 1 + carouselProducts.value.length) % carouselProducts.value.length;
}

function manualNext() {
  nextHeroSlide();
  startAutoSlide();
}

function manualPrev() {
  prevHeroSlide();
  startAutoSlide();
}

function goToSlide(idx: number) {
  currentHeroSlide.value = idx;
  startAutoSlide();
}

// Calendar dates around today
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

// Masterpiece interactive state
const masterpieceOptions = [
  { id: 'engravings', label: 'Engravings', img: '/assets/ring.webp' },
  { id: 'straps', label: 'Straps & Colors', img: '/assets/bracelet.webp' },
  { id: 'stones', label: 'Stones & Details', img: '/assets/necklace.webp' },
  { id: 'finishing', label: 'Finishing Touches', img: '/assets/earrings.webp' },
];
const activeMasterpiece = ref(masterpieceOptions[0]);
const circleRotation = ref(0);

function selectMasterpiece(opt: typeof masterpieceOptions[0]) {
  if (activeMasterpiece.value.id === opt.id) return;
  activeMasterpiece.value = opt;
  circleRotation.value += 360; // Rotate circle 360 degrees forward smoothly
}

// Partners & Friends interactive state
const partnerList = [
  { name: 'Bottega Veneta', logo: '/assets/partner-bottega-veneta-logo.webp', img: '/assets/partner-bottega-veneta.webp' },
  { name: 'Givenchy', logo: '/assets/partner-givenchy-logo.webp', img: '/assets/partner-givenchy.webp' },
  { name: 'Hermes', logo: '/assets/partner-hermes-logo.webp', img: '/assets/partner-hermes.webp' },
  { name: 'Versace', logo: '/assets/partner-versace-logo.webp', img: '/assets/partner-versace.webp' },
  { name: 'Gucci', logo: '/assets/partner-gucci-logo.webp', img: '/assets/partner-gucci.webp' },
];
const activePartner = ref(partnerList[0]);

// Wishlist toggle connected to Pinia wishlistStore
async function toggleWishlist(product: Product | number) {
  try {
    await wishlistStore.toggleItem(product);
  } catch (err) {
    console.error('Failed to toggle wishlist:', err);
  }
}

function openQuickView(prod: Product) {
  selectedProduct.value = prod;
  isQuickViewOpen.value = true;
}

async function loadHeroProducts() {
  try {
    const { data } = await catalogueStore.fetchProducts({ limit: 8 });
    heroProducts.value = data;
  } catch (err) {
    console.error('Failed to load products for homepage:', err);
  }
}

onMounted(async () => {
  await loadHeroProducts();
  startAutoSlide();
});

onUnmounted(() => {
  stopAutoSlide();
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <!-- Top Navbar -->
        <EcloreHeader @open-search="isSearchOpen = true" />

        <main class="flex-grow">
          <!-- ============================================== -->
          <!-- SECTION 1: HERO (Crafted Beyond Time)         -->
          <!-- ============================================== -->
          <section class="relative w-full min-h-[92vh] bg-cover bg-center flex flex-col justify-between pt-12 text-white" style="background-image: url('/assets/hero-bg.webp')">
            <div class="absolute inset-0 bg-black/35 backdrop-blur-[1px]"></div>

            <!-- Top Header Part -->
            <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 pt-12 pb-16 sm:pb-24">
              <span class="font-azeret text-[10px] md:text-xs uppercase tracking-[0.4em] text-[#B6965D] mb-4">
                Haute Joaillerie &bull; Manila Atelier
              </span>
              <h1 class="font-playfair text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-light tracking-tight leading-tight text-white mb-6">
                Crafted Beyond Time
              </h1>
              <p class="font-azeret text-[10px] md:text-xs tracking-[0.25em] text-white/80 uppercase max-w-2xl mx-auto leading-relaxed mb-8">
                BEYOND TIME. BEYOND FORM. A WORLD WHERE LIGHT SHAPES EMOTION, AND ELEGANCE BECOMES ETERNAL.
              </p>
              <div>
                <button
                  type="button"
                  class="eclore-cta-btn bg-white text-black hover:bg-stone-100 px-8 sm:px-10 py-3.5 min-h-[48px] text-xs sm:text-sm tracking-[0.2em] uppercase font-azeret font-bold transition-all inline-flex items-center justify-center gap-3.5 shadow-xl active:scale-95"
                  @click="router.push('/catalogue')"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                  <span>DISCOVER COLLECTION</span>
                </button>
              </div>

              <!-- Luxury Scroll Indicator (Pulsing Gold Line) -->
              <div class="mt-8 flex flex-col items-center select-none pointer-events-none">
                <span class="text-[9px] font-azeret uppercase tracking-[0.3em] text-[#B6965D] mb-2 opacity-90">Explore</span>
                <div class="w-[1.5px] h-9 bg-white/20 relative overflow-hidden rounded-full">
                  <div class="absolute inset-0 bg-[#B6965D] animate-scroll-line"></div>
                </div>
              </div>
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
                  <div
                    class="w-1.5 h-1.5 rounded-full mb-2"
                    :class="d.isToday ? 'bg-[#B6965D]' : 'bg-transparent'"
                  ></div>
                  <span class="text-[11px] font-azeret font-medium tracking-wider">{{ d.day }}</span>
                </div>
              </div>
            </div>

            <!-- Middle Description Strip -->
            <div class="relative z-10 border-b border-white/20 w-full bg-black/15 backdrop-blur-xs">
              <div class="max-w-7xl mx-auto flex flex-col md:flex-row">
                <div class="md:w-1/3 p-8 lg:p-12 border-b md:border-b-0 md:border-r border-white/20 flex items-center justify-center md:justify-start">
                  <button
                    type="button"
                    class="inline-flex items-center text-white border-b border-white/50 hover:border-[#B6965D] hover:text-[#B6965D] pb-1.5 text-xs font-azeret tracking-[0.25em] uppercase transition-all"
                    @click="router.push('/catalogue')"
                  >
                    EXPLORE COLLECTIONS
                  </button>
                </div>
                <div class="md:w-2/3 p-8 lg:p-12">
                  <p class="font-azeret text-[10px] md:text-xs tracking-wider text-white/80 uppercase leading-loose">
                    LIMITED EDITION COLLECTION: EACH PIECE IN THIS COLLECTION CAPTURES THE QUIET BRILLIANCE OF TIMELESS LUXURY. CRAFTED IN LIMITED NUMBERS, THESE DESIGNS REFLECT PURITY, DEPTH, AND ELEGANCE.
                  </p>
                </div>
              </div>
            </div>

            <!-- Product Showcase Section (Left slot + Carousel slot) -->
            <div class="relative z-10 w-full bg-black/25">
              <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-white/20">
                <!-- Left Slot -->
                <div v-if="leftProduct" class="p-8 sm:p-12 flex flex-col items-center justify-center relative group overflow-hidden">
                  <div class="absolute top-6 right-6 z-20">
                    <button
                      type="button"
                      class="p-2 text-white hover:text-[#B6965D] transition-colors"
                      @click="toggleWishlist(leftProduct)"
                    >
                      <svg class="w-6 h-6" :class="wishlistStore.isInWishlist(leftProduct.id) ? 'fill-[#B6965D] text-[#B6965D]' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                      </svg>
                    </button>
                  </div>

                  <!-- Aesthetic Luxury Pedestal Frame -->
                  <div class="relative w-64 h-64 sm:w-72 sm:h-72 rounded-3xl bg-gradient-to-b from-white/95 via-white/90 to-stone-100/90 border border-[#B6965D]/30 shadow-2xl p-6 flex items-center justify-center ring-1 ring-[#B6965D]/20 backdrop-blur-md overflow-hidden group-hover:shadow-[0_20px_50px_rgba(182,150,93,0.25)] transition-all duration-700">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-amber-50/50 via-transparent to-transparent opacity-80 pointer-events-none"></div>
                    <img :src="getProductImage(leftProduct)" :alt="leftProduct.name" class="relative z-10 max-h-full max-w-full object-contain drop-shadow-xl transform group-hover:scale-105 transition-transform duration-700" />
                  </div>

                  <div class="mt-8 text-center">
                    <h3 class="font-playfair text-2xl text-white">{{ leftProduct.name }}</h3>
                    <p class="font-azeret text-xs text-white/70 tracking-widest uppercase my-2">
                      <CurrencyDisplay :amount="leftProduct.final_price || leftProduct.price || leftProduct.base_price" color-class="text-white/90" />
                    </p>
                    <button
                      type="button"
                      class="bg-white text-black hover:bg-stone-100 px-7 py-3 min-h-[46px] text-xs tracking-[0.2em] uppercase font-azeret font-bold transition-all inline-flex items-center justify-center gap-3 mt-3 shadow-md active:scale-95"
                      @click="router.push(`/products/${leftProduct.slug || leftProduct.id}`)"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                      <span>SEE DETAILS</span>
                    </button>
                  </div>
                </div>

                <!-- Right Carousel Slot -->
                <div
                  v-if="carouselProducts.length > 0"
                  class="p-8 sm:p-12 flex flex-col items-center justify-center relative group overflow-hidden"
                  @mouseenter="isHoveringCarousel = true"
                  @mouseleave="isHoveringCarousel = false"
                >
                  <!-- Orbital Product Arc Background -->
                  <div class="absolute w-[180%] h-[180%] rounded-full border border-white/5 pointer-events-none"></div>

                  <div class="absolute top-6 right-6 z-20">
                    <button
                      type="button"
                      class="p-2.5 text-white hover:text-[#B6965D] transition-colors rounded-full hover:bg-white/10"
                      @click="toggleWishlist(carouselProducts[currentHeroSlide])"
                    >
                      <svg class="w-6 h-6" :class="wishlistStore.isInWishlist(carouselProducts[currentHeroSlide].id) ? 'fill-[#B6965D] text-[#B6965D]' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                      </svg>
                    </button>
                  </div>

                  <!-- Carousel Controls -->
                  <button
                    type="button"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 text-white/70 hover:text-white bg-black/40 hover:bg-black/75 rounded-full w-11 h-11 flex items-center justify-center transition-all backdrop-blur-sm border border-white/20 active:scale-95"
                    aria-label="Previous slide"
                    @click="manualPrev"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                  </button>
                  <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 text-white/70 hover:text-white bg-black/40 hover:bg-black/75 rounded-full w-11 h-11 flex items-center justify-center transition-all backdrop-blur-sm border border-white/20 active:scale-95"
                    aria-label="Next slide"
                    @click="manualNext"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>

                  <!-- Smooth Slide / Transition Container -->
                  <transition name="carousel-pedestal" mode="out-in">
                    <div
                      :key="carouselProducts[currentHeroSlide]?.id"
                      class="flex flex-col items-center justify-center w-full"
                    >
                      <!-- Aesthetic Display Pedestal Frame for Carousel -->
                      <div class="relative w-64 h-64 sm:w-72 sm:h-72 rounded-3xl bg-gradient-to-b from-white/95 via-white/90 to-stone-100/90 border border-[#B6965D]/30 shadow-2xl p-6 flex items-center justify-center ring-1 ring-[#B6965D]/20 backdrop-blur-md overflow-hidden group-hover:shadow-[0_20px_50px_rgba(182,150,93,0.25)] transition-all duration-700">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-amber-50/50 via-transparent to-transparent opacity-80 pointer-events-none"></div>
                        <img
                          :src="getProductImage(carouselProducts[currentHeroSlide])"
                          :alt="carouselProducts[currentHeroSlide].name"
                          class="relative z-10 max-h-full max-w-full object-contain drop-shadow-xl transform group-hover:scale-105 transition-transform duration-700"
                        />
                      </div>

                      <div class="mt-8 text-center w-full">
                        <h3 class="font-playfair text-2xl text-white">{{ carouselProducts[currentHeroSlide].name }}</h3>
                        <p class="font-azeret text-xs text-white/70 tracking-widest uppercase my-2">
                          <CurrencyDisplay :amount="carouselProducts[currentHeroSlide].final_price || carouselProducts[currentHeroSlide].price || carouselProducts[currentHeroSlide].base_price" color-class="text-white/90" />
                        </p>
                        <button
                          type="button"
                          class="eclore-cta-btn bg-white text-black hover:bg-stone-100 px-8 py-3.5 min-h-[46px] text-xs tracking-[0.2em] uppercase font-azeret font-bold transition-all inline-flex items-center justify-center gap-3 mt-3 shadow-md active:scale-95"
                          @click="router.push(`/products/${carouselProducts[currentHeroSlide].slug || carouselProducts[currentHeroSlide].id}`)"
                        >
                          <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                          <span>SEE DETAILS</span>
                        </button>
                      </div>
                    </div>
                  </transition>

                  <!-- Pagination Dots -->
                  <div class="flex items-center gap-2 mt-6 z-20">
                    <button
                      v-for="(_, idx) in carouselProducts"
                      :key="idx"
                      type="button"
                      class="h-1.5 rounded-full transition-all duration-300"
                      :class="currentHeroSlide === idx ? 'w-6 bg-[#B6965D]' : 'w-2 bg-white/30 hover:bg-white/60'"
                      :aria-label="`Slide ${idx + 1}`"
                      @click="goToSlide(idx)"
                    ></button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Animated Scroll Line Indicator -->
            <div class="py-8 flex flex-col items-center justify-center gap-2">
              <span class="text-[9px] uppercase tracking-[0.3em] font-light text-white/60 font-azeret">Scroll</span>
              <div class="w-px h-10 bg-gradient-to-b from-white to-transparent animate-pulse"></div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 2: LIMITED EDITION                    -->
          <!-- ============================================== -->
          <section class="relative min-h-[85vh] flex flex-col lg:flex-row bg-white overflow-hidden border-b border-[#E2DDD5]" id="limited-edition">
            <div class="lg:w-1/2 bg-white flex flex-col justify-between py-16 px-8 sm:px-16 lg:px-20">
              <div class="flex justify-between items-center w-full border-b border-black/5 pb-4 opacity-60">
                <span v-for="d in dates" :key="d.day" class="font-azeret text-[10px] tracking-widest" :class="d.isToday ? 'text-black font-bold scale-110' : 'text-black/40'">
                  {{ d.day }}
                </span>
              </div>

              <div class="py-12 text-center max-w-lg mx-auto">
                <h2 class="font-playfair text-4xl sm:text-6xl text-[#1A1A1A] mb-6">Limited Edition</h2>
                <p class="font-azeret text-xs sm:text-sm text-stone-600 uppercase tracking-widest leading-loose mb-10">
                  RARE AND TIMELESS, OUR ICONIC CREATIONS EMBODY THE SPIRIT OF ETERNITY. EACH LIMITED PIECE TELLS A STORY OF HERITAGE, PASSION, AND EXCEPTIONAL ARTISTRY.
                </p>
                <div class="flex justify-center">
                  <button
                    type="button"
                    class="eclore-cta-btn bg-black hover:bg-stone-900 text-white px-8 sm:px-10 py-3.5 min-h-[48px] text-xs tracking-[0.2em] uppercase font-azeret font-bold transition-all inline-flex items-center justify-center gap-3.5 shadow-md active:scale-95"
                    @click="router.push('/catalogue')"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                    <span>PRIVATE APPOINTMENT</span>
                  </button>
                </div>
              </div>

              <div class="flex justify-between items-center w-full border-t border-black/5 pt-4 opacity-60">
                <span v-for="d in dates" :key="d.day" class="font-azeret text-[10px] tracking-widest" :class="d.isToday ? 'text-black font-bold scale-110' : 'text-black/40'">
                  {{ d.day }}
                </span>
              </div>
            </div>

            <!-- Right: Full-bleed image -->
            <div class="lg:w-1/2 h-72 sm:h-96 lg:h-auto overflow-hidden">
              <img src="/assets/limited-hero.webp" alt="Limited Edition" class="w-full h-full object-cover grayscale-[0.2] hover:grayscale-0 hover:scale-105 transition-all duration-1000" />
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 3: THE ÉCLORE ICONS (CAROUSEL)        -->
          <!-- ============================================== -->
          <section class="bg-white py-24 border-b border-[#E2DDD5]" id="featured">
            <div class="max-w-7xl mx-auto px-4 text-center mb-16">
              <h2 class="font-playfair text-4xl sm:text-6xl text-[#1A1A1A]">· The Éclore Icons ·</h2>
              <p class="font-azeret text-xs uppercase tracking-[0.25em] text-[#B6965D] mt-2">Signature Masterpieces</p>
            </div>

            <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
              <div
                v-for="(prod, index) in heroProducts.slice(0, 4)"
                :key="prod.id"
                v-scroll-reveal="(index % 4) * 80"
                class="product-card-scroll group relative bg-[#F9F9F9] border border-stone-200 hover:border-[#B6965D]/60 p-6 flex flex-col justify-between hover:shadow-xl transition-all"
              >
                <!-- Wishlist Toggle -->
                <button
                  type="button"
                  class="btn-wishlist-bounce absolute top-3 right-3 z-20 p-2 text-stone-400 hover:text-[#B6965D] transition-colors"
                  aria-label="Save to wishlist"
                  @click.stop="toggleWishlist(prod)"
                >
                  <svg class="w-5 h-5 transition-transform active:scale-125" :class="wishlistStore.isInWishlist(prod.id) ? 'fill-[#B6965D] text-[#B6965D]' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                  </svg>
                </button>

                <div class="aspect-square flex items-center justify-center p-4 overflow-hidden">
                  <img :src="getProductImage(prod)" :alt="prod.name" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500" />
                </div>

                <div class="mt-4 text-center">
                  <h3 class="font-playfair text-lg text-[#1A1A1A] line-clamp-1">{{ prod.name }}</h3>
                  <div class="my-2">
                    <CurrencyDisplay :amount="prod.final_price || prod.price || prod.base_price" bold />
                  </div>
                  <button
                    type="button"
                    class="mt-3 w-full border border-[#B6965D]/40 hover:border-[#B6965D] bg-transparent hover:bg-[#B6965D] text-[#B6965D] hover:text-white min-h-[42px] px-4 py-2.5 text-xs uppercase tracking-[0.2em] font-azeret font-bold transition-all inline-flex items-center justify-center gap-2 shadow-sm active:scale-95 group"
                    @click="openQuickView(prod)"
                  >
                    <span>QUICK VIEW</span>
                    <span class="text-xs transition-transform group-hover:translate-x-1">→</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="text-center mt-12">
              <button
                type="button"
                class="eclore-cta-btn bg-black hover:bg-stone-900 text-white px-10 py-4 min-h-[50px] text-xs font-azeret uppercase tracking-[0.2em] font-bold transition-colors inline-flex items-center justify-center gap-3.5 shadow-md active:scale-95"
                @click="router.push('/catalogue')"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                <span>DISCOVER ALL ICONS</span>
              </button>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 4: OUR CREATIONS (3-CARD GRID)        -->
          <!-- ============================================== -->
          <section class="bg-white py-24 border-b border-[#E2DDD5]" id="creations">
            <div class="max-w-7xl mx-auto px-4">
              <div class="text-center mb-16">
                <h2 class="font-playfair text-4xl sm:text-6xl text-[#1A1A1A]">· Our Creations ·</h2>
                <p class="font-azeret text-xs uppercase tracking-[0.25em] text-[#B6965D] mt-2">Atelier High Jewellery</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Bracelets -->
                <div
                  class="relative aspect-[4/5] group overflow-hidden cursor-pointer"
                  @click="router.push('/catalogue?category=bracelets-bangles')"
                >
                  <img src="/assets/category-bracelet.webp" alt="Bracelets" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                  <div class="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition-colors flex flex-col items-center justify-center text-white">
                    <h3 class="font-playfair text-4xl mb-4">Bracelets</h3>
                    <span class="border border-white/40 bg-white/10 backdrop-blur-xs px-6 py-2 min-h-[40px] text-[10px] tracking-[0.2em] uppercase font-azeret font-bold inline-flex items-center justify-center gap-2.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                      <span>SEE DETAILS</span>
                    </span>
                  </div>
                </div>

                <!-- Earrings -->
                <div
                  class="relative aspect-[4/5] group overflow-hidden cursor-pointer"
                  @click="router.push('/catalogue?category=earrings')"
                >
                  <img src="/assets/category-earrings.webp" alt="Earrings" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                  <div class="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition-colors flex flex-col items-center justify-center text-white">
                    <h3 class="font-playfair text-4xl mb-4">Earrings</h3>
                    <span class="border border-white/40 bg-white/10 backdrop-blur-xs px-6 py-2 min-h-[40px] text-[10px] tracking-[0.2em] uppercase font-azeret font-bold inline-flex items-center justify-center gap-2.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                      <span>SEE DETAILS</span>
                    </span>
                  </div>
                </div>

                <!-- Rings -->
                <div
                  class="relative aspect-[4/5] group overflow-hidden cursor-pointer"
                  @click="router.push('/catalogue?category=rings')"
                >
                  <img src="/assets/category-rings.webp" alt="Rings" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                  <div class="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition-colors flex flex-col items-center justify-center text-white">
                    <h3 class="font-playfair text-4xl mb-4">Rings</h3>
                    <span class="border border-white/40 bg-white/10 backdrop-blur-xs px-6 py-2 min-h-[40px] text-[10px] tracking-[0.2em] uppercase font-azeret font-bold inline-flex items-center justify-center gap-2.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                      <span>SEE DETAILS</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 5: LEGACY IN EVERY DETAIL              -->
          <!-- ============================================== -->
          <section class="relative min-h-[80vh] flex bg-[#F8F8F8] overflow-hidden py-24 border-b border-[#E2DDD5]" id="legacy">
            <div class="absolute inset-0 opacity-40">
              <img src="/assets/legacy-section.png" alt="Legacy Background" class="w-full h-full object-cover object-top" />
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 w-full flex flex-col justify-between">
              <div class="flex flex-col lg:flex-row items-start justify-between gap-12">
                <div class="lg:w-1/2">
                  <h2 class="font-playfair text-5xl sm:text-7xl lg:text-8xl text-[#1A1A1A] leading-tight mb-8">
                    Legacy In Every<br />Detail
                  </h2>
                  <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                      <div class="flex gap-1.5">
                        <span class="w-2.5 h-2.5 bg-black rounded-full"></span>
                        <span class="w-2.5 h-2.5 bg-black rounded-full"></span>
                      </div>
                      <span class="text-xs uppercase tracking-[0.4em] text-stone-500 font-azeret">[HERITAGE]</span>
                    </div>

                    <!-- Revolving Atelier Seal Emblem (from Main Web) -->
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center select-none pointer-events-none">
                      <svg class="w-full h-full animate-rotate-text" viewBox="0 0 100 100">
                        <path id="ecloreSealPath" d="M 50, 50 m -36, 0 a 36,36 0 1,1 72,0 a 36,36 0 1,1 -72,0" fill="none" />
                        <text class="text-[8px] uppercase tracking-[0.22em] font-azeret fill-[#B6965D]">
                          <textPath href="#ecloreSealPath">• ÉCLORE • MANILA ATELIER • EST. 2024</textPath>
                        </text>
                      </svg>
                      <span class="absolute w-2 h-2 rounded-full bg-[#B6965D]"></span>
                    </div>
                  </div>
                </div>

                <div class="lg:w-1/3 space-y-8">
                  <p class="font-azeret text-xs sm:text-sm text-stone-700 uppercase tracking-widest leading-loose">
                    EACH PIECE IS CREATED WITH RESPECT FOR TIME AND TRADITION. OUR ARTISANS PRESERVE THE ESSENCE OF FINE JEWELRY CRAFTSMANSHIP, BLENDING HERITAGE TECHNIQUES WITH MODERN PRECISION.
                  </p>
                  <div>
                    <button
                      type="button"
                      class="eclore-cta-btn bg-black hover:bg-stone-900 text-white px-10 py-4 min-h-[50px] text-xs uppercase tracking-[0.25em] font-azeret font-bold transition-colors inline-flex items-center justify-center gap-3.5 shadow-md active:scale-95 group"
                      @click="router.push('/catalogue')"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
                      <span>ABOUT ÉCLORE</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 6: YOUR STORY YOUR MASTERPIECE         -->
          <!-- ============================================== -->
          <section class="relative min-h-[90vh] flex flex-col justify-between py-24 bg-black text-white overflow-hidden border-b border-white/10" id="masterpiece">
            <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('/assets/story-section.webp')"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 w-full flex-grow flex flex-col justify-between">
              <!-- Header -->
              <div>
                <span class="font-azeret text-xs uppercase tracking-[0.4em] text-[#B6965D] block mb-3">[BESPOKE ATELIER]</span>
                <h2 class="font-playfair text-4xl sm:text-6xl lg:text-7xl text-white">
                  Your Story.<br />Your Masterpiece.
                </h2>
              </div>

              <!-- Interactive Selector & Preview -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center my-12">
                <!-- Selector List -->
                <div class="divide-y divide-white/10">
                  <button
                    v-for="opt in masterpieceOptions"
                    :key="opt.id"
                    type="button"
                    class="w-full py-6 text-left flex items-center justify-between group transition-all"
                    :class="activeMasterpiece.id === opt.id ? 'text-white' : 'text-white/40 hover:text-white/80'"
                    @click="selectMasterpiece(opt)"
                  >
                    <span
                      class="font-playfair text-2xl sm:text-4xl transition-all duration-300"
                      :class="activeMasterpiece.id === opt.id ? 'translate-x-3 text-white' : 'group-hover:translate-x-2'"
                    >
                      {{ opt.label }}
                    </span>
                    <span
                      class="text-sm font-azeret text-[#B6965D] transition-opacity duration-300"
                      :class="activeMasterpiece.id === opt.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                    >
                      Select &rarr;
                    </span>
                  </button>
                </div>

                <!-- Preview Circle with 360 Rotation Animation -->
                <div class="flex items-center justify-center my-6">
                  <div
                    class="relative w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-white shadow-2xl p-8 flex items-center justify-center overflow-hidden will-change-transform"
                    :style="{ transform: `rotate(${circleRotation}deg)` }"
                    style="transition: transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);"
                  >
                    <!-- Decorative Dots on the Rotating Circle -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#1A1A1A] rounded-full opacity-60"></div>
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#1A1A1A] rounded-full opacity-60"></div>
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#1A1A1A] rounded-full opacity-60"></div>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#1A1A1A] rounded-full opacity-60"></div>

                    <!-- Inner Product Image Transition -->
                    <div class="w-full h-full flex items-center justify-center relative">
                      <Transition name="masterpiece-fade" mode="out-in">
                        <img
                          :key="activeMasterpiece.img"
                          :src="activeMasterpiece.img"
                          :alt="activeMasterpiece.label"
                          class="max-w-full max-h-full object-contain p-2 select-none pointer-events-none drop-shadow-md"
                        />
                      </Transition>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CTA -->
              <div class="flex justify-center">
                <button
                  type="button"
                  class="eclore-cta-btn bg-white hover:bg-stone-100 text-black px-10 py-4 min-h-[50px] text-xs font-azeret uppercase tracking-[0.25em] font-bold transition-all inline-flex items-center justify-center gap-3.5 shadow-xl active:scale-95 group"
                  @click="router.push('/catalogue')"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                  <span>CREATE YOUR DESIGN</span>
                </button>
              </div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 7: ÉCLORE EXPERIENCE                  -->
          <!-- ============================================== -->
          <section class="bg-[#FCFCFC] py-24 border-b border-[#E2DDD5]" id="experience">
            <div class="max-w-7xl mx-auto px-6">
              <h2 class="font-playfair text-4xl sm:text-5xl text-center mb-16 text-[#1A1A1A]">· Éclore Experience ·</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                <!-- Appointment -->
                <div class="flex flex-col items-center">
                  <div class="w-24 h-24 mb-6 flex items-center justify-center">
                    <img src="/assets/appointment.webp" alt="Appointment" class="max-h-full object-contain" />
                  </div>
                  <h3 class="font-playfair text-2xl mb-3">Book An Appointment</h3>
                  <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest leading-relaxed mb-6 max-w-xs">
                    DISCOVER HOW TO MAINTAIN AND PRESERVE YOUR CREATIONS FOR YEARS TO COME.
                  </p>
                  <button
                    type="button"
                    class="eclore-cta-btn bg-white border border-[#E2DDD5] hover:border-[#1A1A1A] hover:bg-stone-50 text-[#1A1A1A] px-8 py-3.5 min-h-[48px] text-[11px] font-azeret uppercase tracking-[0.2em] font-bold transition-all inline-flex items-center justify-center gap-3 shadow-sm hover:shadow-md active:scale-95 group"
                    @click="router.push('/appointments')"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                    <span>BOOK NOW</span>
                  </button>
                </div>

                <!-- Shipping -->
                <div class="flex flex-col items-center">
                  <div class="w-24 h-24 mb-6 flex items-center justify-center">
                    <img src="/assets/shipping.webp" alt="Shipping" class="max-h-full object-contain" />
                  </div>
                  <h3 class="font-playfair text-2xl mb-3">Shipping & Returns</h3>
                  <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest leading-relaxed mb-6 max-w-xs">
                    ENJOY EXCLUSIVE PREVIEWS, PRIVATE EVENTS, AND TIMELESS PRIVILEGES.
                  </p>
                  <button
                    type="button"
                    class="eclore-cta-btn bg-white border border-[#E2DDD5] hover:border-[#1A1A1A] hover:bg-stone-50 text-[#1A1A1A] px-8 py-3.5 min-h-[48px] text-[11px] font-azeret uppercase tracking-[0.2em] font-bold transition-all inline-flex items-center justify-center gap-3 shadow-sm hover:shadow-md active:scale-95 group"
                    @click="router.push('/orders-payments')"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                    <span>LEARN MORE</span>
                  </button>
                </div>

                <!-- Service -->
                <div class="flex flex-col items-center">
                  <div class="w-24 h-24 mb-6 flex items-center justify-center">
                    <img src="/assets/service.webp" alt="Service" class="max-h-full object-contain" />
                  </div>
                  <h3 class="font-playfair text-2xl mb-3">At Your Service</h3>
                  <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest leading-relaxed mb-6 max-w-xs">
                    PERSONALIZE STRAPS, DETAILS, OR ENGRAVINGS TO MAKE EACH PIECE UNIQUE.
                  </p>
                  <button
                    type="button"
                    class="eclore-cta-btn bg-white border border-[#E2DDD5] hover:border-[#1A1A1A] hover:bg-stone-50 text-[#1A1A1A] px-8 py-3.5 min-h-[48px] text-[11px] font-azeret uppercase tracking-[0.2em] font-bold transition-all inline-flex items-center justify-center gap-3 shadow-sm hover:shadow-md active:scale-95 group"
                    @click="router.push('/help')"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-black inline-block flex-shrink-0"></span>
                    <span>CONTACT US</span>
                  </button>
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================== -->
          <!-- SECTION 8: PARTNERS & FRIENDS                 -->
          <!-- ============================================== -->
          <section class="bg-[#0F110E] py-24 text-white">
            <div class="max-w-7xl mx-auto px-6 text-center">
              <p class="font-azeret text-xs text-white/40 uppercase tracking-[0.3em] mb-12">
                OUR PARTNERS ARE PART OF THE SAME QUIET ELEGANCE WE EMBODY. TOGETHER, WE SHAPE THE LANGUAGE OF MODERN LUXURY.
              </p>

              <div class="flex flex-col md:flex-row items-center justify-center gap-8 mb-16">
                <h2 class="font-playfair text-4xl sm:text-6xl text-white">Partners</h2>
                <div class="w-64 sm:w-80 h-32 overflow-hidden border border-white/20 relative bg-black/40">
                  <Transition name="partner-fade" mode="out-in">
                    <img :key="activePartner.img" :src="activePartner.img" :alt="activePartner.name" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
                  </Transition>
                </div>
                <h2 class="font-playfair text-4xl sm:text-6xl text-white">& Friends</h2>
              </div>

              <!-- Partner Buttons -->
              <div class="flex flex-wrap justify-center gap-4">
                <button
                  v-for="p in partnerList"
                  :key="p.name"
                  type="button"
                  class="w-24 h-24 sm:w-28 sm:h-28 border flex items-center justify-center p-4 transition-all duration-300 active:scale-95"
                  :class="activePartner.name === p.name ? 'border-[#B6965D] bg-white/10 opacity-100 ring-1 ring-[#B6965D]' : 'border-white/15 opacity-50 grayscale hover:grayscale-0 hover:opacity-100 hover:border-white/30'"
                  @click="activePartner = p"
                >
                  <img :src="p.logo" :alt="p.name" class="max-w-full max-h-full object-contain transition-all duration-300" />
                </button>
              </div>
            </div>
          </section>
        </main>

        <!-- Footer -->
        <EcloreFooter />

        <!-- Search Modal -->
        <SearchModal :is-open="isSearchOpen" @close="isSearchOpen = false" />

        <!-- Quick View Modal -->
        <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
      </div>
    </ion-content>
  </ion-page>
</template>

<style scoped>
.carousel-pedestal-enter-active,
.carousel-pedestal-leave-active {
  transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}
.carousel-pedestal-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.95);
}
.carousel-pedestal-leave-to {
  opacity: 0;
  transform: translateY(-16px) scale(1.02);
}

.masterpiece-fade-enter-active,
.masterpiece-fade-leave-active,
.partner-fade-enter-active,
.partner-fade-leave-active {
  transition: opacity 0.35s ease, transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.masterpiece-fade-enter-from,
.masterpiece-fade-leave-to {
  opacity: 0;
  transform: scale3d(0.95, 0.95, 1);
}
.partner-fade-enter-from,
.partner-fade-leave-to {
  opacity: 0;
  transform: scale3d(1.04, 1.04, 1);
}
</style>
