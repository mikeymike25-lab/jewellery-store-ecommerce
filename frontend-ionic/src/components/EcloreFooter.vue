<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { apiClient } from '@/api/client';

const router = useRouter();
const newsletterEmail = ref('');
const subscribed = ref(false);
const subscribing = ref(false);
const subscriptionMessage = ref('');
const subscriptionError = ref('');

async function handleSubscribe(e?: Event) {
  if (e) {
    e.preventDefault();
  }
  if (subscribing.value) return;
  const cleanEmail = newsletterEmail.value.trim();
  if (!cleanEmail) {
    subscriptionError.value = 'Please enter your email address.';
    return;
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(cleanEmail)) {
    subscriptionError.value = 'Please enter a valid email address.';
    return;
  }

  subscribing.value = true;
  subscriptionError.value = '';
  subscribed.value = false;
  try {
    let res;
    try {
      res = await apiClient.post<{ success: boolean; message: string }>('/v1/newsletter/subscribe', {
        email: cleanEmail,
      });
    } catch (apiErr: any) {
      if (apiErr.response?.status === 404) {
        res = await apiClient.post<{ success: boolean; message: string }>('/newsletter/subscribe', {
          email: cleanEmail,
        });
      } else {
        throw apiErr;
      }
    }

    subscribed.value = true;
    subscriptionMessage.value = res.data?.message || 'Thank you for subscribing to the Éclore Gazette.';
    newsletterEmail.value = '';
    setTimeout(() => {
      subscribed.value = false;
    }, 7000);
  } catch (err: any) {
    subscriptionError.value = err.response?.data?.message || err.message || 'Could not enroll email. Please check your connection.';
  } finally {
    subscribing.value = false;
  }
}

function navigateTo(path: string) {
  router.push(path);
}
</script>

