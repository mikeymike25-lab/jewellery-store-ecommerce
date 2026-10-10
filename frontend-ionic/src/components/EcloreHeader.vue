<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useAuthStore } from '@/stores/auth.store';
import SideMenuDrawer from '@/components/SideMenuDrawer.vue';
import AuthModal from '@/components/AuthModal.vue';

const isSideMenuOpen = ref(false);
const isAuthModalOpen = ref(false);

const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const authStore = useAuthStore();

const cartCount = computed(() => cartStore.itemCount);
const wishlistCount = computed(() => wishlistStore.itemCount);

const emit = defineEmits<{
  (e: 'open-search'): void;
  (e: 'open-cart'): void;
  (e: 'open-auth'): void;
}>();

function navigateTo(path: string) {
  router.push(path);
}
</script>

<template>
  <header class="eclore-header sticky top-0 z-40 bg-[#F3EFE7]/95 backdrop-blur-md border-b border-[#E2DDD5] transition-all">
    <!-- Main Navigation Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Left: Mobile Menu Toggle / Search / Desktop Links -->
      <div class="flex items-center space-x-4 sm:space-x-6">
        <!-- Mobile Hamburger Toggle -->
        <button
          type="button"
          class="md:hidden text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Open menu"
          @click="isSideMenuOpen = true"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Search Button -->
        <button
          type="button"
          class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Search collection"
          @click="emit('open-search')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </button>

        <nav class="hidden md:flex space-x-6 text-xs uppercase tracking-widest font-azeret">
          <router-link to="/catalogue" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors">Catalogue</router-link>
          <router-link to="/catalogue?category=rings" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors">Rings</router-link>
          <router-link to="/catalogue?category=necklaces" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors">Necklaces</router-link>
          <router-link to="/catalogue?category=bracelets" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors">Bracelets</router-link>
          <router-link to="/catalogue?category=earrings" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors">Earrings</router-link>
        </nav>
      </div>

      <!-- Center: Brand Logo -->
      <div class="flex-shrink-0 text-center cursor-pointer" @click="navigateTo('/')">
        <router-link to="/" class="inline-flex items-center">
          <img src="/assets/company-logo.png" alt="Éclore Jewellery" class="h-8 w-auto object-contain hidden sm:block" onerror="this.style.display='none'" />
          <span class="font-playfair text-xl sm:text-2xl tracking-[0.25em] font-normal text-[#1A1A1A] uppercase ml-1 sm:ml-2">
            Éclore
          </span>
        </router-link>
      </div>

      <!-- Right: Action Icons (Account, Wishlist, Bag) -->
      <div class="flex items-center space-x-4 sm:space-x-5">
        <!-- Account Icon -->
        <button
          type="button"
          class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Account"
          @click="authStore.isAuthenticated ? navigateTo('/account') : (isAuthModalOpen = true)"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </button>

        <!-- Wishlist Icon / Drawer Trigger -->
        <button
          type="button"
          class="relative text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Favorites"
          @click="wishlistStore.toggleWishlistDrawer()"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
          </svg>
          <span
            v-if="wishlistCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#B6965D] text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center font-azeret"
          >
            {{ wishlistCount }}
          </span>
        </button>

        <!-- Shopping Bag Trigger -->
        <button
          type="button"
          class="relative text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Shopping bag"
          @click="cartStore.toggleCart()"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.67 0-1.19-.578-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119.993z" />
          </svg>
          <span
            v-if="cartCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#B6965D] text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center font-azeret"
          >
            {{ cartCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Side Menu Drawer -->
    <SideMenuDrawer
      :is-open="isSideMenuOpen"
      @close="isSideMenuOpen = false"
      @open-search="emit('open-search')"
    />

    <!-- Luxury Auth Modal (Sign In / Sign Up / Forgot Password) -->
    <AuthModal
      :is-open="isAuthModalOpen"
      @close="isAuthModalOpen = false"
      @success="isAuthModalOpen = false"
    />
  </header>
</template>

<style scoped>
.eclore-header {
  padding-top: env(safe-area-inset-top, 0px);
}
</style>
