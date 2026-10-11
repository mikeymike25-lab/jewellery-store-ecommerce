<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { useOrdersStore } from '@/stores/orders.store';

const route = useRoute();
const router = useRouter();
const ordersStore = useOrdersStore();

const orderNumber = ref('');
const email = ref('');
const searched = ref(false);
const copySuccess = ref(false);

onMounted(() => {
  if (route.query.order) {
    orderNumber.value = String(route.query.order);
  }
  if (route.query.email) {
    email.value = String(route.query.email);
  }
  if (orderNumber.value && email.value) {
    handleTrack();
  }
});

async function handleTrack() {
  if (!orderNumber.value.trim() || !email.value.trim()) return;
  searched.value = true;
  await ordersStore.trackOrder(orderNumber.value.trim(), email.value.trim()).catch(() => {});
}

function handleCopyTracking(trackNum: string) {
  navigator.clipboard.writeText(trackNum);
  copySuccess.value = true;
  setTimeout(() => {
    copySuccess.value = false;
  }, 2500);
}

function formatStatus(status: string) {
  switch (status) {
    case 'delivered':
      return { label: 'Delivered', bg: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
    case 'shipped':
      return { label: 'In Armored Transit', bg: 'bg-blue-50 text-blue-800 border-blue-200' };
    case 'processing':
      return { label: 'Atelier Crafting', bg: 'bg-amber-50 text-amber-800 border-amber-200' };
    case 'cancelled':
      return { label: 'Cancelled', bg: 'bg-stone-100 text-stone-600 border-stone-300' };
    default:
      return { label: 'Acquisition Confirmed', bg: 'bg-[#F3EFE7] text-[#B6965D] border-[#B6965D]/30' };
  }
}
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader />

        <main class="flex-grow max-w-3xl mx-auto px-4 py-8 sm:py-12 pb-28 w-full space-y-8">
          <!-- Back Navigation Button -->
          <div>
            <button
              type="button"
              class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-600 hover:text-[#B6965D] transition-colors py-1.5 group"
              @click="router.push('/tabs/account')"
            >
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span>Back to Account</span>
            </button>
          </div>

          <!-- Editorial Header -->
          <div class="text-center space-y-2">
            <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Insured Courier Tracker</span>
            <h1 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A]">Track Your Acquisition</h1>
            <p class="font-azeret text-xs text-[#767676] max-w-md mx-auto leading-relaxed">
              Monitor the live journey of your bespoke fine jewellery from our master atelier to your delivery destination.
            </p>
          </div>

          <!-- Lookup Card -->
          <div class="bg-white border border-[#E2DDD5] p-6 sm:p-8 shadow-sm">
            <form @submit.prevent="handleTrack" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                    Order Reference Number
                  </label>
                  <input
                    v-model="orderNumber"
                    type="text"
                    required
                    placeholder="e.g. ECL-20261011-XXXXXX"
                    class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                  />
                </div>

                <div>
                  <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                    Email Address
                  </label>
                  <input
                    v-model="email"
                    type="email"
                    required
                    placeholder="email@domain.com"
                    class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                  />
                </div>
              </div>

              <div class="pt-2 flex justify-end">
                <EcloreButton
                  variant="primary"
                  size="md"
                  type="submit"
                  :disabled="ordersStore.trackingLoading || !orderNumber.trim() || !email.trim()"
                >
                  <svg v-if="!ordersStore.trackingLoading" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  {{ ordersStore.trackingLoading ? 'Locating Parcel...' : 'Track Acquisition' }}
                </EcloreButton>
              </div>
            </form>
          </div>

          <!-- Error Alert -->
          <div v-if="ordersStore.error && searched" class="p-4 text-xs bg-red-50 text-red-800 border border-red-200 font-azeret text-center">
            {{ ordersStore.error }}
          </div>

          <!-- TRACKING RESULT -->
          <div v-if="ordersStore.trackingResult" class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-6 shadow-sm animate-fadeIn">
            <!-- Header Result -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E2DDD5] pb-4">
              <div>
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Verified Dossier</span>
                <h2 class="font-playfair text-2xl text-[#1A1A1A] mt-0.5">Order #{{ ordersStore.trackingResult.order_number }}</h2>
                <p class="font-azeret text-xs text-[#767676] mt-0.5">Placed on {{ ordersStore.trackingResult.created_at }}</p>
              </div>

              <span
                class="px-3 py-1 text-xs font-azeret uppercase font-semibold border rounded-full"
                :class="formatStatus(ordersStore.trackingResult.status).bg"
              >
                {{ formatStatus(ordersStore.trackingResult.status).label }}
              </span>
            </div>

            <!-- 4-Stage Visual Progress Timeline -->
            <div class="bg-[#F8F7F2] border border-[#E2DDD5] p-5 sm:p-6 space-y-4">
              <span class="font-azeret text-[10px] uppercase tracking-widest text-[#767676] block">
                Fulfillment Progression Timeline
              </span>

              <div class="relative grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div
                  v-for="(step, idx) in ordersStore.trackingResult.timeline"
                  :key="idx"
                  class="flex sm:flex-col items-start sm:items-center text-left sm:text-center space-x-3 sm:space-x-0"
                >
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-azeret font-semibold transition-colors flex-shrink-0"
                    :class="step.completed ? 'bg-[#B6965D] text-white' : 'bg-[#EAE6DF] text-[#767676]'"
                  >
                    <svg v-if="step.completed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else>{{ idx + 1 }}</span>
                  </div>

                  <div class="mt-0 sm:mt-2">
                    <p class="font-playfair text-sm text-[#1A1A1A] leading-tight" :class="{ 'font-bold text-[#B6965D]': step.current }">
                      {{ step.title }}
                    </p>
                    <p class="font-azeret text-[10px] text-[#767676] mt-0.5 leading-snug">{{ step.description }}</p>
                    <span v-if="step.date" class="font-azeret text-[9px] text-[#B6965D] block mt-1">{{ step.date }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Carrier & Tracking Reference Box -->
            <div
              v-if="ordersStore.trackingResult.tracking_number"
              class="border border-[#B6965D]/30 bg-[#F3EFE7]/50 p-4 flex flex-wrap items-center justify-between gap-3 text-xs font-azeret"
            >
              <div>
                <span class="text-[#767676] block text-[10px] uppercase tracking-wider">Armored Courier Partner</span>
                <strong class="text-sm text-[#1A1A1A]">{{ ordersStore.trackingResult.courier || 'Armored VIP Courier' }}</strong>
              </div>

              <div class="flex items-center space-x-2">
                <div class="text-right">
                  <span class="text-[#767676] block text-[10px] uppercase tracking-wider">Waybill Reference</span>
                  <span class="font-bold text-sm text-[#B6965D] font-mono">{{ ordersStore.trackingResult.tracking_number }}</span>
                </div>
                <button
                  type="button"
                  class="p-2 border border-[#E2DDD5] bg-white text-[#767676] hover:text-[#1A1A1A] transition-colors"
                  title="Copy Tracking Number"
                  @click="handleCopyTracking(ordersStore.trackingResult.tracking_number)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                  </svg>
                </button>
              </div>
            </div>
            <p v-if="copySuccess" class="text-xs font-azeret text-emerald-700 text-right -mt-4">
              Tracking number copied to clipboard.
            </p>

            <!-- Line Items in this Order -->
            <div class="space-y-3">
              <span class="font-azeret text-[10px] uppercase tracking-widest text-[#767676] block">
                Acquisition Contents ({{ ordersStore.trackingResult.items.length }})
              </span>
              <div class="border border-[#E2DDD5] divide-y divide-[#EAE6DF]">
                <div
                  v-for="item in ordersStore.trackingResult.items"
                  :key="item.id"
                  class="p-3.5 flex items-center justify-between gap-3 text-xs font-azeret"
                >
                  <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-12 h-12 bg-[#F3EFE7] border border-[#E2DDD5] flex-shrink-0 flex items-center justify-center overflow-hidden">
                      <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.product_name"
                        class="w-full h-full object-cover"
                      />
                      <svg v-else class="w-5 h-5 text-[#B6965D]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                      </svg>
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-playfair text-sm text-[#1A1A1A] truncate">{{ item.product_name }}</h4>
                      <span class="text-[10px] text-[#767676]">Qty: {{ item.quantity }} &bull; SKU: {{ item.product_sku }}</span>
                    </div>
                  </div>

                  <CurrencyDisplay :amount="item.total_price" class="font-bold text-[#1A1A1A] flex-shrink-0" />
                </div>
              </div>
            </div>

            <!-- Concierge Help Footer -->
            <div class="border-t border-[#E2DDD5] pt-4 text-center font-azeret text-xs text-[#767676] space-y-1">
              <p>Questions regarding your insured delivery?</p>
              <p>Contact Éclore Concierge Relations at <a href="mailto:concierge@eclorejewellery.shop" class="text-[#B6965D] underline">concierge@eclorejewellery.shop</a> or call +63 (2) 8888-ECLORE.</p>
            </div>
          </div>

        </main>

        <EcloreFooter />
      </div>
    </ion-content>
  </ion-page>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
