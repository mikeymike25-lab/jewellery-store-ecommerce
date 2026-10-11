<script setup lang="ts">
import { computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationsStore, type NotificationItem } from '@/stores/notifications.store';
import { useAuthStore } from '@/stores/auth.store';

interface Props {
  isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'count-updated', count: number): void;
}>();

const router = useRouter();
const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();

const notifications = computed(() => notificationsStore.notifications);
const loading = computed(() => notificationsStore.loading);

async function handleNotificationClick(notification: NotificationItem) {
  await markAsRead(notification);
  if (notification.type === 'appointment' || notification.data?.type === 'appointment') {
    emit('close');
    router.push('/appointments');
  } else if (notification.data?.order_number) {
    emit('close');
    router.push('/account');
  }
}

async function markAsRead(notification: NotificationItem) {
  await notificationsStore.markAsRead(notification);
  emit('count-updated', notificationsStore.unreadCount);
}

async function markAllAsRead() {
  await notificationsStore.markAllAsRead();
  emit('count-updated', 0);
}

async function clearAll() {
  await notificationsStore.clearAll();
  emit('count-updated', 0);
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '';
  try {
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    if (diffHours < 1) return 'Just now';
    if (diffHours < 24) return `${diffHours}h ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  } catch {
    return dateStr;
  }
}

watch(
  () => props.isOpen,
  (open) => {
    if (open && authStore.isAuthenticated) {
      notificationsStore.fetchNotifications().then(() => {
        emit('count-updated', notificationsStore.unreadCount);
      });
    }
  }
);
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50"
        @click="emit('close')"
      />
    </Transition>

    <!-- Sliding Notifications Panel (Slide in from Right) -->
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <aside
        v-if="isOpen"
        class="fixed top-0 right-0 bottom-0 w-[90vw] max-w-md bg-[#F8F7F2] z-50 flex flex-col shadow-2xl border-l border-[#E0E0D9] overflow-hidden"
        style="padding-top: max(env(safe-area-inset-top, 0px), 0px);"
        role="dialog"
        aria-modal="true"
        aria-labelledby="notificationsDrawerTitle"
      >
        <!-- Header -->
        <header class="flex items-center justify-between px-6 py-5 border-b border-[#E0E0D9] bg-[#F8F7F2] shrink-0">
          <div class="flex items-baseline space-x-2">
            <h2 id="notificationsDrawerTitle" class="font-playfair text-xl text-[#1A1A1A] font-semibold tracking-wide">
              Notifications
            </h2>
            <span
              v-if="notifications.filter((n) => n.status !== 'read').length > 0"
              class="font-azeret text-[10px] text-[#B6965D] uppercase tracking-widest font-bold"
            >
              ({{ notifications.filter((n) => n.status !== 'read').length }} Unread)
            </span>
          </div>

          <button
            type="button"
            class="w-8 h-8 rounded-full border border-stone-300 hover:border-stone-500 hover:bg-stone-200/50 flex items-center justify-center text-stone-600 hover:text-black transition-all cursor-pointer"
            aria-label="Close notifications"
            @click="emit('close')"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </header>

        <!-- Action Toolbar -->
        <div
          v-if="notifications.length > 0"
          class="px-6 py-2.5 bg-[#EFECE6]/60 border-b border-[#E0E0D9] flex items-center justify-between font-azeret text-[10px] uppercase tracking-wider text-[#767676] shrink-0"
        >
          <button
            type="button"
            class="hover:text-[#B6965D] transition-colors cursor-pointer"
            @click="markAllAsRead"
          >
            Mark All As Read
          </button>
          <button
            type="button"
            class="hover:text-red-700 transition-colors cursor-pointer"
            @click="clearAll"
          >
            Clear All
          </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
          <!-- Loading State -->
          <div v-if="loading" class="py-12 space-y-3">
            <div v-for="i in 3" :key="i" class="animate-pulse p-4 bg-white border border-[#E0E0D9] rounded">
              <div class="h-3 bg-stone-200 w-1/3 rounded mb-2"></div>
              <div class="h-2.5 bg-stone-200 w-full rounded"></div>
            </div>
          </div>

          <!-- Not Logged In State -->
          <div v-else-if="!authStore.isAuthenticated" class="py-16 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-[#EFECE6] border border-[#E0E0D9] flex items-center justify-center text-[#B6965D]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
            </div>
            <p class="font-playfair text-lg text-stone-800">Sign in to View Alerts</p>
            <p class="font-azeret text-xs text-stone-500 max-w-xs mx-auto">
              Access real-time shipping milestones, boutique appointment reminders, and private concierge communiqués.
            </p>
            <div class="pt-2">
              <button
                type="button"
                class="px-5 py-2.5 rounded-full bg-[#B6965D] hover:bg-[#9A7E4D] text-white font-azeret text-[10px] uppercase tracking-widest font-bold active:scale-95 transition-all shadow-sm cursor-pointer"
                @click="emit('close'); authStore.openAuthModal('Sign in to view your boutique notifications and appointments.')"
              >
                Sign In
              </button>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="notifications.length === 0" class="py-16 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-[#EFECE6] border border-[#E0E0D9] flex items-center justify-center text-[#B6965D]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
            </div>
            <p class="font-playfair text-lg text-stone-800">No Notifications</p>
            <p class="font-azeret text-xs text-stone-500 max-w-xs mx-auto">
              You are fully up to date with all atelier dispatch notices and salon bookings.
            </p>
          </div>

          <!-- Notification List -->
          <div v-else class="space-y-3">
            <article
              v-for="item in notifications"
              :key="item.id"
              class="p-4 bg-white border border-[#E0E0D9] transition-all cursor-pointer relative hover:border-[#B6965D]"
              :class="item.status !== 'read' ? 'border-l-4 border-l-[#B6965D]' : 'opacity-85'"
              @click="handleNotificationClick(item)"
            >
              <div class="flex items-start justify-between gap-2 mb-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span
                    v-if="item.type === 'appointment' || item.data?.type === 'appointment'"
                    class="font-azeret text-[9px] uppercase tracking-wider px-1.5 py-0.5 bg-[#B6965D]/10 text-[#B6965D] font-bold rounded"
                  >
                    Salon Appointment
                  </span>
                  <h4 class="font-playfair text-sm text-[#1A1A1A] font-semibold">
                    {{ item.title }}
                  </h4>
                </div>
                <span class="font-azeret text-[10px] text-stone-400 whitespace-nowrap shrink-0">
                  {{ formatDate(item.created_at) }}
                </span>
              </div>
              <p class="font-azeret text-xs text-stone-600 leading-relaxed">
                {{ item.message }}
              </p>
            </article>
          </div>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>
