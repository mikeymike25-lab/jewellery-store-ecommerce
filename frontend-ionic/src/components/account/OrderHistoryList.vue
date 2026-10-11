<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import type { Order, OrderItem } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { useOrdersStore } from '@/stores/orders.store';

const emit = defineEmits<{
  (e: 'view-order', order: Order): void;
  (e: 'open-rma', order: Order, item?: OrderItem): void;
}>();

const ordersStore = useOrdersStore();
const activeFilter = ref<'all' | 'processing' | 'delivered' | 'cancelled'>('all');
const downloadingOrderNumber = ref<string | null>(null);
const downloadSuccessOrderNumber = ref<string | null>(null);
const downloadErrorOrderNumber = ref<string | null>(null);
const downloadError = ref('');

onMounted(() => {
  ordersStore.fetchOrders(1);
  ordersStore.fetchReturnsRepairs();
});

const filteredOrders = computed(() => {
  if (activeFilter.value === 'all') return ordersStore.orders;
  if (activeFilter.value === 'processing') {
    return ordersStore.orders.filter((o) => ['pending', 'processing', 'shipped'].includes(o.status));
  }
  return ordersStore.orders.filter((o) => o.status === activeFilter.value);
});

async function handleDownloadReceipt(order: Order, event: Event) {
  event.stopPropagation();
  downloadError.value = '';
  downloadErrorOrderNumber.value = null;
  downloadSuccessOrderNumber.value = null;
  downloadingOrderNumber.value = order.order_number;
  try {
    await ordersStore.downloadReceiptPdf(order.order_number);
    downloadSuccessOrderNumber.value = order.order_number;
    setTimeout(() => {
      if (downloadSuccessOrderNumber.value === order.order_number) {
        downloadSuccessOrderNumber.value = null;
      }
    }, 4000);
  } catch (err: any) {
    downloadError.value = err.message || 'Receipt could not be downloaded.';
    downloadErrorOrderNumber.value = order.order_number;
    setTimeout(() => {
      if (downloadErrorOrderNumber.value === order.order_number) {
        downloadError.value = '';
        downloadErrorOrderNumber.value = null;
      }
    }, 5000);
  } finally {
    downloadingOrderNumber.value = null;
  }
}

