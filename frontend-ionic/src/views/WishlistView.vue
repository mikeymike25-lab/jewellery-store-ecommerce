<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { IonPage, IonContent } from '@ionic/vue';
import { useRouter } from 'vue-router';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useCartStore } from '@/stores/cart.store';
import { useAuthStore } from '@/stores/auth.store';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import { getProductImage as resolveImg } from '@/utils/product-image';

const router = useRouter();
const wishlistStore = useWishlistStore();
const cartStore = useCartStore();
const authStore = useAuthStore();

const items = computed(() => wishlistStore.items);
const loading = computed(() => wishlistStore.loading);
const itemCount = computed(() => wishlistStore.itemCount);

onMounted(async () => {
  await wishlistStore.fetchWishlist();
});

function resolveImage(item: any): string {
  return resolveImg(item.product || item);
}

function formatPrice(amount?: number): string {
  if (!amount) return '0.00';
  return amount.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function handleMoveToCart(item: any) {
  const pId = item.product_id || item.product?.id;
  await cartStore.addToCart(pId, 1);
  await wishlistStore.remove(pId);
}

async function handleRemove(item: any) {
  const pId = item.product_id || item.product?.id;
  await wishlistStore.remove(pId);
}

async function handleClearAll() {
  await wishlistStore.clearWishlist();
}
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-28 w-full">
          <!-- Back Navigation Button -->
          <div class="mb-6">
            <button
              type="button"
              class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-600 hover:text-[#B6965D] transition-colors py-1.5 group"
              @click="router.push('/catalogue')"
            >
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to Atelier</span>
            </button>
          </div>

          <!-- Page Header -->
          <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-[#E2DDD5] pb-6 mb-8 gap-4">
            <div>
              <span class="font-azeret text-xs uppercase tracking-[0.25em] text-[#B6965D]">Private Curation</span>
              <h1 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mt-2">
                Your Saved Masterpieces
              </h1>
            </div>
            <div v-if="items.length > 0" class="flex items-center space-x-3">
              <span class="font-azeret text-xs text-stone-500">
                {{ itemCount }} {{ itemCount === 1 ? 'Creation' : 'Creations' }}
              </span>
              <button
                type="button"
                class="font-azeret text-xs uppercase tracking-wider text-rose-600 hover:text-rose-800 transition-colors py-2 px-3 rounded hover:bg-rose-50"
                @click="handleClearAll"
              >
                Clear All
              </button>
            </div>
          </div>

          <!-- Guest Session Sync Notice Banner -->
          <div v-if="!authStore.isAuthenticated" class="bg-white/80 border border-[#B6965D]/30 p-4 mb-8 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
              <span class="text-[#B6965D] text-base">✦</span>
              <span class="font-azeret text-xs text-stone-700">
                You are curating a guest collection. Sign in anytime to sync your favorites across all devices.
              </span>
            </div>
            <button
              type="button"
              class="font-azeret text-xs uppercase tracking-wider font-bold text-[#1A1A1A] hover:text-[#B6965D] underline shrink-0 transition-colors"
              @click="authStore.setIntendedRoute('/wishlist'); router.push('/tabs/account')"
            >
              Sign In / Register &rarr;
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="loading && items.length === 0" class="text-center py-24 font-azeret text-xs uppercase tracking-widest text-stone-500">
            Fetching your private curation...
          </div>

          <!-- Empty State -->
          <div v-else-if="items.length === 0" class="bg-white border border-[#E2DDD5] p-12 sm:p-16 text-center space-y-4 max-w-2xl mx-auto">
            <div class="w-16 h-16 mx-auto rounded-full bg-[#F8F7F2] border border-[#E2DDD5] flex items-center justify-center text-[#B6965D]">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </div>
            <h2 class="font-playfair text-xl text-[#1A1A1A]">Your wishlist is empty</h2>
            <p class="font-azeret text-xs text-stone-500 max-w-sm mx-auto">
              Save creations you admire to view them privately or move them directly into your coffret.
            </p>
            <div class="pt-2">
              <EcloreButton variant="primary" size="md" @click="router.push('/catalogue')">
                Explore The Atelier
              </EcloreButton>
            </div>
          </div>

          <!-- Wishlist Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <article
              v-for="item in items"
              :key="item.id"
              class="bg-white border border-[#E2DDD5] rounded-lg overflow-hidden group hover:shadow-lg transition-all flex flex-col justify-between"
            >
              <!-- Card Image -->
              <div
                class="relative aspect-square bg-[#F8F7F2] p-6 flex items-center justify-center cursor-pointer overflow-hidden"
                @click="router.push(`/product/${item.product?.slug || item.product_id}`)"
              >
                <img
                  :src="resolveImage(item)"
                  :alt="item.product?.name"
                  class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105"
                  onerror="this.src='/assets/1.png'"
                />
                <!-- Remove Heart Trigger -->
                <button
                  type="button"
                  class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 border border-stone-200 flex items-center justify-center text-rose-500 hover:bg-rose-50 transition-colors shadow-sm"
                  aria-label="Remove from wishlist"
                  @click.stop="handleRemove(item)"
                >
                  <svg class="w-4 h-4 fill-rose-500" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                  </svg>
                </button>
              </div>

              <!-- Content & Actions -->
              <div class="p-5 flex flex-col flex-grow justify-between space-y-4">
                <div>
                  <span v-if="item.product?.category?.name" class="font-azeret text-[10px] uppercase tracking-wider text-stone-500 block">
                    {{ item.product.category.name }}
                  </span>
                  <h3
                    class="font-playfair text-base font-semibold text-[#1A1A1A] truncate cursor-pointer hover:text-[#B6965D] mt-1"
                    @click="router.push(`/product/${item.product?.slug || item.product_id}`)"
                  >
                    {{ item.product?.name || 'Masterpiece' }}
                  </h3>
                  <p class="font-azeret text-sm font-bold text-[#1A1A1A] mt-2">
                    ₱{{ formatPrice(item.product?.price || item.product?.base_price) }}
                  </p>
                </div>

                <div class="pt-2">
                  <EcloreButton
                    variant="primary"
                    size="md"
                    block
                    @click="handleMoveToCart(item)"
                  >
                    <svg class="w-4 h-4 mr-2 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Move To Coffret</span>
                  </EcloreButton>
                </div>
              </div>
            </article>
          </div>
        </main>

        <EcloreFooter />
      </div>
    </ion-content>
  </ion-page>
</template>
