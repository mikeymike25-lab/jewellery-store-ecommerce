<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import EcloreModal from '@/components/EcloreModal.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import type { Product } from '@/types';
import { getProductImage } from '@/utils/product-image';

interface Props {
  isOpen: boolean;
  product: Product | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'close'): void;
}>();

const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const selectedSize = ref('7');
const adding = ref(false);
const addedSuccess = ref(false);

async function handleAddToCart() {
  if (!props.product) return;
  adding.value = true;
  try {
    await cartStore.addToCart(props.product.id, 1, { ring_size: selectedSize.value });
    addedSuccess.value = true;
    setTimeout(() => {
      addedSuccess.value = false;
      emit('close');
    }, 1000);
  } catch (err) {
    console.error('Failed to add to cart:', err);
  } finally {
    adding.value = false;
  }
}

async function handleToggleWishlist() {
  if (!props.product) return;
  try {
    await wishlistStore.toggleItem(props.product);
  } catch (err) {
    console.error('Failed to toggle wishlist:', err);
  }
}

function viewFullDetails() {
  if (!props.product) return;
  emit('close');
  router.push(`/products/${props.product.slug || props.product.id}`);
}
</script>

<template>
  <EcloreModal
    :is-open="isOpen"
    :title="product?.name || 'Quick View'"
    subtitle="Atelier Creation"
    max-width="max-w-2xl"
    @close="emit('close')"
  >
    <div v-if="product" class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
      <!-- Image -->
      <div class="aspect-square bg-stone-50 border border-[#E2DDD5] flex items-center justify-center overflow-hidden p-4">
        <img :src="getProductImage(product)" :alt="product.name" class="w-full h-full object-contain" />
      </div>

      <!-- Details -->
      <div class="space-y-4 flex flex-col justify-between">
        <div>
          <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">
            {{ product.category?.name || 'Fine Jewellery' }}
          </span>
          <h3 class="font-playfair text-2xl text-[#1A1A1A] mt-1">{{ product.name }}</h3>
          
          <div class="mt-3">
            <CurrencyDisplay :amount="product.final_price || product.price || product.base_price" size="xl" bold />
            <span class="ml-2 font-azeret text-[10px] text-stone-500 uppercase">VAT Included</span>
          </div>

          <p class="font-azeret text-xs text-stone-600 mt-3 line-clamp-3 leading-relaxed">
            {{ product.description || 'Masterfully handcrafted with certified materials in our private atelier.' }}
          </p>

          <!-- Ring Size Selector -->
          <div class="mt-4">
            <span class="block font-azeret text-[10px] uppercase tracking-wider text-stone-600 mb-2">Size</span>
            <div class="flex gap-2">
              <button
                v-for="size in ['5', '6', '7', '8', '9']"
                :key="size"
                type="button"
                class="w-8 h-8 text-xs font-azeret border transition-all"
                :class="selectedSize === size ? 'border-[#B6965D] bg-[#B6965D] text-white' : 'border-[#E2DDD5] bg-white text-stone-800 hover:border-black'"
                @click="selectedSize = size"
              >
                {{ size }}
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-2 pt-4">
          <div class="flex items-center gap-3">
            <div class="relative flex-grow">
              <EcloreButton variant="primary" block :loading="adding" @click="handleAddToCart">
                <span v-if="addedSuccess" class="inline-flex items-center gap-2 text-amber-200">
                  <span>Added to Bag</span>
                  <svg class="w-4 h-4 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </span>
                <span v-else>Add To Shopping Bag</span>
              </EcloreButton>

              <!-- Sparkle Star Animation Overlay -->
              <div
                v-if="addedSuccess"
                class="absolute -top-3.5 -right-3 z-30 pointer-events-none animate-sparkle"
              >
                <svg class="w-7 h-7 text-[#B6965D] filter drop-shadow-[0_0_8px_rgba(182,150,93,0.9)]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 0l2.8 8.4L23.2 11.2l-8.4 2.8L12 22.4l-2.8-8.4L0.8 11.2l8.4-2.8z" />
                </svg>
              </div>
            </div>

            <!-- Wishlist Heart Button -->
            <button
              type="button"
              class="btn-wishlist-bounce min-h-[48px] w-12 px-3 flex items-center justify-center border border-[#E2DDD5] bg-white rounded text-stone-700 hover:border-[#B6965D] hover:text-[#B6965D] transition-colors shrink-0 shadow-sm"
              :class="props.product && wishlistStore.isInWishlist(props.product.id) ? 'text-rose-500 border-rose-300' : ''"
              aria-label="Save to favorites"
              @click="handleToggleWishlist"
            >
              <svg class="w-5 h-5" :class="props.product && wishlistStore.isInWishlist(props.product.id) ? 'fill-rose-500 text-rose-500' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </button>
          </div>

          <EcloreButton variant="ghost" size="sm" block @click="viewFullDetails">
            View Full Atelier Specifications &rarr;
          </EcloreButton>
        </div>
      </div>
    </div>
  </EcloreModal>
</template>
