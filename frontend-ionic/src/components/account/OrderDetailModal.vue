<script setup lang="ts">
import { ref } from 'vue';
import type { Order, OrderItem } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { useOrdersStore } from '@/stores/orders.store';

const props = defineProps<{
  isOpen: boolean;
  order: Order | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'open-rma', order: Order, item?: OrderItem): void;
}>();

const ordersStore = useOrdersStore();
const isDownloadingPdf = ref(false);
const pdfError = ref('');
const pdfSuccess = ref(false);

async function handleDownloadReceipt() {
  if (!props.order) return;
  pdfError.value = '';
  pdfSuccess.value = false;
  isDownloadingPdf.value = true;
  try {
    await ordersStore.downloadReceiptPdf(props.order.order_number);
    pdfSuccess.value = true;
    setTimeout(() => {
      pdfSuccess.value = false;
    }, 5000);
  } catch (err: any) {
    pdfError.value = err.message || 'Receipt could not be downloaded.';
    setTimeout(() => {
      pdfError.value = '';
    }, 6000);
  } finally {
    isDownloadingPdf.value = false;
  }
}

function handleOpenRma(item?: OrderItem) {
  if (!props.order) return;
  emit('open-rma', props.order, item);
}

function formatStatus(status: string) {
  switch (status) {
    case 'delivered':
      return { label: 'Delivered', bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200' };
    case 'shipped':
      return { label: 'In Transit', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200' };
    case 'processing':
      return { label: 'Atelier Crafting', bg: 'bg-amber-50', text: 'text-amber-800', border: 'border-amber-200' };
    case 'cancelled':
      return { label: 'Cancelled', bg: 'bg-stone-100', text: 'text-stone-600', border: 'border-stone-300' };
    default:
      return { label: 'Order Confirmed', bg: 'bg-[#F3EFE7]', text: 'text-[#B6965D]', border: 'border-[#B6965D]/30' };
  }
}

function formatPaymentStatus(status: string) {
  switch (status) {
    case 'paid':
      return { label: 'Paid', class: 'text-emerald-700 bg-emerald-50 border-emerald-200' };
    case 'failed':
      return { label: 'Failed', class: 'text-red-700 bg-red-50 border-red-200' };
    default:
      return { label: 'Pending Payment', class: 'text-amber-800 bg-amber-50 border-amber-200' };
  }
}

function resolveTimeline(status: string) {
  return [
    {
      title: 'Order Placed',
      desc: 'Acquisition recorded & confirmed',
      completed: true,
      current: status === 'pending',
    },
    {
      title: 'Atelier Crafting',
      desc: 'Certified & carefully prepared',
      completed: ['processing', 'shipped', 'delivered'].includes(status),
      current: status === 'processing',
    },
    {
      title: 'Armored Transit',
      desc: props.order?.tracking_number ? `Courier Ref: ${props.order.tracking_number}` : 'Insured armored carrier',
      completed: ['shipped', 'delivered'].includes(status),
      current: status === 'shipped',
    },
    {
      title: 'Delivered',
      desc: 'Received at destination',
      completed: status === 'delivered',
      current: status === 'delivered',
    },
  ];
}
</script>

<template>
  <div v-if="isOpen && order" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="emit('close')" />

    <div class="flex min-h-full items-center justify-center p-3 sm:p-6">
      <div class="relative w-full max-w-2xl bg-[#F8F7F2] border border-[#E2DDD5] shadow-2xl p-5 sm:p-8 space-y-6 text-[#1A1A1A] animate-fadeIn">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-[#E2DDD5] pb-4">
          <div>
            <div class="flex items-center space-x-2">
              <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Acquisition Details</span>
              <span
                class="px-2 py-0.5 text-[9px] font-azeret uppercase tracking-wider font-semibold border rounded-full"
                :class="[formatStatus(order.status).bg, formatStatus(order.status).text, formatStatus(order.status).border]"
              >
                {{ formatStatus(order.status).label }}
              </span>
            </div>
            <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">Order #{{ order.order_number }}</h2>
            <p class="font-azeret text-xs text-[#767676] mt-0.5">Placed on {{ new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
          </div>

          <button
            type="button"
            class="p-2 text-[#767676] hover:text-[#1A1A1A] transition-colors -mr-2 -mt-2"
            @click="emit('close')"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- 4-Stage Visual Delivery Timeline -->
        <div class="bg-white border border-[#E2DDD5] p-4 sm:p-5">
          <div class="font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-4">Delivery Progression</div>
          <div class="relative flex flex-col sm:flex-row justify-between gap-4 sm:gap-2">
            <div
              v-for="(step, idx) in resolveTimeline(order.status)"
              :key="idx"
              class="flex sm:flex-col items-start sm:items-center text-left sm:text-center flex-1 space-x-3 sm:space-x-0"
            >
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-azeret transition-colors flex-shrink-0"
                :class="step.completed ? 'bg-[#B6965D] text-white' : 'bg-[#EAE6DF] text-[#767676]'"
              >
                <svg v-if="step.completed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <div class="mt-0 sm:mt-2">
                <p class="font-playfair text-sm text-[#1A1A1A] leading-tight" :class="{ 'font-semibold': step.current }">{{ step.title }}</p>
                <p class="font-azeret text-[10px] text-[#767676] mt-0.5 leading-snug">{{ step.desc }}</p>
              </div>
            </div>
          </div>

          <div v-if="order.tracking_number" class="mt-4 pt-3 border-t border-[#EAE6DF] flex flex-wrap items-center justify-between gap-2 text-xs font-azeret">
            <span class="text-[#767676]">Carrier: <strong class="text-[#1A1A1A]">{{ order.courier || 'Armored VIP Courier' }}</strong></span>
            <span class="text-[#767676]">Waybill: <strong class="text-[#B6965D]">{{ order.tracking_number }}</strong></span>
          </div>
        </div>

        <!-- Purchased Line Items -->
        <div class="space-y-3">
          <div class="font-azeret text-[10px] uppercase tracking-widest text-[#767676]">Creations ({{ (order.order_items || order.items || []).length }})</div>
          <div class="bg-white border border-[#E2DDD5] divide-y divide-[#EAE6DF]">
            <div
              v-for="item in (order.order_items || order.items || [])"
              :key="item.id"
              class="p-3 sm:p-4 flex items-center justify-between gap-4"
            >
              <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                <div class="w-14 h-14 bg-[#F3EFE7] border border-[#E2DDD5] flex-shrink-0 flex items-center justify-center overflow-hidden">
                  <img
                    v-if="item.product && item.product.image_url"
                    :src="item.product.image_url"
                    :alt="item.product_name"
                    class="w-full h-full object-cover"
                  />
                  <svg v-else class="w-6 h-6 text-[#B6965D]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h4 class="font-playfair text-sm text-[#1A1A1A] truncate">{{ item.product_name }}</h4>
                  <div class="font-azeret text-[10px] text-[#767676] space-x-2 mt-0.5">
                    <span>Qty: {{ item.quantity }}</span>
                    <span>&bull;</span>
                    <span v-if="item.product_sku" class="uppercase">{{ item.product_sku }}</span>
                  </div>
                </div>
              </div>

              <div class="text-right flex-shrink-0">
                <CurrencyDisplay :amount="item.total_price || item.subtotal || item.price * item.quantity" class="font-azeret text-sm font-semibold text-[#1A1A1A]" />
                <button
                  v-if="['delivered', 'shipped', 'processing'].includes(order.status)"
                  type="button"
                  class="block mt-1 text-[10px] font-azeret text-[#B6965D] hover:underline"
                  @click="handleOpenRma(item)"
                >
                  Return / Repair
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Financials & Destination Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Destination -->
          <div class="bg-white border border-[#E2DDD5] p-4 text-xs font-azeret space-y-1">
            <span class="text-[10px] uppercase tracking-widest text-[#767676] block mb-1">Destination</span>
            <p class="font-semibold text-[#1A1A1A]">
              {{ order.shipping_address?.first_name || '' }} {{ order.shipping_address?.last_name || '' }}
            </p>
            <p class="text-[#767676]">{{ order.shipping_address?.address_line_1 || order.shipping_address?.street || 'Flagship Atelier' }}</p>
            <p class="text-[#767676]">
              {{ order.shipping_address?.barangay ? `${order.shipping_address.barangay}, ` : '' }}
              {{ order.shipping_address?.city || '' }}
              {{ order.shipping_address?.zip_code ? ` ${order.shipping_address.zip_code}` : '' }}
            </p>
            <div class="pt-2 text-[10px] text-[#767676]">
              Payment: <strong class="uppercase text-[#1A1A1A]">{{ order.payment_method }}</strong>
              <span class="ml-1.5 px-1.5 py-0.2 rounded border text-[9px]" :class="formatPaymentStatus(order.payment_status).class">
                {{ formatPaymentStatus(order.payment_status).label }}
              </span>
            </div>
          </div>

          <!-- Total Breakdown -->
          <div class="bg-white border border-[#E2DDD5] p-4 text-xs font-azeret space-y-1.5">
            <span class="text-[10px] uppercase tracking-widest text-[#767676] block mb-1">Financial Summary</span>
            <div class="flex justify-between text-[#767676]">
              <span>Net Subtotal:</span>
              <CurrencyDisplay :amount="order.subtotal" />
            </div>
            <div class="flex justify-between text-[#767676]">
              <span>12% Philippine VAT:</span>
              <CurrencyDisplay :amount="order.tax_amount || 0" />
            </div>
            <div class="flex justify-between text-[#767676]">
              <span>Armored Shipping:</span>
              <span>{{ (order.shipping_amount || 0) === 0 ? 'Complimentary' : `₱${Number(order.shipping_amount).toLocaleString()}` }}</span>
            </div>
            <div class="flex justify-between border-t border-[#EAE6DF] pt-2 font-semibold text-sm text-[#1A1A1A]">
              <span>Total Paid:</span>
              <CurrencyDisplay :amount="order.total_amount" class="text-[#B6965D]" />
            </div>
          </div>
        </div>

        <!-- Feedback for Receipt -->
        <div v-if="pdfError" class="p-3 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
          {{ pdfError }}
        </div>
        <div v-if="pdfSuccess" class="p-3 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-azeret">
          Official tax receipt PDF downloaded successfully.
        </div>

        <!-- Actions -->
        <div class="border-t border-[#E2DDD5] pt-4 flex flex-wrap items-center justify-between gap-3">
          <EcloreButton
            v-if="['delivered', 'shipped', 'processing'].includes(order.status)"
            variant="outline"
            size="sm"
            @click="handleOpenRma()"
          >
            After-Sales Service / RMA
          </EcloreButton>
          <div v-else />

          <div class="flex items-center space-x-2">
            <EcloreButton
              v-if="order.payment_status && order.payment_status.toLowerCase() === 'paid'"
              variant="primary"
              size="sm"
              :disabled="isDownloadingPdf"
              @click="handleDownloadReceipt"
            >
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              {{ isDownloadingPdf ? 'Generating...' : 'Download Tax Receipt' }}
            </EcloreButton>

            <EcloreButton variant="outline" size="sm" @click="emit('close')">
              Close
            </EcloreButton>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
