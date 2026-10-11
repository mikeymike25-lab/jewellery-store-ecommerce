<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useCartStore } from '@/stores/cart.store';
import EcloreButton from '@/components/EcloreButton.vue';
import { getProductImage as resolveImg } from '@/utils/product-image';

const router = useRouter();
const wishlistStore = useWishlistStore();
const cartStore = useCartStore();

const isOpen = computed(() => wishlistStore.isOpen);
const items = computed(() => wishlistStore.items);
const itemCount = computed(() => wishlistStore.itemCount);

function close() {
  wishlistStore.closeWishlist();
}

function resolveItemImage(item: any): string {
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
  try {
    const pId = item.product_id || item.product?.id;
    await cartStore.addToCart(pId, 1);
    await wishlistStore.remove(pId);
  } catch (e) {
    console.error('Failed to move item to coffret:', e);
  }
}

async function handleRemove(productId: number) {
  await wishlistStore.remove(productId);
}

async function handleClearAll() {
  await wishlistStore.clearWishlist();
}
</script>

<template>
  <!-- Offcanvas Wishlist Drawer with GPU-Accelerated Transition -->
  <Transition name="drawer-slide">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex justify-end"
      role="dialog"
      aria-modal="true"
      aria-labelledby="offcanvasWishlistLabel"
    >
    <!-- Backdrop Overlay -->
    <div
      class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"
      @click="close"
    />

    <!-- Sliding Wishlist Panel -->
    <aside
      class="relative w-full max-w-md bg-[#F8F7F2] h-full shadow-2xl flex flex-col z-10 border-l border-[#E0E0D9] transform transition-transform duration-300 ease-in-out"
    >
      <!-- Header -->
      <header class="flex items-center justify-between px-6 py-5 border-b border-[#E0E0D9] bg-[#F8F7F2] shrink-0">
        <div class="flex items-baseline space-x-2">
          <h2 id="offcanvasWishlistLabel" class="font-playfair text-lg text-[#1A1A1A] font-semibold tracking-wide">
            Your Favorites
          </h2>
          <span class="font-azeret text-xs text-[#B6965D] font-bold">
            ({{ itemCount }})
          </span>
        </div>

        <button
          type="button"
          class="w-8 h-8 rounded-full border border-stone-300 hover:border-stone-500 hover:bg-stone-200/50 flex items-center justify-center text-stone-600 hover:text-black transition-all"
          aria-label="Close favorites"
          @click="close"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </header>

      <!-- Body / Scrollable Content -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
        <!-- Empty State -->
        <div v-if="items.length === 0" class="py-16 text-center space-y-4">
          <div class="w-16 h-16 mx-auto rounded-full bg-[#EFECE6] border border-[#E0E0D9] flex items-center justify-center text-[#B6965D]">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
          </div>
          <p class="font-playfair text-lg text-stone-800">No favorites yet.</p>
          <p class="font-azeret text-xs text-stone-500 max-w-xs mx-auto">
            Curate your bespoke selection by tapping the heart icon on your favorite pieces.
          </p>
          <div class="pt-4">
            <EcloreButton
              variant="primary"
              size="md"
              class="mx-auto"
              @click="close(); router.push('/catalogue')"
            >
              Explore Creations
            </EcloreButton>
          </div>
        </div>

        <!-- Wishlist Items List -->
        <div v-else class="space-y-3">
          <article
            v-for="item in items"
            :key="item.id"
            class="bg-white border border-[#E2DDD5] rounded-lg p-3.5 transition-all shadow-sm hover:shadow-md flex items-start space-x-3.5"
          >
            <!-- Product Thumbnail -->
            <div class="w-20 h-20 bg-[#F3EFE7] rounded border border-stone-200 overflow-hidden shrink-0 flex items-center justify-center cursor-pointer" @click="close(); router.push(`/product/${item.product?.slug || item.product_id}`)">
              <img
                :src="resolveItemImage(item)"
                :alt="item.product?.name || 'Jewellery'"
                class="w-full h-full object-contain p-1"
                onerror="this.src='/assets/1.png'"
              />
            </div>

            <!-- Product Details -->
            <div class="flex-1 min-w-0">
              <h3
                class="font-playfair text-sm font-semibold text-[#1A1A1A] truncate cursor-pointer hover:text-[#B6965D]"
                @click="close(); router.push(`/product/${item.product?.slug || item.product_id}`)"
              >
                {{ item.product?.name || 'Bespoke Masterpiece' }}
              </h3>

              <p class="font-azeret text-xs font-semibold text-[#1A1A1A] mt-1 mb-3">
                ₱{{ formatPrice(item.product?.price || item.product?.base_price) }}
              </p>

              <!-- Actions Row -->
              <div class="flex items-center space-x-2 pt-1">
                <button
                  type="button"
                  class="flex-1 bg-[#B6965D] hover:bg-[#9A7E4D] active:bg-[#846B40] text-white font-azeret text-[11px] font-semibold uppercase tracking-[0.14em] py-2.5 px-3 rounded shadow-xs transition-all text-center flex items-center justify-center gap-1.5 active:scale-[0.98]"
                  @click="handleMoveToCart(item)"
                >
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  <span>Move To Coffret</span>
                </button>
                <button
                  type="button"
                  class="w-9 h-9 rounded flex items-center justify-center text-stone-400 hover:text-rose-600 hover:bg-rose-50 border border-stone-200 hover:border-rose-200 transition-colors shrink-0"
                  aria-label="Remove"
                  @click="handleRemove(item.product_id || item.product?.id)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- Offcanvas Footer -->
      <footer
        v-if="items.length > 0"
        class="border-t border-[#E0E0D9] bg-[#F5F5F0] px-6 py-4 shrink-0"
      >
        <button
          type="button"
          class="w-full min-h-[48px] py-3.5 px-4 my-1 border border-rose-300 text-rose-700 hover:bg-rose-50 rounded font-azeret text-xs uppercase tracking-wider font-semibold transition-all flex items-center justify-center space-x-2 active:scale-[0.99]"
          @click="handleClearAll"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
          </svg>
          <span>Clear All Favorites</span>
        </button>
      </footer>
    </aside>
  </div>
  </Transition>
</template>
