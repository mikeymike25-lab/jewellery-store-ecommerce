<script setup lang="ts">
import { ref } from 'vue';
import type { ShippingAddress, CartItem } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';

interface Props {
  shippingData: ShippingAddress;
  paymentData: {
    payment_method: 'cod' | 'xendit' | 'existing';
    payment_method_id?: number | null;
  };
  cartItems: CartItem[];
  subtotal: number;
  shippingCost: number;
  taxAmount: number;
  total: number;
  isSubmitting?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isSubmitting: false,
});

const emit = defineEmits<{
  (e: 'back'): void;
  (e: 'edit-shipping'): void;
  (e: 'edit-payment'): void;
  (e: 'confirm', data: { notes?: string; terms_accepted: boolean }): void;
}>();

const notes = ref('');
const termsAccepted = ref(false);
const errorMessage = ref<string | null>(null);

function formatPrice(amount: number): string {
  return amount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function handleConfirm() {
  errorMessage.value = null;

  if (!termsAccepted.value) {
    errorMessage.value = 'Please review and accept the Atelier Terms & Conditions to confirm your acquisition.';
    return;
  }

  emit('confirm', {
    notes: notes.value.trim() || undefined,
    terms_accepted: true,
  });
}
</script>

<template>
  <div class="bg-white p-6 sm:p-10 border border-[#E2DDD5] rounded-lg shadow-sm">
    <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mb-8 pb-4 border-b border-stone-100">
      Review Acquisition
    </h2>

    <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 font-azeret text-xs uppercase tracking-wider">
      {{ errorMessage }}
    </div>

    <!-- 1. Delivery Mandate Review Card -->
    <div class="mb-10">
      <div class="flex items-center justify-between mb-4 pb-2 border-b border-stone-100">
        <h3 class="font-playfair text-lg text-[#1A1A1A]">Delivery Mandate</h3>
        <button
          type="button"
          class="font-azeret text-[9px] uppercase tracking-[0.2em] text-[#B6965D] hover:text-[#1A1A1A] transition-colors border-b border-transparent hover:border-[#1A1A1A] pb-0.5"
          @click="emit('edit-shipping')"
        >
          Amend Details
        </button>
      </div>

      <div class="p-6 bg-[#FAFAFA] border border-[#E2DDD5] grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <span class="font-azeret text-[9px] tracking-[0.2em] text-stone-400 uppercase block mb-1">Recipient</span>
          <p class="font-sans text-sm text-[#1A1A1A] font-medium mb-3">
            {{ shippingData.first_name }} {{ shippingData.last_name }}
          </p>

          <span class="font-azeret text-[9px] tracking-[0.2em] text-stone-400 uppercase block mb-1">Concierge Reach</span>
          <p class="font-sans text-xs text-stone-600 font-light mb-0">
            {{ shippingData.email }}<br />
            {{ shippingData.phone }}
          </p>
        </div>

        <div>
          <span class="font-azeret text-[9px] tracking-[0.2em] text-stone-400 uppercase block mb-1">Destination</span>
          <p class="font-sans text-xs text-stone-700 leading-relaxed font-light mb-0">
            {{ shippingData.address_line_1 }}<br />
            {{ shippingData.barangay }}, {{ shippingData.city }}<br />
            <span v-if="shippingData.province">{{ shippingData.province }}, </span>
            {{ shippingData.region }} {{ shippingData.zip_code }}
          </p>
          <div class="mt-2 font-azeret text-[9px] text-[#B6965D] uppercase tracking-wider">
            Mode: {{ shippingData.shipping_method_name || 'Armed Courier' }}
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Payment Summary Card -->
    <div class="mb-10">
      <div class="flex items-center justify-between mb-4 pb-2 border-b border-stone-100">
        <h3 class="font-playfair text-lg text-[#1A1A1A]">Payment Summary</h3>
        <button
          type="button"
          class="font-azeret text-[9px] uppercase tracking-[0.2em] text-[#B6965D] hover:text-[#1A1A1A] transition-colors border-b border-transparent hover:border-[#1A1A1A] pb-0.5"
          @click="emit('edit-payment')"
        >
          Amend Method
        </button>
      </div>

      <div class="p-6 bg-[#FAFAFA] border border-[#E2DDD5]">
        <div v-if="paymentData.payment_method === 'cod'" class="flex items-center justify-between">
          <div>
            <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold block mb-1">
              Cash on Delivery (COD)
            </span>
            <p class="font-sans text-xs text-stone-500 font-light mb-0">
              Treasury settlement required upon receipt of sealed parcel.
            </p>
          </div>
          <span class="font-azeret text-[8px] uppercase tracking-wider px-2 py-0.5 bg-stone-200 text-stone-700">
            ON DELIVERY
          </span>
        </div>

        <div v-else-if="paymentData.payment_method === 'xendit'" class="flex items-center justify-between">
          <div>
            <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold block mb-1">
              Digital Treasury Gateway (Xendit)
            </span>
            <p class="font-sans text-xs text-stone-500 font-light mb-0">
              Encrypted transfer via Visa, Mastercard, GCash, or Maya hosted invoice.
            </p>
          </div>
          <span class="font-azeret text-[8px] uppercase tracking-[0.2em] text-[#B6965D] border border-stone-300 px-2 py-0.5 bg-white">
            FORTIFIED
          </span>
        </div>

        <div v-else class="flex items-center justify-between">
          <div>
            <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold block mb-1">
              Saved Payment Method
            </span>
            <p class="font-sans text-xs text-stone-500 font-light mb-0">
              Authorized atelier token payment.
            </p>
          </div>
          <span class="font-azeret text-[8px] uppercase tracking-wider px-2 py-0.5 bg-[#1A1A1A] text-white">
            STORED
          </span>
        </div>
      </div>
    </div>

    <!-- 3. Your Selection / Line Items Breakdown -->
    <div class="mb-10">
      <h3 class="font-playfair text-lg text-[#1A1A1A] mb-4 pb-2 border-b border-stone-100">
        Your Selection
      </h3>

      <div class="divide-y divide-stone-100">
        <div
          v-for="item in cartItems"
          :key="item.id"
          class="py-4 flex items-center justify-between gap-4"
        >
          <div class="flex items-center gap-4">
            <div class="w-14 h-16 bg-[#F3EFE7] border border-[#E2DDD5] flex items-center justify-center overflow-hidden flex-shrink-0">
              <img
                v-if="item.product?.primary_image || item.product_data?.image"
                :src="String(item.product?.primary_image || item.product_data?.image)"
                :alt="item.product_name"
                class="w-full h-full object-cover"
              />
              <span v-else class="font-azeret text-[9px] text-stone-400">ÉCLORE</span>
            </div>

            <div>
              <h4 class="font-playfair text-sm text-[#1A1A1A] font-medium mb-1">
                {{ item.product_name || item.product?.name }}
              </h4>
              <div class="font-azeret text-[10px] text-stone-500 space-x-2">
                <span v-if="item.product_sku || item.product?.sku">
                  SKU: {{ item.product_sku || item.product?.sku }}
                </span>
                <span>Qty: {{ item.quantity }}</span>
                <span v-if="item.ring_size || item.product_data?.size">
                  Size: {{ item.ring_size || item.product_data?.size }}
                </span>
              </div>
            </div>
          </div>

          <div class="text-right">
            <div class="font-azeret text-sm font-semibold text-[#1A1A1A]">
              ₱{{ formatPrice((item.unit_price || 0) * item.quantity) }}
            </div>
            <div v-if="item.quantity > 1" class="font-azeret text-[10px] text-stone-400">
              ₱{{ formatPrice(item.unit_price) }} each
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Concierge Order Notes -->
    <div class="mb-10">
      <label for="order_notes" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 mb-2 block">
        Concierge Instructions / Gift Message (Optional)
      </label>
      <textarea
        id="order_notes"
        v-model="notes"
        rows="3"
        class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm p-4 focus:border-[#1A1A1A] focus:outline-none resize-none"
        placeholder="Enter any bespoke packaging requests or concierge instructions..."
      ></textarea>
    </div>

    <!-- 5. Terms & Conditions Agreement -->
    <div class="mb-10 pt-6 border-t border-stone-100">
      <label class="flex items-start cursor-pointer group">
        <input
          v-model="termsAccepted"
          type="checkbox"
          class="mt-1 w-4 h-4 accent-[#1A1A1A] rounded-none cursor-pointer"
          required
        />
        <span class="ml-3 font-sans text-xs text-stone-700 leading-relaxed font-light">
          I confirm that I have reviewed my acquisition details and agree to the
          <a href="#" class="underline font-medium text-[#1A1A1A] hover:text-[#B6965D]">Terms of Service</a>
          and
          <a href="#" class="underline font-medium text-[#1A1A1A] hover:text-[#B6965D]">Privacy Policy</a>.
        </span>
      </label>
    </div>

    <!-- Navigation Buttons with Rule 9 Standards -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-stone-100">
      <button
        type="button"
        class="w-full sm:w-auto font-azeret text-[10px] uppercase tracking-[0.25em] text-stone-400 hover:text-[#1A1A1A] py-3.5 px-4 transition-colors text-center order-2 sm:order-1"
        @click="emit('back')"
      >
        ← Back to Payment
      </button>

      <EcloreButton
        variant="primary"
        size="lg"
        class="w-full sm:w-auto order-1 sm:order-2"
        :loading="isSubmitting"
        :disabled="!termsAccepted || isSubmitting"
        @click="handleConfirm"
      >
        Confirm Acquisition
      </EcloreButton>
    </div>
  </div>
</template>
