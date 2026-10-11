<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonContent,
  IonRefresher,
  IonRefresherContent,
  onIonViewWillEnter,
} from '@ionic/vue';
import { useCartStore } from '@/stores/cart.store';
import { useAuthStore } from '@/stores/auth.store';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { getProductImage as resolveImg } from '@/utils/product-image';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const items = computed(() => cartStore.items);
const selectedIds = computed(() => cartStore.selectedIds);
const allSelected = computed(() => cartStore.allSelected);
const selectedCount = computed(() => cartStore.selectedCount);
const totalCount = computed(() => items.value.length);
const subtotal = computed(() => cartStore.subtotal);
const vatAmount = computed(() => cartStore.vatAmount);
const netOfVat = computed(() => cartStore.netOfVat);
const total = computed(() => cartStore.total);
const loading = computed(() => cartStore.loading);

function getProductImage(item: any): string {
  return resolveImg(item.product || item);
}

function handleSelectAll(e: Event) {
  const checked = (e.target as HTMLInputElement).checked;
  cartStore.selectAll(checked);
}

function handleToggleItem(id: number) {
  cartStore.toggleItemSelection(id);
}

function handleQuantityChange(productId: number, newQty: number) {
  if (newQty <= 0) {
    cartStore.removeItem(productId);
  } else {
    cartStore.updateQuantity(productId, newQty);
  }
}

function handleRemoveItem(productId: number) {
  cartStore.removeItem(productId);
}

function handleClearCart() {
  if (confirm('Are you sure you wish to clear all creations from your coffret?')) {
    cartStore.clearCart();
  }
}

function handleProceedToCheckout() {
  if (!isAuthenticated.value) {
    authStore.setIntendedRoute('/checkout');
    router.push('/tabs/account');
    return;
  }
  router.push('/checkout');
}

function navigateToLogin() {
  authStore.setIntendedRoute('/tabs/coffret');
  router.push('/tabs/account');
}

onIonViewWillEnter(async () => {
  await cartStore.fetchCart();
});

