<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import type { ShippingMethod, ShippingAddress } from '@/types';
import PsgcAddressPicker from './PsgcAddressPicker.vue';
import EcloreButton from '@/components/EcloreButton.vue';

interface UserAddressInfo {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string | null;
  street?: string | null;
  barangay?: string | null;
  city?: string | null;
  province?: string | null;
  region?: string | null;
  zip_code?: string | null;
  is_default_address_complete: boolean;
}

interface Props {
  user: UserAddressInfo;
  availableShippingMethods: ShippingMethod[];
  defaultShippingMethod?: ShippingMethod | null;
  initialShippingData?: ShippingAddress | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'proceed', data: ShippingAddress): void;
}>();

const router = useRouter();

const addressOption = ref<'default' | 'custom'>(
  props.initialShippingData?.address_option || (props.user.is_default_address_complete ? 'default' : 'custom')
);

// Orderer & Recipient Info - pre-filled from user profile data, fully editable
const ordererInfo = ref({
  first_name: props.initialShippingData?.first_name || props.user.first_name || '',
  last_name: props.initialShippingData?.last_name || props.user.last_name || '',
  email: props.initialShippingData?.email || props.user.email || '',
  phone: props.initialShippingData?.phone || props.user.phone || '',
});

// Alternative Delivery Address - pre-filled from user profile data for easy editing
const customAddress = ref({
  address_line_1: props.initialShippingData?.address_line_1 || props.user.street || '',
  barangay: props.initialShippingData?.barangay || props.user.barangay || '',
  city: props.initialShippingData?.city || props.user.city || '',
  province: props.initialShippingData?.province || props.user.province || '',
  region: props.initialShippingData?.region || props.user.region || '',
  zip_code: props.initialShippingData?.zip_code || props.user.zip_code || '',
  save_as_default: false,
});

const selectedShippingMethodId = ref<number | null>(
  props.initialShippingData?.shipping_method_id || props.defaultShippingMethod?.id || (props.availableShippingMethods[0]?.id ?? null)
);

const errorMessage = ref<string | null>(null);

const selectedMethod = computed(() => {
  return props.availableShippingMethods.find((m) => m.id === selectedShippingMethodId.value) || props.defaultShippingMethod;
});

// Watch for user data if it hydrates after mount
watch(
  () => props.user,
  (newUser) => {
    if (newUser && !props.initialShippingData) {
      if (!ordererInfo.value.first_name) ordererInfo.value.first_name = newUser.first_name || '';
      if (!ordererInfo.value.last_name) ordererInfo.value.last_name = newUser.last_name || '';
      if (!ordererInfo.value.email) ordererInfo.value.email = newUser.email || '';
      if (!ordererInfo.value.phone) ordererInfo.value.phone = newUser.phone || '';

      if (!customAddress.value.address_line_1) customAddress.value.address_line_1 = newUser.street || '';
      if (!customAddress.value.barangay) customAddress.value.barangay = newUser.barangay || '';
      if (!customAddress.value.city) customAddress.value.city = newUser.city || '';
      if (!customAddress.value.province) customAddress.value.province = newUser.province || '';
      if (!customAddress.value.region) customAddress.value.region = newUser.region || '';
      if (!customAddress.value.zip_code) customAddress.value.zip_code = newUser.zip_code || '';
    }
  },
  { deep: true }
);

