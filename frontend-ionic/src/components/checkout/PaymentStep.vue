<script setup lang="ts">
import { ref } from 'vue';
import type { SavedPaymentMethod } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';

interface Props {
  total: number;
  codEligible: boolean;
  savedPaymentMethods?: SavedPaymentMethod[];
  initialPaymentMethod?: string;
  initialPaymentMethodId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
  savedPaymentMethods: () => [],
  initialPaymentMethod: 'xendit',
  initialPaymentMethodId: null,
});

const emit = defineEmits<{
  (e: 'back'): void;
  (e: 'proceed', data: { payment_method: 'cod' | 'xendit' | 'existing'; payment_method_id?: number | null }): void;
}>();

const selectedMethod = ref<'cod' | 'xendit' | 'existing'>(
  (props.initialPaymentMethod as any) || (props.codEligible ? 'cod' : 'xendit')
);
const selectedPaymentMethodId = ref<number | null>(props.initialPaymentMethodId);
const errorMessage = ref<string | null>(null);

function handleContinue() {
  errorMessage.value = null;

  if (selectedMethod.value === 'cod' && !props.codEligible) {
    errorMessage.value = 'Cash on Delivery is limited to orders ₱3,000 and below. Please choose the Digital Treasury Gateway.';
    return;
  }

  if (selectedMethod.value === 'existing' && !selectedPaymentMethodId.value) {
    errorMessage.value = 'Please select a saved payment method to proceed.';
    return;
  }

  emit('proceed', {
    payment_method: selectedMethod.value,
    payment_method_id: selectedMethod.value === 'existing' ? selectedPaymentMethodId.value : null,
  });
}
</script>