async function handleRefresh(event: CustomEvent) {
  try {
    await cartStore.fetchCart();
  } finally {
    (event.target as any)?.complete();
  }
}
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content pulling-text="Pull to refresh bag..." refreshing-spinner="crescent" />
      </ion-refresher>
      <EcloreHeader />

      <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14 pb-28">
        <!-- Title & Breadcrumb -->
        <div class="mb-8 sm:mb-12 text-center sm:text-left border-b border-[#E2DDD5] pb-6">
          <nav class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#767676] mb-3">
            <span>Maison Éclore</span>
            <span class="mx-2">/</span>
            <span class="text-[#1A1A1A]">Le Coffret</span>
          </nav>
          <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <h1 class="font-playfair text-3xl sm:text-5xl text-[#1A1A1A] font-light">
              Your Coffret
            </h1>
            <p v-if="isAuthenticated && items.length > 0" class="font-azeret text-xs uppercase tracking-widest text-[#767676]">
              {{ selectedCount }} of {{ totalCount }} creation{{ totalCount > 1 ? 's' : '' }} curated
            </p>
          </div>
        </div>

        <!-- 1. GUEST UNAUTHENTICATED STATE -->
        <div
          v-if="!isAuthenticated"
          class="bg-[#FDFBF7] border border-[#E2DDD5] p-8 sm:p-14 text-center max-w-2xl mx-auto shadow-sm my-8"
        >
          <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-[#B6965D]/10 flex items-center justify-center text-[#B6965D]">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </div>
          <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mb-3">
            Sign In to Access Your Coffret
          </h2>
          <p class="font-azeret text-xs text-[#767676] leading-relaxed tracking-wide mb-8 max-w-md mx-auto">
            To view, curate, and proceed with acquisitions in your personal jewellery casket, please sign in to your authenticated Maison Éclore account.
          </p>
          <EcloreButton variant="primary" size="lg" @click="navigateToLogin">
            Sign In / Create Account
          </EcloreButton>

          <div class="mt-12 pt-8 border-t border-[#E2DDD5] grid grid-cols-1 sm:grid-cols-3 gap-6 text-left">
            <div>
              <p class="font-azeret text-[10px] text-[#B6965D] tracking-widest uppercase mb-1">Armored Transit</p>
              <p class="text-xs text-[#767676]">Complimentary insured armored courier delivery nationwide.</p>
            </div>
            <div>
              <p class="font-azeret text-[10px] text-[#B6965D] tracking-widest uppercase mb-1">Tax Invoiced</p>
              <p class="text-xs text-[#767676]">Authoritative 12% Philippine BIR VAT official receipts.</p>
            </div>
            <div>
              <p class="font-azeret text-[10px] text-[#B6965D] tracking-widest uppercase mb-1">Haute Joaillerie</p>
              <p class="text-xs text-[#767676]">Certificate of Authenticity with unique serial registry.</p>
            </div>
          </div>
        </div>

        <!-- 2. AUTHENTICATED EMPTY CART STATE -->
        <div
          v-else-if="items.length === 0"
          class="bg-[#FDFBF7] border border-[#E2DDD5] p-10 sm:p-16 text-center max-w-xl mx-auto shadow-sm my-8"
        >
          <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-[#B6965D]/10 flex items-center justify-center text-[#B6965D]">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.67 0-1.19-.578-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119.993z" />
            </svg>
          </div>
          <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mb-2">
            Your Coffret is Empty
          </h2>
          <p class="font-azeret text-xs text-[#767676] tracking-wider mb-8">
            Explore our handcrafted collections and curate exceptional pieces for your casket.
          </p>
          <EcloreButton variant="primary" size="md" @click="router.push('/tabs/catalogue')">
            Discover Atelier Collections
          </EcloreButton>
        </div>

        <!-- 3. AUTHENTICATED ACTIVE CART ITEMS -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          <!-- Left Column: Items List -->
          <div class="lg:col-span-8 space-y-4">
            <!-- Header Toolbar -->
            <div class="bg-white border border-[#E2DDD5] px-4 py-3 flex items-center justify-between font-azeret text-xs shadow-2xs">
              <label class="flex items-center gap-3 cursor-pointer select-none text-[#1A1A1A]">
                <input
                  type="checkbox"
                  class="cart-item-checkbox w-4 h-4 accent-[#B6965D] rounded border-[#E2DDD5]"
                  :checked="allSelected"
                  @change="handleSelectAll"
                />
                <span class="uppercase tracking-wider font-semibold text-[11px] sm:text-xs">Select All Creations</span>
              </label>

              <button
                type="button"
                class="text-stone-500 hover:text-red-700 uppercase tracking-widest text-[10px] sm:text-[11px] transition-colors font-medium"
                @click="handleClearCart"
              >
                Clear Basket
              </button>
            </div>

            <!-- Items -->
            <div
              v-for="item in items"
              :key="item.id"
              class="bg-white border border-[#E2DDD5] p-4 sm:p-5 transition-all hover:border-[#B6965D]/60 shadow-2xs"
            >
              <div class="flex items-start gap-3 sm:gap-4">
                <!-- Checkbox -->
                <div class="pt-1 flex items-center shrink-0">
                  <input
                    type="checkbox"
                    class="cart-item-checkbox w-4 h-4 accent-[#B6965D] rounded border-[#E2DDD5] cursor-pointer"
                    :checked="selectedIds.includes(item.id)"
                    @change="handleToggleItem(item.id)"
                  />
                </div>

                <!-- Thumbnail -->
                <div
                  class="w-20 h-20 sm:w-24 sm:h-24 bg-[#F3EFE7] border border-[#E2DDD5] shrink-0 cursor-pointer overflow-hidden rounded-xs"
                  @click="router.push(`/products/${item.product?.slug || item.product_id}`)"
                >
                  <img
                    :src="getProductImage(item)"
                    :alt="item.product_name"
                    class="w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-500"
                  />
                </div>

                <!-- Info & Actions -->
                <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
                  <div class="flex items-start justify-between gap-2 sm:gap-4">
                    <div class="min-w-0 flex-1">
                      <h3
                        class="font-playfair text-sm sm:text-base font-normal text-[#1A1A1A] hover:text-[#B6965D] cursor-pointer line-clamp-2 leading-snug break-words"
                        @click="router.push(`/products/${item.product?.slug || item.product_id}`)"
                      >
                        {{ item.product_name }}
                      </h3>
                      <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5">
                        <p v-if="item.selected_size || item.options?.size || item.product?.size || item.size || item.ring_size" class="font-azeret text-[10px] text-stone-500 uppercase tracking-wider">
                          Size: {{ item.selected_size || item.options?.size || item.product?.size || item.size || item.ring_size }}
                        </p>
                        <p v-if="item.metal_type" class="font-azeret text-[10px] text-stone-500 uppercase tracking-wider">
                          Metal: {{ item.metal_type }}
                        </p>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="text-stone-400 hover:text-red-700 transition-colors p-1 -mr-1 -mt-1 shrink-0"
                      aria-label="Remove item"
                      @click="handleRemoveItem(item.product_id)"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>

                  <!-- Price & Quantity Row -->
                  <div class="flex items-end justify-between gap-3 mt-3 pt-2.5 border-t border-[#EAE6DF]">
                    <!-- Quantity Stepper -->
                    <div class="inline-flex items-center border border-[#E2DDD5] bg-[#FAF8F5] rounded-xs overflow-hidden">
                      <button
                        type="button"
                        class="w-7 h-7 flex items-center justify-center text-sm text-[#1A1A1A] hover:bg-[#EAE6DF] transition-colors"
                        :disabled="loading"
                        @click="handleQuantityChange(item.product_id, item.quantity - 1)"
                      >
                        -
                      </button>
                      <span class="px-2 font-azeret text-xs font-bold text-[#1A1A1A] min-w-[1.75rem] text-center bg-white h-7 flex items-center justify-center border-x border-[#E2DDD5]">
                        {{ item.quantity }}
                      </span>
                      <button
                        type="button"
                        class="w-7 h-7 flex items-center justify-center text-sm text-[#1A1A1A] hover:bg-[#EAE6DF] transition-colors"
                        :disabled="loading"
                        @click="handleQuantityChange(item.product_id, item.quantity + 1)"
                      >
                        +
                      </button>
                    </div>

                    <!-- Line Total -->
                    <div class="text-right shrink-0">
                      <span class="font-azeret text-sm sm:text-base font-semibold text-[#1A1A1A] tracking-tight">
                        <CurrencyDisplay :amount="item.line_total || item.total_price || (item.unit_price * item.quantity)" />
                      </span>
                      <p v-if="item.quantity > 1" class="font-azeret text-[9px] text-stone-500">
                        <CurrencyDisplay :amount="item.unit_price" /> each
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Acquisition Summary -->
          <div class="lg:col-span-4 sticky top-24">
            <div class="bg-[#FDFBF7] border border-[#E2DDD5] p-6 sm:p-8 shadow-sm space-y-6">
              <h2 class="font-playfair text-2xl text-[#1A1A1A] font-light border-b border-[#E2DDD5] pb-4">
                Acquisition Summary
              </h2>

              <!-- Breakdown -->
              <div class="space-y-3 font-azeret text-xs text-[#767676]">
                <div class="flex justify-between">
                  <span>Selected Subtotal</span>
                  <span class="text-[#1A1A1A] font-medium"><CurrencyDisplay :amount="subtotal" /></span>
                </div>
                <div class="flex justify-between">
                  <span>Net of 12% VAT</span>
                  <span><CurrencyDisplay :amount="netOfVat" /></span>
                </div>
                <div class="flex justify-between">
                  <span>12% Philippine VAT</span>
                  <span><CurrencyDisplay :amount="vatAmount" /></span>
                </div>
                <div class="flex justify-between text-[#B6965D]">
                  <span>Insured Armored Delivery</span>
                  <span>COMPLIMENTARY</span>
                </div>
              </div>

              <!-- Total -->
              <div class="border-t border-[#E2DDD5] pt-4 flex items-baseline justify-between font-azeret">
                <span class="text-xs uppercase tracking-widest text-[#1A1A1A] font-semibold">Total</span>
                <span class="font-playfair text-2xl font-bold text-[#1A1A1A]">
                  <CurrencyDisplay :amount="total" />
                </span>
              </div>

              <!-- Checkout Action -->
              <div>
                <EcloreButton
                  variant="primary"
                  size="lg"
                  block
                  :disabled="selectedCount === 0 || loading"
                  @click="handleProceedToCheckout"
                >
                  {{ selectedCount === 0 ? 'Select Items to Checkout' : 'Proceed to Acquisition' }}
                </EcloreButton>
                <p v-if="selectedCount === 0" class="font-azeret text-[10px] text-center text-[#767676] mt-2 uppercase tracking-wider">
                  Please select at least 1 creation to proceed
                </p>
              </div>

              <!-- Trust & Reassurances -->
              <div class="pt-4 border-t border-[#E2DDD5] space-y-3 font-azeret text-[10px] text-[#767676] uppercase tracking-wider">
                <div class="flex items-center space-x-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D]"></span>
                  <span>Secured by Ferrari Armored Transit</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D]"></span>
                  <span>Official BIR VAT Tax Invoice Included</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D]"></span>
                  <span>30-Day Atelier Return & Restoration Guarantee</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