function formatStatusBadge(status: string) {
  switch (status) {
    case 'delivered':
      return { label: 'Delivered', class: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
    case 'shipped':
      return { label: 'In Transit', class: 'bg-blue-50 text-blue-800 border-blue-200' };
    case 'processing':
      return { label: 'Atelier Crafting', class: 'bg-amber-50 text-amber-800 border-amber-200' };
    case 'cancelled':
      return { label: 'Cancelled', class: 'bg-stone-100 text-stone-600 border-stone-300' };
    default:
      return { label: 'Confirmed', class: 'bg-[#F3EFE7] text-[#B6965D] border-[#B6965D]/30' };
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Filter Tabs (Clean luxury tabs with no overlapping underline) -->
    <div class="flex items-center justify-between border-b border-[#E2DDD5] overflow-x-auto no-scrollbar text-xs font-azeret uppercase tracking-wider">
      <div class="flex space-x-6 sm:space-x-8">
        <button
          type="button"
          class="pb-3 px-1 transition-all border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider -mb-[1px] cursor-pointer"
          :class="activeFilter === 'all'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:border-stone-300'"
          @click="activeFilter = 'all'"
        >
          All Acquisitions
        </button>
        <button
          type="button"
          class="pb-3 px-1 transition-all border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider -mb-[1px] cursor-pointer"
          :class="activeFilter === 'processing'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:border-stone-300'"
          @click="activeFilter = 'processing'"
        >
          In Progress
        </button>
        <button
          type="button"
          class="pb-3 px-1 transition-all border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider -mb-[1px] cursor-pointer"
          :class="activeFilter === 'delivered'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:border-stone-300'"
          @click="activeFilter = 'delivered'"
        >
          Delivered
        </button>
      </div>

      <span class="text-[10px] text-[#767676] hidden sm:inline pb-3">
        Showing {{ filteredOrders.length }} Order{{ filteredOrders.length === 1 ? '' : 's' }}
      </span>
    </div>

    <!-- Error Banner -->
    <div v-if="downloadError" class="p-3 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
      {{ downloadError }}
    </div>

    <!-- Loading Skeleton -->
    <div v-if="ordersStore.loading && ordersStore.orders.length === 0" class="space-y-4">
      <div v-for="i in 2" :key="i" class="bg-white border border-[#E2DDD5] p-6 animate-pulse space-y-4">
        <div class="h-4 bg-[#EAE6DF] w-1/3 rounded" />
        <div class="h-10 bg-[#F3EFE7] w-full rounded" />
        <div class="h-4 bg-[#EAE6DF] w-1/4 rounded" />
      </div>
    </div>

    <!-- Orders Cards List -->
    <div v-else-if="filteredOrders.length > 0" class="space-y-4">
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="bg-white border border-[#E2DDD5] hover:border-[#B6965D] transition-colors p-5 sm:p-6 space-y-4 shadow-sm"
      >
        <!-- Top Row: Order Number, Date, Status Badges -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#EAE6DF] pb-3">
          <div>
            <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Order Reference</span>
            <h3 class="font-playfair text-lg text-[#1A1A1A] font-semibold">#{{ order.order_number }}</h3>
            <p class="font-azeret text-[10px] text-[#767676]">
              {{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
            </p>
          </div>

          <div class="flex items-center space-x-2">
            <span
              class="px-2.5 py-1 text-[10px] font-azeret uppercase font-semibold border rounded-full"
              :class="formatStatusBadge(order.status).class"
            >
              {{ formatStatusBadge(order.status).label }}
            </span>
            <span
              v-if="order.payment_status === 'paid'"
              class="px-2 py-0.5 text-[9px] font-azeret uppercase font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full"
            >
              Paid
            </span>
          </div>
        </div>

        <!-- Middle Row: Item Previews & Total -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <!-- Items preview -->
          <div class="flex items-center space-x-3 overflow-x-auto max-w-full">
            <div
              v-for="item in (order.order_items || order.items || []).slice(0, 3)"
              :key="item.id"
              class="w-12 h-12 bg-[#F3EFE7] border border-[#E2DDD5] flex-shrink-0 flex items-center justify-center overflow-hidden"
              :title="item.product_name"
            >
              <img
                v-if="item.product && item.product.image_url"
                :src="item.product.image_url"
                :alt="item.product_name"
                class="w-full h-full object-cover"
              />
              <svg v-else class="w-5 h-5 text-[#B6965D]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
              </svg>
            </div>
            <span v-if="(order.order_items || order.items || []).length > 3" class="text-xs font-azeret text-[#767676]">
              +{{ (order.order_items || order.items || []).length - 3 }} more
            </span>
            <span class="font-azeret text-xs text-[#767676] pl-1">
              {{ (order.order_items || order.items || []).length }} Creation{{ (order.order_items || order.items || []).length === 1 ? '' : 's' }}
            </span>
          </div>

          <!-- Total -->
          <div class="text-left sm:text-right">
            <span class="font-azeret text-[10px] uppercase tracking-wider text-[#767676] block">Total Amount</span>
            <CurrencyDisplay :amount="order.total_amount" class="font-playfair text-lg text-[#1A1A1A] font-bold" />
          </div>
        </div>

        <!-- Bottom Row: Action Controls with Balanced Ergonomics -->
        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#EAE6DF]">
          <div class="text-[10px] font-azeret text-[#767676]">
            <span v-if="order.tracking_number">
              Tracking: <strong class="text-[#B6965D]">{{ order.tracking_number }}</strong>
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Receipt Download CTA -->
            <button
              v-if="order.payment_status && order.payment_status.toLowerCase() === 'paid'"
              type="button"
              class="inline-flex items-center px-3 py-2 text-xs font-azeret uppercase tracking-wider text-[#1A1A1A] hover:text-[#B6965D] border border-[#E2DDD5] hover:border-[#B6965D] transition-colors disabled:opacity-50"
              :disabled="downloadingOrderNumber === order.order_number"
              @click="handleDownloadReceipt(order, $event)"
            >
              <svg class="w-3.5 h-3.5 mr-1 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              {{ downloadingOrderNumber === order.order_number ? 'Downloading...' : 'Receipt PDF' }}
            </button>

            <!-- RMA Service CTA -->
            <button
              v-if="['delivered', 'shipped', 'processing'].includes(order.status)"
              type="button"
              class="inline-flex items-center px-3 py-2 text-xs font-azeret uppercase tracking-wider text-[#767676] hover:text-[#1A1A1A] border border-[#E2DDD5] hover:border-[#1A1A1A] transition-colors"
              @click="emit('open-rma', order)"
            >
              Service / RMA
            </button>

            <!-- View Details CTA -->
            <EcloreButton variant="primary" size="sm" @click="emit('view-order', order)">
              View Details
            </EcloreButton>
          </div>

          <!-- Feedback for Receipt -->
          <div v-if="downloadError && downloadErrorOrderNumber === order.order_number" class="w-full mt-2 p-2 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
            {{ downloadError }}
          </div>
          <div v-if="downloadSuccessOrderNumber === order.order_number" class="w-full mt-2 p-2 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-azeret">
            Official tax receipt downloaded successfully.
          </div>
        </div>

      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white border border-[#E2DDD5] p-8 sm:p-12 text-center space-y-4">
      <div class="w-12 h-12 bg-[#F3EFE7] rounded-full mx-auto flex items-center justify-center text-[#B6965D]">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
      </div>
      <h3 class="font-playfair text-xl text-[#1A1A1A]">No Acquisitions Recorded</h3>
      <p class="font-azeret text-xs text-[#767676] max-w-sm mx-auto">
        Your acquisition journal is currently empty. Explore our fine creations at the atelier.
      </p>
      <div class="pt-2">
        <router-link to="/catalogue">
          <EcloreButton variant="primary" size="md">
            Discover Catalogue
          </EcloreButton>
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