<template>
  <div class="bg-white p-6 sm:p-10 border border-[#E2DDD5] rounded-lg shadow-sm">
    <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mb-8 pb-4 border-b border-stone-100">
      Payment Method
    </h2>

    <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 font-azeret text-xs uppercase tracking-wider">
      {{ errorMessage }}
    </div>

    <!-- Payment Method Options -->
    <div class="space-y-6 mb-10">
      <!-- 1. Cash on Delivery (COD) -->
      <div
        class="border transition-all rounded-none p-6"
        :class="[
          selectedMethod === 'cod' ? 'border-[#1A1A1A] bg-[#FAFAFA]' : 'border-[#E2DDD5] hover:border-stone-400',
          !codEligible ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'
        ]"
      >
        <label class="flex items-start" :class="!codEligible ? 'cursor-not-allowed' : 'cursor-pointer'">
          <input
            type="radio"
            name="payment_method_selection"
            value="cod"
            class="mt-1 mr-4 w-4 h-4 accent-[#1A1A1A]"
            :checked="selectedMethod === 'cod'"
            :disabled="!codEligible"
            @change="selectedMethod = 'cod'"
          />
          <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
              <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold">
                Cash on Delivery (COD)
              </span>
              <span
                v-if="!codEligible"
                class="font-azeret text-[8px] uppercase tracking-wider px-2 py-0.5 bg-red-100 text-red-700 border border-red-200"
              >
                Max ₱3,000 Limit
              </span>
            </div>
            <p class="font-sans text-xs text-stone-500 font-light mb-0 leading-relaxed">
              Complete your exquisite acquisition with treasury settlement upon armored courier receipt.
              <span v-if="!codEligible" class="block mt-1 text-red-600 font-azeret text-[10px]">
                Disabled: Order exceeds the maximum ₱3,000 limit for Cash on Delivery.
              </span>
            </p>
          </div>
        </label>
      </div>

      <!-- 2. Digital Treasury Gateway (Xendit) -->
      <div
        class="border transition-all rounded-none p-6 cursor-pointer"
        :class="selectedMethod === 'xendit' ? 'border-[#1A1A1A] bg-[#FAFAFA]' : 'border-[#E2DDD5] hover:border-stone-400'"
      >
        <label class="flex items-start cursor-pointer">
          <input
            type="radio"
            name="payment_method_selection"
            value="xendit"
            class="mt-1 mr-4 w-4 h-4 accent-[#1A1A1A]"
            :checked="selectedMethod === 'xendit'"
            @change="selectedMethod = 'xendit'"
          />
          <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
              <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold">
                Digital Treasury Gateway (Xendit)
              </span>
              <span class="font-azeret text-[8px] uppercase tracking-[0.2em] text-[#B6965D] border border-stone-200 px-2 py-0.5 bg-white">
                SECURE PORTAL
              </span>
            </div>
            <p class="font-sans text-xs text-stone-500 font-light mb-4 leading-relaxed">
              Process your acquisition securely via our encrypted portal. Certified by the Central Bank of the Philippines.
            </p>

            <div class="flex items-center gap-3 flex-wrap pt-2 border-t border-stone-100 font-azeret text-[10px] tracking-wider text-stone-700">
              <span class="text-stone-400 text-[9px] uppercase">PARTNERS &bull;</span>
              <span class="px-2 py-1 bg-white border border-[#E2DDD5] text-[#1A1A1A] font-semibold">VISA</span>
              <span class="px-2 py-1 bg-white border border-[#E2DDD5] text-[#1A1A1A] font-semibold">MASTERCARD</span>
              <span class="px-2 py-1 bg-white border border-[#E2DDD5] text-blue-600 font-semibold">GCASH</span>
              <span class="px-2 py-1 bg-white border border-[#E2DDD5] text-emerald-600 font-semibold">MAYA</span>
            </div>
          </div>
        </label>
      </div>

      <!-- 3. Saved Payment Methods (if available) -->
      <div v-if="savedPaymentMethods && savedPaymentMethods.length > 0" class="space-y-4 pt-4">
        <label class="font-azeret text-[10px] uppercase tracking-[0.2em] text-[#B6965D] block">
          Saved Payment Methods
        </label>

        <div
          v-for="pm in savedPaymentMethods"
          :key="pm.id"
          class="border transition-all rounded-none p-5 cursor-pointer"
          :class="selectedMethod === 'existing' && selectedPaymentMethodId === pm.id ? 'border-[#1A1A1A] bg-[#FAFAFA]' : 'border-[#E2DDD5] hover:border-stone-400'"
        >
          <label class="flex items-center cursor-pointer">
            <input
              type="radio"
              name="payment_method_selection"
              value="existing"
              class="mr-4 w-4 h-4 accent-[#1A1A1A]"
              :checked="selectedMethod === 'existing' && selectedPaymentMethodId === pm.id"
              @change="selectedMethod = 'existing'; selectedPaymentMethodId = pm.id"
            />
            <div class="flex-1 flex items-center justify-between">
              <div>
                <span class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-wider font-semibold">
                  {{ pm.card_holder_name || pm.gcash_name || 'Stored Method' }}
                </span>
                <span class="font-azeret text-xs text-stone-500 ml-2">
                  {{ pm.card_last_four ? `•••• ${pm.card_last_four}` : pm.gcash_number }}
                </span>
              </div>
              <span v-if="pm.is_default" class="font-azeret text-[8px] bg-[#1A1A1A] text-white px-2 py-0.5 uppercase tracking-wider">
                PRIMARY
              </span>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Security & Encryption Guarantee Notice -->
    <div class="mb-10 p-6 bg-[#FAFAFA] border border-stone-200 flex items-start gap-4">
      <svg class="w-5 h-5 text-[#B6965D] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      <div>
        <h4 class="font-azeret text-[10px] uppercase tracking-[0.2em] text-[#1A1A1A] font-semibold mb-1">
          256-Bit Encrypted Treasury Environment
        </h4>
        <p class="font-sans text-xs text-stone-500 font-light mb-0 leading-relaxed">
          Your credentials and banking tokens remain encrypted end-to-end. Éclore fine jewellery preserves absolute privacy and never archives personal payment card details.
        </p>
      </div>
    </div>

    <!-- Navigation Buttons with Rule 9 Standards -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-stone-100">
      <button
        type="button"
        class="w-full sm:w-auto font-azeret text-[10px] uppercase tracking-[0.25em] text-stone-400 hover:text-[#1A1A1A] py-3.5 px-4 transition-colors text-center order-2 sm:order-1"
        @click="emit('back')"
      >
        ← Back to Shipping
      </button>

      <EcloreButton
        variant="primary"
        size="lg"
        class="w-full sm:w-auto order-1 sm:order-2"
        @click="handleContinue"
      >
        Continue to Review
      </EcloreButton>
    </div>
  </div>
</template>
