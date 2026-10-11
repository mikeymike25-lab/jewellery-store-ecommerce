<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import { Browser } from '@capacitor/browser';
import { App } from '@capacitor/app';
import { apiClient } from '@/api/client';
import { useCartStore } from '@/stores/cart.store';
import { useOrdersStore } from '@/stores/orders.store';
import { useAuthStore } from '@/stores/auth.store';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const ordersStore = useOrdersStore();
const authStore = useAuthStore();

const orderNumber = computed(() => (route.params.orderNumber as string) || (route.query.order as string) || '');
const invoiceUrl = computed(() => (route.query.invoice_url as string) || '');

const order = ref<any>(null);
const paymentStatus = ref<'pending' | 'paid' | 'failed'>('pending');
const loading = ref(true);
const pollingActive = ref(false);
let pollingInterval: any = null;
let pollCount = 0;
const maxPolls = 15; // 15 polls * 2.5s = ~37.5s

function formatPrice(amount: number): string {
  return amount.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function fetchOrderDetails() {
  if (!orderNumber.value) return;
  try {
    const res = await apiClient.get(`/v1/orders/${orderNumber.value}`);
    if (res.data.success && res.data.order) {
      order.value = res.data.order;
      paymentStatus.value = res.data.order.payment_status || 'pending';

      if (paymentStatus.value === 'paid') {
        stopPolling();
        await cartStore.fetchCart();
      }
    }
  } catch (err) {
    console.warn('Failed to load full order details:', err);
  } finally {
    loading.value = false;
  }
}

async function checkPaymentStatus() {
  if (!orderNumber.value || paymentStatus.value === 'paid') return;

  try {
    const res = await apiClient.get(`/v1/orders/${orderNumber.value}/status`);
    if (res.data.success && res.data.order) {
      const status = res.data.order.payment_status;
      paymentStatus.value = status;

      if (status === 'paid') {
        stopPolling();
        await Promise.allSettled([
          fetchOrderDetails(),
          cartStore.fetchCart(),
          ordersStore.fetchOrders(1),
        ]);
      } else if (status === 'failed') {
        stopPolling();
      }
    }
  } catch (err) {
    console.warn('Status poll warning:', err);
  }
}

function startPolling() {
  if (pollingActive.value) return;
  pollingActive.value = true;
  pollCount = 0;

  pollingInterval = setInterval(async () => {
    pollCount++;
    await checkPaymentStatus();

    if (pollCount >= maxPolls || paymentStatus.value === 'paid' || paymentStatus.value === 'failed') {
      stopPolling();
    }
  }, 2500);
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
  pollingActive.value = false;
}

const isAuthorizing = ref(false);
const isRefreshingStatus = ref(false);

const isSimulated = computed(() => {
  const url = invoiceUrl.value || order.value?.notes || '';
  if (!url) return true;
  return (
    url.startsWith('simulated') ||
    url.includes('simulated=xendit') ||
    url.includes('/checkout/confirmation') ||
    (!url.startsWith('http://') && !url.startsWith('https://')) ||
    (url.includes('checkout/confirmation') && !url.includes('xendit.co'))
  );
});

async function handleManualRefresh() {
  if (isRefreshingStatus.value) return;
  isRefreshingStatus.value = true;
  try {
    await checkPaymentStatus();
  } finally {
    isRefreshingStatus.value = false;
  }
}

// Open Xendit hosted invoice portal or handle in-app simulation
async function openSecurePortal() {
  // If simulation or internal link, process payment directly in-app without leaving to web
  if (isSimulated.value) {
    if (!orderNumber.value) return;
    isAuthorizing.value = true;
    try {
      const res = await apiClient.post(`/v1/orders/${orderNumber.value}/simulate-payment`);
      if (res.data.success) {
        paymentStatus.value = 'paid';
        stopPolling();
        await Promise.allSettled([
          fetchOrderDetails(),
          cartStore.fetchCart(),
          ordersStore.fetchOrders(1),
        ]);
      } else {
        await checkPaymentStatus();
      }
    } catch (e) {
      console.warn('Simulation auth warning:', e);
      await checkPaymentStatus();
    } finally {
      isAuthorizing.value = false;
    }
    return;
  }

  const targetUrl = invoiceUrl.value || order.value?.notes || '';
  if (!targetUrl) {
    await handleManualRefresh();
    return;
  }

  try {
    // Attempt Capacitor Browser on native devices
    await Browser.open({ url: targetUrl });
  } catch (e) {
    // Fallback to web window
    window.open(targetUrl, '_blank');
  }

  // Start polling in background
  startPolling();
}

function goToAccountOrders() {
  ordersStore.fetchOrders(1);
  router.push('/tabs/account');
}

const isDownloadingReceipt = ref(false);
const receiptError = ref('');
const receiptSuccess = ref(false);

async function handleDownloadReceipt() {
  if (!orderNumber.value) return;
  receiptError.value = '';
  receiptSuccess.value = false;
  isDownloadingReceipt.value = true;
  try {
    await ordersStore.downloadReceiptPdf(orderNumber.value);
    receiptSuccess.value = true;
    setTimeout(() => {
      receiptSuccess.value = false;
    }, 5000);
  } catch (err: any) {
    receiptError.value = err.message || 'Receipt could not be downloaded.';
    setTimeout(() => {
      receiptError.value = '';
    }, 6000);
  } finally {
    isDownloadingReceipt.value = false;
  }
}

// Window focus event to immediately re-check status when user returns to tab
function handleWindowFocus() {
  if (paymentStatus.value === 'pending') {
    checkPaymentStatus();
  }
}

// Capacitor Deep Link Listener (eclore://)
let appUrlListener: any = null;

onMounted(async () => {
  window.addEventListener('focus', handleWindowFocus);

  try {
    Browser.addListener('browserFinished', async () => {
      await checkPaymentStatus();
    });
  } catch (e) {
    // non-fatal
  }

  try {
    appUrlListener = await App.addListener('appUrlOpen', async (event) => {
      if (event.url.includes('eclore://') && event.url.includes('return/success')) {
        try {
          await Browser.close();
        } catch (e) {
          // non-fatal
        }
        await checkPaymentStatus();
      }
    });
  } catch (e) {
    // Not running on native Capacitor or plugin unsupported
  }

  await fetchOrderDetails();

  // If pending and COD, order is considered placed and processing
  if (order.value?.payment_method?.includes('Cash on Delivery')) {
    paymentStatus.value = 'paid'; // COD treated as confirmed acquisition
    stopPolling();
  } else if (paymentStatus.value === 'pending') {
    startPolling();
  }
});

onUnmounted(() => {
  stopPolling();
  window.removeEventListener('focus', handleWindowFocus);
  try {
    Browser.removeAllListeners();
  } catch (e) {
    // non-fatal
  }
  if (appUrlListener && typeof appUrlListener.remove === 'function') {
    appUrlListener.remove();
  }
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true">
      <div class="min-h-screen flex flex-col bg-[#F3EFE7] text-[#1A1A1A]">
        <EcloreHeader />

        <main class="flex-grow max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
          <!-- Step indicator showing Confirmation active -->
          <div class="mb-10 text-center">
            <span class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D]">
              Atelier Acquisition Protocol
            </span>
            <h1 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mt-2 mb-6">
              Order Confirmation
            </h1>

            <div class="max-w-md mx-auto grid grid-cols-4 gap-2 text-center font-azeret text-[9px] uppercase tracking-wider">
              <div class="border-b-2 border-[#1A1A1A] pb-2 text-[#1A1A1A]">1. Shipping</div>
              <div class="border-b-2 border-[#1A1A1A] pb-2 text-[#1A1A1A]">2. Payment</div>
              <div class="border-b-2 border-[#1A1A1A] pb-2 text-[#1A1A1A]">3. Review</div>
              <div class="border-b-2 border-[#B6965D] pb-2 text-[#1A1A1A] font-bold">4. Confirm</div>
            </div>
          </div>

          <!-- 1. State: PAID / CONFIRMED -->
          <div
            v-if="paymentStatus === 'paid'"
            class="bg-white border border-[#E2DDD5] p-8 sm:p-12 text-center rounded-lg shadow-sm animate-fade-in"
          >
            <div class="mx-auto flex items-center justify-center h-20 w-20 bg-[#F9F8F6] border border-[#E2DDD5] mb-6 rounded-full">
              <svg class="h-8 w-8 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>

            <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mb-3 font-normal">
              Acquisition Authenticated
            </h2>
            <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest mb-8">
              Payment & Delivery Mandate Verified
            </p>

            <div class="border-t border-b border-stone-100 py-8 my-8 max-w-lg mx-auto">
              <span class="font-azeret text-[9px] tracking-[0.25em] text-stone-400 uppercase block mb-1">
                ORDER MANDATE
              </span>
              <p class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] tracking-wider mb-2">
                #{{ orderNumber }}
              </p>
              <p v-if="order" class="font-azeret text-xs text-stone-600 uppercase tracking-wider">
                Value: <span class="font-bold text-[#1A1A1A]">₱{{ formatPrice(Number(order.total_amount) || 0) }}</span>
              </p>
            </div>

            <p class="font-sans text-sm text-stone-600 max-w-md mx-auto leading-relaxed font-light mb-10">
              Your exquisite selection is now officially in preparation in our atelier. A formal confirmation and armored courier tracking link have been dispatched to your email.
            </p>

            <!-- Receipt download for paid customers -->
            <div v-if="paymentStatus === 'paid' || authStore.isAuthenticated" class="mb-8">
              <EcloreButton
                variant="outline"
                size="md"
                class="w-full sm:w-auto mx-auto inline-flex items-center justify-center space-x-2"
                :disabled="isDownloadingReceipt"
                @click="handleDownloadReceipt"
              >
                <svg class="w-4 h-4 mr-1 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ isDownloadingReceipt ? 'Generating Receipt...' : 'Download Official Tax Receipt' }}</span>
              </EcloreButton>

              <div v-if="receiptError" class="mt-3 max-w-sm mx-auto p-2 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
                {{ receiptError }}
              </div>
              <div v-if="receiptSuccess" class="mt-3 max-w-sm mx-auto p-2 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-azeret">
                Official tax receipt downloaded successfully.
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <EcloreButton
                variant="primary"
                size="lg"
                class="w-full sm:w-auto"
                @click="router.push('/catalogue')"
              >
                Return to Collections
              </EcloreButton>

              <EcloreButton
                variant="outline-gold"
                size="lg"
                class="w-full sm:w-auto"
                @click="goToAccountOrders"
              >
                View Account Orders
              </EcloreButton>
            </div>
          </div>

          <!-- 2. State: PENDING (Xendit Gateway awaiting transaction) -->
          <div
            v-else-if="paymentStatus === 'pending'"
            class="bg-white border border-[#E2DDD5] p-8 sm:p-12 text-center rounded-lg shadow-sm animate-fade-in"
          >
            <div class="mx-auto flex items-center justify-center h-20 w-20 bg-[#F9F8F6] border border-[#E2DDD5] mb-6 rounded-full">
              <div class="animate-spin h-8 w-8 border-2 border-stone-300 border-t-[#B6965D] rounded-full"></div>
            </div>

            <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mb-3 font-normal">
              Securing Treasury Protocol
            </h2>
            <p class="font-azeret text-xs text-stone-500 uppercase tracking-widest mb-8">
              Establishing Fortified Connection to Xendit
            </p>

            <div class="border-t border-b border-stone-100 py-8 my-8 max-w-lg mx-auto">
              <span class="font-azeret text-[9px] tracking-[0.25em] text-stone-400 uppercase block mb-1">
                ORDER MANDATE
              </span>
              <p class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] tracking-wider mb-2">
                #{{ orderNumber }}
              </p>
              <p v-if="order" class="font-azeret text-xs text-stone-600 uppercase tracking-wider">
                Mandate Value: <span class="font-bold text-[#1A1A1A]">₱{{ formatPrice(Number(order.total_amount) || 0) }}</span>
              </p>
            </div>

            <p class="font-sans text-sm text-stone-600 max-w-md mx-auto leading-relaxed font-light mb-8">
              Please finalize your transaction via the encrypted gateway. This screen actively polls for confirmation and will authenticate automatically once complete.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <EcloreButton
                variant="primary"
                size="lg"
                class="w-full sm:w-auto"
                :loading="isAuthorizing"
                @click="openSecurePortal"
              >
                Enter Secure Portal
              </EcloreButton>

              <EcloreButton
                variant="outline-gold"
                size="lg"
                class="w-full sm:w-auto"
                :loading="isRefreshingStatus"
                @click="handleManualRefresh"
              >
                Refresh Payment Status
              </EcloreButton>
            </div>
          </div>

          <!-- 3. State: FAILED -->
          <div
            v-else
            class="bg-white border border-[#E2DDD5] p-8 sm:p-12 text-center rounded-lg shadow-sm animate-fade-in"
          >
            <div class="mx-auto flex items-center justify-center h-20 w-20 bg-red-50 border border-red-200 mb-6 rounded-full">
              <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>

            <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A] mb-3 font-normal">
              Transaction Unfulfilled
            </h2>
            <p class="font-sans text-sm text-stone-500 max-w-md mx-auto font-light mb-8">
              Our treasury was unable to finalize your acquisition at this moment. You may reattempt or select an alternative payment method.
            </p>

            <div class="max-w-md mx-auto bg-[#FAFAFA] border border-[#E2DDD5] p-6 text-left mb-8 font-azeret text-[10px] text-stone-600 uppercase tracking-wider space-y-2">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                <span>INSUFFICIENT TREASURY / BALANCE</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                <span>EXPIRED CREDENTIALS / OTP TIMEOUT</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                <span>GATEWAY INTERRUPTED</span>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <EcloreButton
                variant="primary"
                size="lg"
                class="w-full sm:w-auto"
                @click="router.push('/checkout')"
              >
                Reattempt Acquisition
              </EcloreButton>

              <EcloreButton
                variant="outline-gold"
                size="lg"
                class="w-full sm:w-auto"
                @click="router.push('/catalogue')"
              >
                Return to Collections
              </EcloreButton>
            </div>
          </div>
        </main>

        <EcloreFooter />
      </div>
    </ion-content>
  </ion-page>
</template>
