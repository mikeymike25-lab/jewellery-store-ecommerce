<script setup lang="ts">
import { onMounted } from 'vue';
import { IonApp, IonRouterOutlet } from '@ionic/vue';
import { Capacitor } from '@capacitor/core';
import { StatusBar, Style } from '@capacitor/status-bar';
import { useAuthStore } from '@/stores/auth.store';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import CartDrawer from '@/components/CartDrawer.vue';
import WishlistDrawer from '@/components/WishlistDrawer.vue';
import NoupeConciergeModal from '@/components/NoupeConciergeModal.vue';
import EcloreBottomNav from '@/components/EcloreBottomNav.vue';
import AuthModal from '@/components/AuthModal.vue';

const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

onMounted(async () => {
  if (Capacitor.isNativePlatform()) {
    try {
      await StatusBar.setStyle({ style: Style.Light });
      await StatusBar.setBackgroundColor({ color: '#F3EFE7' });
      await StatusBar.setOverlaysWebView({ overlay: false });
    } catch (err) {
      console.warn('StatusBar initialization notice:', err);
    }
  }

  await authStore.init();
  await Promise.allSettled([
    cartStore.fetchCart(),
    wishlistStore.fetchWishlist(),
  ]);
});
</script>

<template>
  <ion-app>
    <ion-router-outlet :animated="false" />
    <!-- Global Persistent Bottom Navigation -->
    <EcloreBottomNav />
    <!-- Global Sliding Drawers & AI Concierge -->
    <CartDrawer />
    <WishlistDrawer />
    <NoupeConciergeModal />
    <!-- Global Authentication Modal -->
    <AuthModal
      :is-open="authStore.isAuthModalOpen"
      :reason="authStore.authModalReason"
      :initial-mode="authStore.authModalMode"
      @close="authStore.closeAuthModal()"
      @success="authStore.closeAuthModal()"
    />
  </ion-app>
</template>
