<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart.store';
import EcloreButton from '@/components/EcloreButton.vue';

const router = useRouter();
const cartStore = useCartStore();

const isOpen = computed(() => cartStore.isOpen);
const items = computed(() => cartStore.items);
const selectedIds = computed(() => cartStore.selectedIds);
const allSelected = computed(() => cartStore.allSelected);
const subtotal = computed(() => cartStore.subtotal);
const vatAmount = computed(() => cartStore.vatAmount);
const selectedCount = computed(() => cartStore.selectedCount);

function close() {
  cartStore.closeCart();
}

function handleSelectAll() {
  cartStore.selectAll(!allSelected.value);
}

function resolveItemImage(item: any): string {
  if (item.product?.primary_image) return item.product.primary_image;
  if (item.product?.images && item.product.images.length > 0) {
    const first = item.product.images[0];
    return typeof first === 'string' ? first : first.image_url;
  }
  if (item.product_data?.image) return item.product_data.image;
  return '/assets/1.png';
}

function formatPrice(amount: number): string {
  return amount.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function proceedToCheckout() {
  if (selectedCount.value === 0) return;
  cartStore.closeCart();
  router.push('/checkout');
}

function goToCart() {
  cartStore.closeCart();
  router.push('/cart');
}
</script>

<template>
  <!-- Offcanvas Shopping Bag Drawer with GPU-Accelerated Transition -->
  <Transition name="drawer-slide">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex justify-end"
      role="dialog"
      aria-modal="true"
      aria-labelledby="offcanvasCartLabel"
    >
    <!-- Backdrop Overlay -->
    <div
      class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"
      @click="close"
    />

    <!-- Sliding Bag Panel -->
    <aside
      class="relative w-full max-w-md bg-[#F8F7F2] h-full shadow-2xl flex flex-col z-10 border-l border-[#E0E0D9] transform transition-transform duration-300 ease-in-out"
    >
      <!-- Header -->
      <header class="flex items-center justify-between px-6 py-5 border-b border-[#E0E0D9] bg-[#F8F7F2] shrink-0">
        <div class="flex items-baseline space-x-2">
          <h2 id="offcanvasCartLabel" class="font-playfair text-lg text-[#1A1A1A] font-semibold tracking-wide">
            Your Shopping Bag
          </h2>
          <span class="font-azeret text-xs text-[#B6965D] font-bold">
            ({{ cartStore.itemCount }})
          </span>
        </div>

        <button
          type="button"
          class="w-8 h-8 rounded-full border border-stone-300 hover:border-stone-500 hover:bg-stone-200/50 flex items-center justify-center text-stone-600 hover:text-black transition-all"
          aria-label="Close bag"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.67 0-1.19-.578-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119.993z" />
            </svg>
          </div>
          <p class="font-playfair text-lg text-stone-800">Your bag is currently empty.</p>
          <p class="font-azeret text-xs text-stone-500 max-w-xs mx-auto">
            Discover handcrafted 18K fine jewellery created with bespoke precision.
          </p>
          <div class="pt-4">
            <EcloreButton
              variant="primary"
              size="md"
              class="mx-auto"
              @click="close(); router.push('/catalogue')"
            >
              Explore The Atelier
            </EcloreButton>
          </div>
        </div>

        <!-- Item Cards List -->
        <div v-else class="space-y-3">
          <!-- Selection Bar -->
          <div class="flex items-center justify-between pb-2 border-b border-stone-200/70 text-xs font-azeret text-stone-600">
            <button
              type="button"
              class="select-all-btn inline-flex items-center space-x-2 text-[#1A1A1A] hover:text-[#B6965D] font-medium transition-colors"
              @click="handleSelectAll"
            >
              <input
                type="checkbox"
                class="cart-item-checkbox w-4 h-4 rounded text-[#B6965D] border-stone-300 focus:ring-[#B6965D] cursor-pointer"
                :checked="allSelected"
                @click.stop="handleSelectAll"
              />
              <span>{{ allSelected ? 'Deselect All' : 'Select All Items' }}</span>
            </button>
            <span class="text-stone-500 text-[11px]">
              {{ selectedCount }} of {{ items.length }} selected
            </span>
          </div>

          <!-- Items loop -->
          <article
            v-for="item in items"
            :key="item.id"
            class="cart-item-new bg-white border border-[#E2DDD5] rounded-lg p-3.5 transition-all shadow-sm hover:shadow-md"
            :data-product-id="item.product_id"
          >
            <div class="flex items-start space-x-3.5">
              <!-- Item Selection Checkbox -->
              <div class="pt-1">
                <input
                  type="checkbox"
                  class="cart-item-checkbox item-checkbox w-4 h-4 rounded text-[#B6965D] border-stone-300 focus:ring-[#B6965D] cursor-pointer"
                  :checked="selectedIds.includes(item.id)"
                  :data-product-id="item.product_id"
                  :data-item-total="item.unit_price * item.quantity"
                  @change="cartStore.toggleItemSelection(item.id)"
                />
              </div>

              <!-- Product Image Thumbnail -->
              <div class="w-20 h-20 bg-[#F3EFE7] rounded border border-stone-200 overflow-hidden shrink-0 flex items-center justify-center">
                <img
                  :src="resolveItemImage(item)"
                  :alt="item.product_name"
                  class="w-full h-full object-contain p-1"
                  onerror="this.src='/assets/1.png'"
                />
              </div>

              <!-- Item Details -->
              <div class="flex-1 min-w-0">
                <h3 class="font-playfair text-sm font-semibold text-[#1A1A1A] truncate">
                  {{ item.product_name }}
                </h3>

                <!-- Variant Attributes (Size, Metal) -->
                <div class="font-azeret text-[10px] text-stone-500 space-x-2 mt-0.5 mb-1.5 flex flex-wrap gap-1">
                  <span v-if="item.ring_size || item.product_data?.size" class="bg-stone-100 px-1.5 py-0.5 rounded border border-stone-200">
                    Size: {{ item.ring_size || item.product_data?.size }}
                  </span>
                  <span v-if="item.metal_type || item.product_data?.metal_type" class="bg-stone-100 px-1.5 py-0.5 rounded border border-stone-200">
                    {{ item.metal_type || item.product_data?.metal_type }}
                  </span>
                </div>

                <!-- Unit Price -->
                <p class="font-azeret text-xs font-semibold text-[#1A1A1A] mb-2">
                  ₱{{ formatPrice(item.unit_price) }}
                </p>

                <!-- Stepper + Actions Row -->
                <div class="flex items-center justify-between">
                  <!-- Stepper -->
                  <div class="quantity-selector inline-flex items-center border border-stone-300 rounded bg-[#F8F7F2]">
                    <button
                      type="button"
                      class="qty-btn w-7 h-7 flex items-center justify-center text-stone-700 hover:text-black font-bold text-xs"
                      aria-label="Decrease quantity"
                      @click="cartStore.updateQuantity(item.product_id, item.quantity - 1)"
                    >
                      &minus;
                    </button>
                    <span class="qty-display px-2.5 font-azeret text-xs font-bold text-stone-800">
                      {{ item.quantity }}
                    </span>
                    <button
                      type="button"
                      class="qty-btn w-7 h-7 flex items-center justify-center text-stone-700 hover:text-black font-bold text-xs"
                      aria-label="Increase quantity"
                      @click="cartStore.updateQuantity(item.product_id, item.quantity + 1)"
                    >
                      &#43;
                    </button>
                  </div>

                  <!-- Remove Button -->
                  <button
                    type="button"
                    class="remove-btn text-[11px] font-azeret text-stone-400 hover:text-rose-600 transition-colors p-1"
                    @click="cartStore.removeItem(item.product_id)"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <!-- Line Total Bottom Bar -->
            <div class="mt-2.5 pt-2 border-t border-stone-100 flex items-center justify-between font-azeret text-[11px] text-stone-500">
              <span>Item Total</span>
              <span class="font-semibold text-stone-900">
                ₱{{ formatPrice(item.unit_price * item.quantity) }}
              </span>
            </div>
          </article>
        </div>
      </div>

      <!-- Offcanvas Footer -->
      <footer
        v-if="items.length > 0"
        class="offcanvas-footer border-t border-[#E0E0D9] bg-[#F5F5F0] px-6 py-5 shrink-0 space-y-3"
      >
        <!-- Subtotal Row -->
        <div class="flex items-center justify-between font-playfair text-base text-[#1A1A1A]">
          <span class="font-medium">Subtotal</span>
          <span id="cart-subtotal" class="font-bold text-lg text-[#1A1A1A]">
            ₱{{ formatPrice(subtotal) }}
          </span>
        </div>

        <!-- 12% VAT Breakdown (Strict Philippine Tax Integrity) -->
        <div class="flex items-center justify-between font-azeret text-[11px] text-stone-500">
          <span>Philippine VAT (12% inclusive)</span>
          <span>₱{{ formatPrice(vatAmount) }}</span>
        </div>

        <!-- Checkout Button (Enforcing Rule 9 Button & Control Sizing Standards) -->
        <button
          type="button"
          class="checkout-btn eclore-button w-full min-h-[48px] py-3.5 px-6 my-2 rounded font-azeret text-xs uppercase tracking-widest font-semibold transition-all flex items-center justify-center shadow-md active:scale-[0.99]"
          :class="selectedCount === 0
            ? 'bg-stone-300 text-stone-500 cursor-not-allowed opacity-60'
            : 'bg-[#8C9B8C] hover:bg-[#788878] text-white cursor-pointer'"
          :disabled="selectedCount === 0"
          @click="proceedToCheckout"
        >
          <span v-if="selectedCount === 0">Select Items To Checkout</span>
          <span v-else>Proceed To Checkout ({{ selectedCount }} Items)</span>
        </button>

        <!-- View Full Bag Link Button -->
        <button
          type="button"
          class="w-full min-h-[44px] py-2.5 px-4 rounded border border-[#E0E0D9] bg-white hover:bg-stone-100 text-[#1A1A1A] font-azeret text-xs uppercase tracking-widest font-semibold transition-colors flex items-center justify-center"
          @click="goToCart"
        >
          View Full Bag / Coffret
        </button>

        <!-- Micro-copy -->
        <p class="font-azeret text-[10px] text-stone-400 text-center tracking-wide">
          Complimentary armed transport & insurance calculated at checkout.
        </p>
      </footer>
    </aside>
  </div>
  </Transition>
</template>

<style scoped>
.cart-item-new {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cart-item-new:hover {
  transform: translateY(-1px);
}
</style>
