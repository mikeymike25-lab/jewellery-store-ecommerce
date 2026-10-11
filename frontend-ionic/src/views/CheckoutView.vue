<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { useCartStore } from '@/stores/cart.store';
import { useOrdersStore } from '@/stores/orders.store';
import type { ShippingMethod, ShippingAddress, SavedPaymentMethod, CartItem } from '@/types';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import ShippingStep from '@/components/checkout/ShippingStep.vue';
import PaymentStep from '@/components/checkout/PaymentStep.vue';
import ReviewStep from '@/components/checkout/ReviewStep.vue';

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();
const ordersStore = useOrdersStore();

const currentStep = ref<1 | 2 | 3>(1);
const loading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

// Checkout state from backend /api/v1/checkout/init
const userAddressInfo = ref<any>({
  id: 0,
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  street: '',
  barangay: '',
  city: '',
  province: '',
  region: '',
  zip_code: '',
  is_default_address_complete: false,
});

const availableShippingMethods = ref<ShippingMethod[]>([]);
const defaultShippingMethod = ref<ShippingMethod | null>(null);
const savedPaymentMethods = ref<SavedPaymentMethod[]>([]);
const codEligible = ref(true);

// Current checkout flow payloads
const shippingData = ref<ShippingAddress | null>(null);
const paymentData = ref<{
  payment_method: 'cod' | 'xendit' | 'existing';
  payment_method_id?: number | null;
}>({
  payment_method: 'xendit',
  payment_method_id: null,
});

const selectedItems = computed<CartItem[]>(() => {
  return cartStore.selectedItems.length > 0 ? cartStore.selectedItems : cartStore.items;
});

const subtotal = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + (Number(item.unit_price) * Number(item.quantity)), 0);
});

const shippingCost = computed(() => {
  if (shippingData.value?.shipping_cost !== undefined) {
    return shippingData.value.shipping_cost;
  }
  return defaultShippingMethod.value?.cost || 0;
});

// 12% Philippine VAT (standard VAT calculation)
const vatAmount = computed(() => {
  return Math.round(subtotal.value * 0.12 * 100) / 100;
});

const total = computed(() => {
  return subtotal.value + shippingCost.value + vatAmount.value;
});