function formatPrice(amount: number): string {
  return amount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function handleContinue() {
  errorMessage.value = null;

  // Validate orderer / recipient name
  if (!ordererInfo.value.first_name.trim()) {
    errorMessage.value = 'Please enter the recipient / orderer first name.';
    return;
  }
  if (!ordererInfo.value.last_name.trim()) {
    errorMessage.value = 'Please enter the recipient / orderer last name.';
    return;
  }
  if (!ordererInfo.value.phone.trim()) {
    errorMessage.value = 'Please enter a contact number.';
    return;
  }

  if (addressOption.value === 'default') {
    if (!props.user.is_default_address_complete) {
      errorMessage.value = 'Your primary atelier profile address is incomplete. Please select Alternative Delivery Address.';
      return;
    }

    const payload: ShippingAddress = {
      address_option: 'default',
      first_name: ordererInfo.value.first_name.trim(),
      last_name: ordererInfo.value.last_name.trim(),
      email: ordererInfo.value.email.trim() || props.user.email,
      phone: ordererInfo.value.phone.trim() || props.user.phone || '',
      address_line_1: props.user.street || '',
      barangay: props.user.barangay || '',
      city: props.user.city || '',
      province: props.user.province,
      region: props.user.region || '',
      zip_code: props.user.zip_code || '',
      shipping_method_id: selectedShippingMethodId.value || undefined,
      shipping_method_name: selectedMethod.value?.name || 'Standard Courier',
      shipping_cost: selectedMethod.value?.cost || 0,
    };

    emit('proceed', payload);
  } else {
    // Validate custom destination
    if (!customAddress.value.address_line_1.trim()) {
      errorMessage.value = 'Please enter your street address.';
      return;
    }
    if (!customAddress.value.region.trim()) {
      errorMessage.value = 'Please select a Region.';
      return;
    }
    if (!customAddress.value.city.trim()) {
      errorMessage.value = 'Please select a City / Municipality.';
      return;
    }
    if (!customAddress.value.barangay.trim()) {
      errorMessage.value = 'Please select a Barangay.';
      return;
    }
    if (!customAddress.value.zip_code.trim()) {
      errorMessage.value = 'Please enter your ZIP Code.';
      return;
    }

    const payload: ShippingAddress = {
      address_option: 'custom',
      first_name: ordererInfo.value.first_name.trim(),
      last_name: ordererInfo.value.last_name.trim(),
      email: ordererInfo.value.email.trim() || props.user.email,
      phone: ordererInfo.value.phone.trim() || props.user.phone || '',
      address_line_1: customAddress.value.address_line_1.trim(),
      barangay: customAddress.value.barangay.trim(),
      city: customAddress.value.city.trim(),
      province: customAddress.value.province ? customAddress.value.province.trim() : null,
      region: customAddress.value.region.trim(),
      zip_code: customAddress.value.zip_code.trim(),
      save_as_default: customAddress.value.save_as_default,
      shipping_method_id: selectedShippingMethodId.value || undefined,
      shipping_method_name: selectedMethod.value?.name || 'Standard Courier',
      shipping_cost: selectedMethod.value?.cost || 0,
    };

    emit('proceed', payload);
  }
}
</script>

<template>
  <div class="bg-white p-6 sm:p-10 border border-[#E2DDD5] rounded-lg shadow-sm">
    <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mb-8 pb-4 border-b border-stone-100">
      Shipping Details
    </h2>

    <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 font-azeret text-xs uppercase tracking-wider">
      {{ errorMessage }}
    </div>

    <!-- 1. Orderer & Recipient Information (Pre-filled and fully editable) -->
    <div class="mb-10">
      <div class="flex items-center justify-between mb-4">
        <label class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D] block font-semibold">
          Orderer & Recipient Information
        </label>
        <span class="font-azeret text-[9px] uppercase tracking-wider text-stone-400">
          Pre-filled • Editable
        </span>
      </div>

      <div class="p-6 bg-[#FAFAFA] border border-[#E2DDD5] rounded-none space-y-6">
        <p class="font-sans text-xs text-stone-500 font-light mb-0">
          Your profile details have been automatically pre-filled below. You can freely edit the recipient name and contact details if you are ordering on behalf of someone else or sending a gift.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="orderer_first_name" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
              Recipient First Name *
            </label>
            <input
              id="orderer_first_name"
              v-model="ordererInfo.first_name"
              type="text"
              class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
              placeholder="First name"
              required
            />
          </div>
          <div>
            <label for="orderer_last_name" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
              Recipient Last Name *
            </label>
            <input
              id="orderer_last_name"
              v-model="ordererInfo.last_name"
              type="text"
              class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
              placeholder="Last name"
              required
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="orderer_phone" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
              Contact Phone Number *
            </label>
            <input
              id="orderer_phone"
              v-model="ordererInfo.phone"
              type="tel"
              class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
              placeholder="+63 9XX XXX XXXX"
              required
            />
          </div>
          <div>
            <label for="orderer_email" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
              Notification Email
            </label>
            <input
              id="orderer_email"
              v-model="ordererInfo.email"
              type="email"
              class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
              placeholder="email@example.com"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Delivery Destination Selection -->
    <div class="mb-10">
      <label class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D] mb-4 block font-semibold">
        Delivery Destination
      </label>

      <div class="space-y-4">
        <!-- Default Address Option -->
        <label
          class="flex items-start p-6 border transition-all cursor-pointer rounded-none"
          :class="[
            addressOption === 'default' && user.is_default_address_complete
              ? 'border-[#1A1A1A] bg-[#FAFAFA]'
              : 'border-[#E2DDD5] hover:border-stone-400',
            !user.is_default_address_complete ? 'opacity-60 cursor-not-allowed' : ''
          ]"
        >
          <input
            type="radio"
            name="address_option"
            value="default"
            class="mt-1 mr-4 w-4 h-4 accent-[#1A1A1A]"
            :checked="addressOption === 'default'"
            :disabled="!user.is_default_address_complete"
            @change="addressOption = 'default'"
          />
          <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
              <div class="font-azeret text-[10px] text-[#1A1A1A] tracking-[0.2em] uppercase font-semibold">
                Primary Atelier Address
              </div>
              <span v-if="user.is_default_address_complete" class="font-azeret text-[9px] uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                Saved Destination
              </span>
            </div>
            <div v-if="user.is_default_address_complete" class="font-sans text-sm text-stone-600 leading-relaxed font-light">
              {{ user.street }}<br />
              {{ user.barangay }}, {{ user.city }}<br />
              <span v-if="user.province">{{ user.province }}, </span>
              {{ user.region }} {{ user.zip_code }}
            </div>
            <div v-else class="font-azeret text-xs text-red-500 mt-1">
              Atelier address profile incomplete. Please provide alternative destination below.
            </div>
          </div>
        </label>

        <!-- Custom Destination Option -->
        <label
          class="flex items-start p-6 border transition-all cursor-pointer rounded-none"
          :class="addressOption === 'custom' ? 'border-[#1A1A1A] bg-[#FAFAFA]' : 'border-[#E2DDD5] hover:border-stone-400'"
        >
          <input
            type="radio"
            name="address_option"
            value="custom"
            class="mt-1 mr-4 w-4 h-4 accent-[#1A1A1A]"
            :checked="addressOption === 'custom'"
            @change="addressOption = 'custom'"
          />
          <div class="flex-1">
            <div class="font-azeret text-[10px] text-[#1A1A1A] tracking-[0.2em] uppercase font-semibold mb-2">
              Alternative Delivery Address
            </div>
            <p class="font-sans text-sm text-stone-500 font-light mb-0">
              Deliver to a different destination or edit address fields for this order.
            </p>
          </div>
        </label>
      </div>
    </div>

    <!-- Custom Address Form (Cascading PSGC Picker) -->
    <div v-if="addressOption === 'custom'" class="space-y-6 pt-4 border-t border-stone-100 mb-10 animate-fade-in">
      <div class="mb-4">
        <span class="font-azeret text-[10px] uppercase tracking-[0.2em] text-[#B6965D] block mb-1 font-semibold">
          Alternative Address Details
        </span>
        <p class="font-sans text-xs text-stone-500 font-light mb-0">
          Pre-filled with your saved profile address. You may modify any field below.
        </p>
      </div>

      <div>
        <label for="address_line_1" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
          Street Address / House / Unit / Building *
        </label>
        <input
          id="address_line_1"
          v-model="customAddress.address_line_1"
          type="text"
          class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
          placeholder="123 Luxury Boulevard, Penthouse 4"
          required
        />
      </div>

      <!-- Philippine Standard Geographic Code (PSGC) Picker -->
      <PsgcAddressPicker
        v-model:region="customAddress.region"
        v-model:province="customAddress.province"
        v-model:city="customAddress.city"
        v-model:barangay="customAddress.barangay"
      />

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="zip_code" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-600 mb-2 block font-medium">
            ZIP Code *
          </label>
          <input
            id="zip_code"
            v-model="customAddress.zip_code"
            type="text"
            class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm px-4 py-3 min-h-[48px] focus:border-[#1A1A1A] focus:outline-none transition-colors"
            placeholder="1224"
            required
          />
        </div>

        <div class="flex items-center pt-6">
          <label class="flex items-center cursor-pointer">
            <input
              v-model="customAddress.save_as_default"
              type="checkbox"
              class="w-4 h-4 accent-[#1A1A1A] rounded-none cursor-pointer"
            />
            <span class="ml-3 font-sans text-xs text-stone-700">
              Save this address as my primary atelier profile destination
            </span>
          </label>
        </div>
      </div>
    </div>

    <!-- 3. Delivery Mode / Shipping Method Selection -->
    <div class="pt-8 border-t border-stone-100 mb-10">
      <h3 class="font-playfair text-xl text-[#1A1A1A] mb-6">Delivery Mode</h3>

      <div class="space-y-4">
        <label
          v-for="method in availableShippingMethods"
          :key="method.id"
          class="flex items-start p-6 border transition-all cursor-pointer rounded-none"
          :class="selectedShippingMethodId === method.id ? 'border-[#1A1A1A] bg-[#FAFAFA]' : 'border-[#E2DDD5] hover:border-stone-400'"
        >
          <input
            type="radio"
            name="shipping_method_selection"
            :value="method.id"
            class="mt-1 mr-4 w-4 h-4 accent-[#1A1A1A]"
            :checked="selectedShippingMethodId === method.id"
            @change="selectedShippingMethodId = method.id"
          />
          <div class="flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <div class="font-azeret text-[10px] text-[#1A1A1A] uppercase tracking-[0.2em] font-semibold mb-1">
                {{ method.name }}
              </div>
              <div class="font-sans text-xs text-stone-500 font-light">
                {{ method.description }}
              </div>
              <div class="font-azeret text-[9px] text-[#B6965D] mt-2 uppercase tracking-wider">
                Estimated Delivery: {{ method.estimated_days }}
              </div>
            </div>

            <div class="font-azeret text-xs uppercase tracking-wider text-right font-medium">
              <span v-if="method.cost === 0" class="text-[#B6965D]">COMPLIMENTARY</span>
              <span v-else class="text-[#1A1A1A]">₱{{ formatPrice(method.cost) }}</span>
            </div>
          </div>
        </label>
      </div>
    </div>

    <!-- 4. Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-stone-100">
      <button
        type="button"
        class="w-full sm:w-auto font-azeret text-[10px] uppercase tracking-[0.25em] text-stone-400 hover:text-[#1A1A1A] py-3.5 px-4 transition-colors text-center order-2 sm:order-1"
        @click="router.push('/catalogue')"
      >
        ← Return to Collections
      </button>

      <EcloreButton
        variant="primary"
        size="lg"
        class="w-full sm:w-auto order-1 sm:order-2"
        @click="handleContinue"
      >
        Continue to Payment
      </EcloreButton>
    </div>
  </div>
</template>
