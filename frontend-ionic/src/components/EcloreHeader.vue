<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useWishlistStore } from '@/stores/wishlist.store';
import { useAuthStore } from '@/stores/auth.store';
import { useNotificationsStore } from '@/stores/notifications.store';
import SideMenuDrawer from '@/components/SideMenuDrawer.vue';
import AuthModal from '@/components/AuthModal.vue';
import NotificationsDrawer from '@/components/NotificationsDrawer.vue';

const isSideMenuOpen = ref(false);
const isAuthModalOpen = ref(false);
const isNotificationsOpen = ref(false);

const router = useRouter();
const wishlistStore = useWishlistStore();
const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();

const wishlistCount = computed(() => wishlistStore.itemCount);
const unreadNotificationsCount = computed(() => notificationsStore.unreadCount);

const emit = defineEmits<{
  (e: 'open-search'): void;
  (e: 'open-cart'): void;
  (e: 'open-auth'): void;
}>();

onMounted(() => {
  if (authStore.isAuthenticated) {
    notificationsStore.fetchUnreadCount();
  }
});

watch(
  () => authStore.isAuthenticated,
  (authed) => {
    if (authed) {
      notificationsStore.fetchUnreadCount(true);
    }
  }
);

function navigateTo(path: string) {
  router.push(path);
}

function handleWishlistClick() {
  wishlistStore.toggleWishlistDrawer();
}

function handleNotificationsClick() {
  if (!authStore.isAuthenticated) {
    authStore.setIntendedRoute('/tabs/home');
    isAuthModalOpen.value = true;
    return;
  }
  isNotificationsOpen.value = true;
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

      <!-- Right: Action Icons (Notifications & Wishlist) -->
      <div class="flex items-center space-x-4 sm:space-x-5">
        <!-- Notification Bell Icon / Drawer Trigger -->
        <button
          type="button"
          class="relative text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Notifications"
          @click="handleNotificationsClick"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
          </svg>
          <span
            v-if="unreadNotificationsCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#B6965D] text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center font-azeret animate-pulse"
          >
            {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
          </span>
        </button>

        <!-- Wishlist Icon / Drawer Trigger -->
        <button
          type="button"
          class="relative text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="Favorites"
          @click="handleWishlistClick"
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

        <!-- Account Profile Icon Trigger -->
        <button
          type="button"
          class="relative text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
          aria-label="My Account"
          @click="navigateTo('/tabs/account')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Side Menu Drawer -->
    <SideMenuDrawer
      :is-open="isSideMenuOpen"
      @close="isSideMenuOpen = false"
      @open-search="emit('open-search')"
    />

    <!-- Notifications Drawer -->
    <NotificationsDrawer
      :is-open="isNotificationsOpen"
      @close="isNotificationsOpen = false"
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
