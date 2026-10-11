<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useIonRouter } from '@ionic/vue';
import { useCartStore } from '@/stores/cart.store';

const route = useRoute();
const ionRouter = useIonRouter();
const cartStore = useCartStore();

const cartCount = computed(() => cartStore.itemCount);

function isRouteActive(target: string): boolean {
  const current = route?.path || '';
  if (target === 'home') {
    return current === '/' || current === '/tabs/home';
  }
  if (target === 'catalogue') {
    return current.startsWith('/tabs/catalogue') || current.startsWith('/catalogue') || current.startsWith('/products/');
  }
  if (target === 'coffret') {
    return current.startsWith('/tabs/coffret') || current.startsWith('/cart') || current.startsWith('/basket');
  }
  if (target === 'account') {
    return current.startsWith('/tabs/account') || current.startsWith('/account') || current.startsWith('/track-order');
  }
  return false;
}

function navigateTo(path: string) {
  // Navigate with 'none' direction to eliminate any page slide transition animations
  ionRouter.navigate(path, 'none', 'replace');
}
</script>

<template>
  <nav
    class="fixed bottom-0 inset-x-0 z-40 bg-[#F3EFE7]/95 backdrop-blur-md border-t border-[#E2DDD5] shadow-[0_-4px_25px_rgba(0,0,0,0.06)] pb-[env(safe-area-inset-bottom,0px)]"
    aria-label="Main Navigation"
  >
    <div class="max-w-md mx-auto md:max-w-xl px-4">
      <div class="h-16 flex items-center justify-around">
        <!-- 1. Home -->
        <button
          type="button"
          class="relative flex flex-col items-center justify-center py-1 px-4 min-w-[68px] transition-colors group focus:outline-none"
          :class="isRouteActive('home') ? 'text-[#B6965D]' : 'text-stone-500 hover:text-stone-900'"
          @click="navigateTo('/tabs/home')"
        >
          <div
            v-if="isRouteActive('home')"
            class="absolute -top-2 w-7 h-0.5 bg-[#B6965D] rounded-full"
          ></div>
          <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>
          <span class="font-azeret text-[10px] tracking-wider uppercase font-medium">Home</span>
        </button>

        <!-- 2. Atelier / Catalogue -->
        <button
          type="button"
          class="relative flex flex-col items-center justify-center py-1 px-4 min-w-[68px] transition-colors group focus:outline-none"
          :class="isRouteActive('catalogue') ? 'text-[#B6965D]' : 'text-stone-500 hover:text-stone-900'"
          @click="navigateTo('/tabs/catalogue')"
        >
          <div
            v-if="isRouteActive('catalogue')"
            class="absolute -top-2 w-7 h-0.5 bg-[#B6965D] rounded-full"
          ></div>
          <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
          </svg>
          <span class="font-azeret text-[10px] tracking-wider uppercase font-medium">Atelier</span>
        </button>

        <!-- 3. Coffret (Shopping Bag) -->
        <button
          type="button"
          class="relative flex flex-col items-center justify-center py-1 px-4 min-w-[68px] transition-colors group focus:outline-none"
          :class="isRouteActive('coffret') ? 'text-[#B6965D]' : 'text-stone-500 hover:text-stone-900'"
          @click="navigateTo('/tabs/coffret')"
        >
          <div
            v-if="isRouteActive('coffret')"
            class="absolute -top-2 w-7 h-0.5 bg-[#B6965D] rounded-full"
          ></div>
          <div class="relative">
            <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.67 0-1.19-.578-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
            </svg>
            <span
              v-if="cartCount > 0"
              class="absolute -top-1.5 -right-2 bg-[#B6965D] text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center font-azeret shadow-sm leading-none"
            >
              {{ cartCount }}
            </span>
          </div>
          <span class="font-azeret text-[10px] tracking-wider uppercase font-medium">Coffret</span>
        </button>

        <!-- 4. Account -->
        <button
          type="button"
          class="relative flex flex-col items-center justify-center py-1 px-4 min-w-[68px] transition-colors group focus:outline-none"
          :class="isRouteActive('account') ? 'text-[#B6965D]' : 'text-stone-500 hover:text-stone-900'"
          @click="navigateTo('/tabs/account')"
        >
          <div
            v-if="isRouteActive('account')"
            class="absolute -top-2 w-7 h-0.5 bg-[#B6965D] rounded-full"
          ></div>
          <svg class="w-5 h-5 mb-1 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
          <span class="font-azeret text-[10px] tracking-wider uppercase font-medium">Account</span>
        </button>
      </div>
    </div>
  </nav>
</template>