<template>
  <footer class="eclore-footer relative bg-[#FCFCFC] pt-20 pb-28 sm:pb-32 overflow-hidden border-t border-gray-100 text-gray-900">
    <!-- Background Watermark -->
    <div class="absolute bottom-0 w-full pl-40 text-left pointer-events-none z-0 opacity-[0.03] select-none">
      <h2 class="font-playfair text-[20vw] leading-none tracking-tight">Éclore</h2>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
      <!-- Brand Partners Bar (Matching Web View) -->
      <div class="flex flex-wrap justify-center items-center gap-x-8 sm:gap-x-12 gap-y-4 mb-16 font-azeret">
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">HERMES</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">CHANEL</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">GUCCI</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">BOTTEGA VENETA</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">PORSCHE</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">APPLE</span>
        <span class="text-xs text-gray-300">●</span>
        <span class="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-light">GIVENCHY</span>
      </div>

      <!-- Newsletter Subscription Form -->
      <div class="max-w-xl mx-auto mb-24 px-4">
        <form @submit.prevent="handleSubscribe" class="relative">
          <div class="flex items-center justify-between border-b-2 border-black pb-2 gap-3">
            <input
              v-model="newsletterEmail"
              type="email"
              placeholder="EMAIL ADDRESS FOR LA GAZETTE"
              class="bg-transparent flex-1 min-w-0 text-xs tracking-wide font-azeret focus:outline-none placeholder:text-gray-400 py-2 text-black pr-2"
              required
            />
            <button
              type="submit"
              :disabled="subscribing"
              class="bg-black text-white hover:bg-stone-900 py-2.5 min-h-[40px] text-[10px] sm:text-[11px] tracking-[0.15em] uppercase font-azeret font-bold inline-flex items-center justify-center gap-2 transition-all flex-shrink-0 shadow-sm active:scale-95 disabled:opacity-50 cursor-pointer"
              style="padding-left: 16px !important; padding-right: 16px !important;"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
              <span class="whitespace-nowrap">{{ subscribing ? 'JOINING...' : 'SUBSCRIBE' }}</span>
            </button>
          </div>
          <!-- In-flow status message so it is always visible on mobile -->
          <div
            v-if="subscribed"
            class="mt-3 p-3 bg-[#FAF8F5] border border-[#B6965D] text-[11px] tracking-wider text-[#B6965D] uppercase font-azeret font-semibold flex items-center gap-2 animate-fadeIn"
          >
            <svg class="w-4 h-4 text-[#B6965D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ subscriptionMessage }}</span>
          </div>
          <div
            v-else-if="subscriptionError"
            class="mt-3 p-3 bg-red-50 border border-red-200 text-[11px] tracking-wider text-red-700 uppercase font-azeret font-semibold flex items-center gap-2 animate-fadeIn"
          >
            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ subscriptionError }}</span>
          </div>
        </form>
      </div>

      <!-- Footer Columns Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-20 text-left">
        <!-- Column 1: Customer -->
        <div>
          <h3 class="font-playfair text-2xl mb-7 text-gray-900">Customer</h3>
          <ul class="space-y-4 text-[10px] tracking-[0.2em] font-azeret text-gray-600 uppercase">
            <li><button type="button" @click="navigateTo('/contact')" class="hover:text-gray-900 transition-colors">CONTACT US</button></li>
            <li><button type="button" @click="navigateTo('/track-order')" class="hover:text-gray-900 transition-colors">TRACK YOUR ORDER</button></li>
            <li><button type="button" @click="navigateTo('/orders-payments')" class="hover:text-gray-900 transition-colors">ORDERS & PAYMENT</button></li>
            <li><button type="button" @click="navigateTo('/accessibility')" class="hover:text-gray-900 transition-colors">ACCESSIBILITY</button></li>
            <li><button type="button" @click="navigateTo('/help')" class="hover:text-gray-900 transition-colors">HELP & FAQ</button></li>
          </ul>
        </div>

        <!-- Column 2: Our Company -->
        <div>
          <h3 class="font-playfair text-2xl mb-7 text-gray-900">Our Company</h3>
          <ul class="space-y-4 text-[10px] tracking-[0.2em] font-azeret text-gray-600 uppercase">
            <li><button type="button" @click="navigateTo('/boutiques')" class="hover:text-gray-900 transition-colors">FIND A BOUTIQUE</button></li>
            <li><button type="button" @click="navigateTo('/appointments')" class="hover:text-gray-900 transition-colors">SALON APPOINTMENTS</button></li>
            <li><button type="button" @click="navigateTo('/vip-club')" class="hover:text-gray-900 transition-colors text-[#B6965D]">VIP PRIVATE CIRCLE</button></li>
            <li><button type="button" @click="navigateTo('/journal')" class="hover:text-gray-900 transition-colors">JOURNAL & SAVOIR-FAIRE</button></li>
            <li><button type="button" @click="navigateTo('/sustainability')" class="hover:text-gray-900 transition-colors">CORPORATE RESPONSIBILITY</button></li>
            <li><button type="button" @click="navigateTo('/care-guide')" class="hover:text-gray-900 transition-colors">CARE & RESTORATION</button></li>
          </ul>
        </div>

        <!-- Column 3: Legal and Privacy -->
        <div>
          <h3 class="font-playfair text-2xl mb-7 text-gray-900">Legal And Privacy</h3>
          <ul class="space-y-4 text-[10px] tracking-[0.2em] font-azeret text-gray-600 uppercase">
            <li><button type="button" @click="navigateTo('/terms')" class="hover:text-gray-900 transition-colors">TERMS OF SERVICE</button></li>
            <li><button type="button" @click="navigateTo('/conditions-of-sale')" class="hover:text-gray-900 transition-colors">CONDITIONS OF SALE</button></li>
            <li><button type="button" @click="navigateTo('/privacy')" class="hover:text-gray-900 transition-colors">PRIVACY POLICY</button></li>
            <li><button type="button" @click="navigateTo('/cookie-policy')" class="hover:text-gray-900 transition-colors">COOKIE POLICY</button></li>
            <li><button type="button" @click="navigateTo('/terms')" class="hover:text-gray-900 transition-colors">COOKIE CENTER</button></li>
          </ul>
        </div>

        <!-- Column 4: Follow Us -->
        <div>
          <h3 class="font-playfair text-2xl mb-7 text-gray-900">Follow Us</h3>
          <div class="flex gap-4">
            <!-- Facebook -->
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full border border-black flex items-center justify-center text-black bg-white hover:bg-black hover:text-white transition-all shadow-xs" aria-label="Facebook">
              <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <!-- Instagram -->
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full border border-black flex items-center justify-center text-black bg-white hover:bg-black hover:text-white transition-all shadow-xs" aria-label="Instagram">
              <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.344 3.608 1.32.975.975 1.258 2.242 1.32 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.344 2.633-1.32 3.608-.975.975-2.242 1.258-3.608 1.32-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.344-3.608-1.32-.975-.975-1.258-2.242-1.32-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.344-2.633 1.32-3.608.975-.975 2.242-1.258 3.608-1.32 1.266-.058 1.646-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.547.054-3.411.156-5.32 2.063-5.476 5.476-.04 0-.054.408-.054 4.547s.014 3.667.054 4.547c.156 3.411 2.063 5.32 5.476 5.476.88.04 1.288.054 4.547.054s3.667-.014 4.547-.054c3.411-.156 5.32-2.063 5.476-5.476.04-.88.054-1.288.054-4.547s-.014-3.667-.054-4.547c-.156-3.411-2.063-5.32-5.476-5.476-.88-.04-1.288-.054-4.547-.054zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.162 6.162 6.162 6.162-2.759 6.162-6.162-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <!-- LinkedIn -->
            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full border border-black flex items-center justify-center text-black bg-white hover:bg-black hover:text-white transition-all shadow-xs" aria-label="LinkedIn">
              <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
            </a>
            <!-- X (Twitter) -->
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full border border-black flex items-center justify-center text-black bg-white hover:bg-black hover:text-white transition-all shadow-xs" aria-label="X">
              <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Copyright Bar -->
      <div class="pt-10 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 text-[9px] tracking-[0.2em] font-azeret text-gray-400 uppercase">
        <p>© 2026 ÉCLORE MAISON. ALL RIGHTS RESERVED.</p>
        <div class="flex gap-8">
          <span>ETHICALLY SOURCED GEMS</span>
          <span>RJC CERTIFIED FOUNDRY</span>
          <span>12% BIR VAT OFFICIAL RECEIPT</span>
        </div>
      </div>
    </div>
  </footer>
</template>