function formatPrice(amount: number): string {
  return amount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function loadCheckoutInit() {
  loading.value = true;
  errorMessage.value = null;

  try {
    const res = await apiClient.get('/v1/checkout/init', {
      params: {
        selected_ids: cartStore.selectedIds,
      },
    });

    if (res.data.success) {
      userAddressInfo.value = res.data.user;
      availableShippingMethods.value = res.data.available_shipping_methods || [];
      defaultShippingMethod.value = res.data.default_shipping_method || null;
      savedPaymentMethods.value = res.data.payment_methods || [];
      codEligible.value = res.data.cod_eligible;

      // Initialize default shipping data pre-filled from user profile
      if (res.data.user) {
        shippingData.value = {
          address_option: res.data.user.is_default_address_complete ? 'default' : 'custom',
          first_name: res.data.user.first_name || '',
          last_name: res.data.user.last_name || '',
          email: res.data.user.email || '',
          phone: res.data.user.phone || '',
          address_line_1: res.data.user.street || '',
          barangay: res.data.user.barangay || '',
          city: res.data.user.city || '',
          province: res.data.user.province || '',
          region: res.data.user.region || '',
          zip_code: res.data.user.zip_code || '',
          shipping_method_id: res.data.default_shipping_method?.id,
          shipping_method_name: res.data.default_shipping_method?.name,
          shipping_cost: res.data.default_shipping_cost || 0,
        };
      }
    }
  } catch (err: any) {
    if (err.response?.status === 401) {
      router.push('/tabs/account');
    } else {
      errorMessage.value = err.response?.data?.message || 'Unable to initialize checkout. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}

// Flow Transitions
function handleShippingProceed(data: ShippingAddress) {
  shippingData.value = data;
  currentStep.value = 2;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function handlePaymentProceed(data: { payment_method: 'cod' | 'xendit' | 'existing'; payment_method_id?: number | null }) {
  paymentData.value = data;
  currentStep.value = 3;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Step 3 Order Execution
async function handleAcquisitionConfirm(payload: { notes?: string; terms_accepted: boolean }) {
  if (!shippingData.value) {
    currentStep.value = 1;
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = null;

  try {
    const postPayload = {
      address_option: shippingData.value.address_option,
      first_name: shippingData.value.first_name,
      last_name: shippingData.value.last_name,
      phone: shippingData.value.phone,
      address_line_1: shippingData.value.address_line_1,
      barangay: shippingData.value.barangay,
      city: shippingData.value.city,
      province: shippingData.value.province || null,
      region: shippingData.value.region,
      zip_code: shippingData.value.zip_code,
      save_as_default: shippingData.value.save_as_default || false,
      shipping_method_id: shippingData.value.shipping_method_id,
      payment_method: paymentData.value.payment_method,
      payment_method_id: paymentData.value.payment_method_id || null,
      notes: payload.notes || null,
      terms_accepted: true,
      selected_ids: cartStore.selectedIds,
    };

    const res = await apiClient.post('/v1/checkout/process', postPayload);

    if (res.data.success) {
      const orderNumber = res.data.order_number;
      const invoiceUrl = res.data.invoice_url;

      // Update local cart and orders stores
      await Promise.allSettled([
        cartStore.fetchCart(),
        ordersStore.fetchOrders(1),
      ]);

      // Navigate to Step 4: Order Confirmation
      const isExternalInvoice = invoiceUrl && 
                                !invoiceUrl.includes('/checkout/confirmation') && 
                                !invoiceUrl.startsWith('simulated:');
      if (isExternalInvoice) {
        router.push({
          path: `/checkout/confirmation/${orderNumber}`,
          query: { invoice_url: invoiceUrl },
        });
      } else {
        router.push(`/checkout/confirmation/${orderNumber}`);
      }
    } else {
      errorMessage.value = res.data.message || 'There was an issue creating your acquisition.';
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Transaction could not be completed. Please check your details.';
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    authStore.setIntendedRoute('/checkout');
    router.push('/tabs/account');
    return;
  }
  await cartStore.fetchCart();
  await loadCheckoutInit();
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 pb-28 w-full">
          <!-- Back Navigation Button -->
          <div class="mb-6">
            <button
              v-if="currentStep === 1"
              type="button"
              class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-600 hover:text-[#B6965D] transition-colors py-1.5 group"
              @click="router.push('/tabs/coffret')"
            >
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to Shopping Bag</span>
            </button>
            <button
              v-else-if="currentStep === 2"
              type="button"
              class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-600 hover:text-[#B6965D] transition-colors py-1.5 group"
              @click="currentStep = 1"
            >
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to Shipping Details</span>
            </button>
            <button
              v-else-if="currentStep === 3"
              type="button"
              class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-600 hover:text-[#B6965D] transition-colors py-1.5 group"
              @click="currentStep = 2"
            >
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to Payment Selection</span>
            </button>
          </div>

          <!-- Step Progress Indicator -->
          <div class="mb-10 text-center">
            <span class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D]">
              Atelier Acquisition Flow
            </span>
            <h1 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mt-2 mb-6">
              Checkout & Acquisition
            </h1>

            <div class="max-w-xl mx-auto flex items-center justify-between px-4">
              <!-- Step 1: Shipping -->
              <div class="flex flex-col items-center flex-shrink-0 cursor-pointer" @click="currentStep = 1">
                <div
                  class="w-3 h-3 rounded-full mb-2 transition-all"
                  :class="currentStep >= 1 ? 'bg-[#1A1A1A] ring-4 ring-stone-300' : 'bg-stone-300'"
                ></div>
                <span
                  class="font-azeret text-[9px] uppercase tracking-[0.2em]"
                  :class="currentStep >= 1 ? 'text-[#1A1A1A] font-bold' : 'text-stone-400'"
                >
                  1. Shipping
                </span>
              </div>

              <div class="h-0.5 flex-1 mx-2 transition-colors" :class="currentStep >= 2 ? 'bg-[#1A1A1A]' : 'bg-stone-300'"></div>

              <!-- Step 2: Payment -->
              <div
                class="flex flex-col items-center flex-shrink-0 cursor-pointer"
                @click="shippingData ? currentStep = 2 : null"
              >
                <div
                  class="w-3 h-3 rounded-full mb-2 transition-all"
                  :class="currentStep >= 2 ? 'bg-[#1A1A1A] ring-4 ring-stone-300' : 'bg-stone-300'"
                ></div>
                <span
                  class="font-azeret text-[9px] uppercase tracking-[0.2em]"
                  :class="currentStep >= 2 ? 'text-[#1A1A1A] font-bold' : 'text-stone-400'"
                >
                  2. Payment
                </span>
              </div>

              <div class="h-0.5 flex-1 mx-2 transition-colors" :class="currentStep >= 3 ? 'bg-[#1A1A1A]' : 'bg-stone-300'"></div>

              <!-- Step 3: Review -->
              <div
                class="flex flex-col items-center flex-shrink-0 cursor-pointer"
                @click="shippingData && paymentData ? currentStep = 3 : null"
              >
                <div
                  class="w-3 h-3 rounded-full mb-2 transition-all"
                  :class="currentStep >= 3 ? 'bg-[#1A1A1A] ring-4 ring-stone-300' : 'bg-stone-300'"
                ></div>
                <span
                  class="font-azeret text-[9px] uppercase tracking-[0.2em]"
                  :class="currentStep >= 3 ? 'text-[#1A1A1A] font-bold' : 'text-stone-400'"
                >
                  3. Review
                </span>
              </div>

              <div class="h-0.5 flex-1 mx-2 bg-stone-300"></div>

              <!-- Step 4: Confirmation -->
              <div class="flex flex-col items-center flex-shrink-0">
                <div class="w-3 h-3 rounded-full mb-2 bg-stone-300"></div>
                <span class="font-azeret text-[9px] uppercase tracking-[0.2em] text-stone-400">
                  4. Confirm
                </span>
              </div>
            </div>
          </div>

          <!-- Empty Cart Notice -->
          <div
            v-if="selectedItems.length === 0 && !loading"
            class="bg-white border border-[#E2DDD5] p-12 text-center rounded-lg shadow-sm max-w-lg mx-auto"
          >
            <h3 class="font-playfair text-xl text-[#1A1A1A] mb-3">Your Shopping Bag is Empty</h3>
            <p class="font-sans text-sm text-stone-500 mb-8 font-light">
              Explore our fine jewellery collections to select timeless pieces for acquisition.
            </p>
            <EcloreButton variant="primary" size="lg" @click="router.push('/catalogue')">
              Return to Collections
            </EcloreButton>
          </div>

          <!-- Active Multi-Step Layout (2 Columns) -->
          <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Column: Active Step -->
            <div class="lg:col-span-7">
              <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 font-azeret text-xs uppercase tracking-wider">
                {{ errorMessage }}
              </div>

              <!-- Step 1: Shipping -->
              <ShippingStep
                v-if="currentStep === 1"
                :user="userAddressInfo"
                :available-shipping-methods="availableShippingMethods"
                :default-shipping-method="defaultShippingMethod"
                :initial-shipping-data="shippingData"
                @proceed="handleShippingProceed"
              />

              <!-- Step 2: Payment -->
              <PaymentStep
                v-else-if="currentStep === 2"
                :total="total"
                :cod-eligible="codEligible"
                :saved-payment-methods="savedPaymentMethods"
                :initial-payment-method="paymentData.payment_method"
                :initial-payment-method-id="paymentData.payment_method_id"
                @back="currentStep = 1"
                @proceed="handlePaymentProceed"
              />

              <!-- Step 3: Review -->
              <ReviewStep
                v-else-if="currentStep === 3"
                :shipping-data="shippingData!"
                :payment-data="paymentData"
                :cart-items="selectedItems"
                :subtotal="subtotal"
                :shipping-cost="shippingCost"
                :tax-amount="vatAmount"
                :total="total"
                :is-submitting="isSubmitting"
                @back="currentStep = 2"
                @edit-shipping="currentStep = 1"
                @edit-payment="currentStep = 2"
                @confirm="handleAcquisitionConfirm"
              />
            </div>

            <!-- Right Column: Sticky Order Summary Card -->
            <div class="lg:col-span-5 sticky top-28">
              <div class="bg-white border border-[#E2DDD5] rounded-lg p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-6">
                  <h3 class="font-playfair text-xl text-[#1A1A1A]">Order Selection</h3>
                  <button
                    type="button"
                    class="font-azeret text-[9px] uppercase tracking-[0.2em] text-[#B6965D] hover:text-[#1A1A1A] transition-colors"
                    @click="cartStore.openCart()"
                  >
                    Modify Bag
                  </button>
                </div>

                <!-- Selected Pieces List -->
                <div class="divide-y divide-stone-100 max-h-64 overflow-y-auto mb-6 pr-2">
                  <div
                    v-for="item in selectedItems"
                    :key="item.id"
                    class="py-3 flex items-center justify-between gap-3 text-xs"
                  >
                    <div>
                      <h4 class="font-playfair text-xs font-semibold text-[#1A1A1A] line-clamp-1">
                        {{ item.product_name || item.product?.name }}
                      </h4>
                      <p class="font-azeret text-[10px] text-stone-500">
                        Qty: {{ item.quantity }}
                        <span v-if="item.ring_size || item.product_data?.size" class="ml-1">
                          &bull; Size: {{ item.ring_size || item.product_data?.size }}
                        </span>
                      </p>
                    </div>

                    <span class="font-azeret text-xs font-bold text-[#1A1A1A] flex-shrink-0">
                      ₱{{ formatPrice((item.unit_price || 0) * item.quantity) }}
                    </span>
                  </div>
                </div>

                <!-- Financial Calculation Breakdown -->
                <div class="space-y-3 pt-4 border-t border-stone-100 font-azeret text-xs text-stone-600">
                  <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-stone-900">₱{{ formatPrice(subtotal) }}</span>
                  </div>

                  <div class="flex justify-between text-stone-500 text-[11px]">
                    <span>Philippine VAT (12%)</span>
                    <span>₱{{ formatPrice(vatAmount) }}</span>
                  </div>

                  <div class="flex justify-between text-stone-500 text-[11px]">
                    <span>Estimated Shipping</span>
                    <span v-if="shippingCost === 0" class="text-[#B6965D] font-medium uppercase">Complimentary</span>
                    <span v-else class="text-stone-900">₱{{ formatPrice(shippingCost) }}</span>
                  </div>

                  <div class="flex items-center gap-2 p-2 bg-[#F9F8F6] border border-stone-100 font-azeret text-[9px] text-[#B6965D] uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Complimentary Armored Courier Insurance Included</span>
                  </div>
                </div>

                <!-- Authoritative Grand Total -->
                <div class="pt-6 mt-4 border-t border-stone-200 flex justify-between items-baseline">
                  <div>
                    <span class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 block">Total Due</span>
                    <span class="font-playfair text-xl text-[#1A1A1A] font-bold">Acquisition Value</span>
                  </div>
                  <div class="text-right">
                    <span class="font-azeret text-2xl font-bold text-[#1A1A1A]">₱{{ formatPrice(total) }}</span>
                    <span class="font-azeret text-[9px] text-stone-400 block uppercase">PHP (Taxes Included)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>

        <EcloreFooter />
      </div>
    </ion-content>
  </ion-page>
</template>
