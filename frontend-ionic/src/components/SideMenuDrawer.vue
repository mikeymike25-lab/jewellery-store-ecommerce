<script setup lang="ts">
import { useRouter } from 'vue-router';

interface Props {
  isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'open-search'): void;
}>();

const router = useRouter();

function handleNavigate(path: string) {
  emit('close');
  router.push(path);
}

function handleSearch() {
  emit('close');
  emit('open-search');
}
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
        v-if="props.isOpen"
        class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50"
        @click="emit('close')"
      />
    </Transition>

    <!-- Side Panel Drawer (Slide in from Left) -->
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <aside
        v-if="props.isOpen"
        class="fixed top-0 left-0 bottom-0 w-[82vw] max-w-sm bg-white z-50 flex flex-col shadow-2xl overflow-y-auto"
        style="padding-top: max(env(safe-area-inset-top, 0px), 16px);"
      >
        <!-- Drawer Top Bar -->
        <div class="px-6 h-16 flex items-center justify-between border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center space-x-5">
            <!-- Close / Hamburger Toggle Button -->
            <button
              type="button"
              class="text-gray-900 hover:text-[#B6965D] transition-colors p-1"
              aria-label="Close menu"
              @click="emit('close')"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Search Icon -->
            <button
              type="button"
              class="text-gray-900 hover:text-[#B6965D] transition-colors p-1"
              aria-label="Search collection"
              @click="handleSearch"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
              </svg>
            </button>
          </div>

          <!-- Brand Logo -->
          <span
            class="font-playfair text-xl tracking-[0.25em] text-gray-900 uppercase font-light cursor-pointer"
            @click="handleNavigate('/')"
          >
            Éclore
          </span>
        </div>

        <!-- Navigation Links (Matching 1:1 with Web Side Panel) -->
        <nav class="px-8 py-10 flex-grow space-y-7 font-azeret text-[13px] tracking-[0.22em] uppercase font-light">
          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2 flex items-center justify-between"
              @click="handleNavigate('/tabs/account')"
            >
              <span>My Account</span>
              <svg class="w-4 h-4 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/catalogue')"
            >
              Catalogue
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/catalogue')"
            >
              Collections
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/journal')"
            >
              About Éclore
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/appointments')"
            >
              Boutiques & Appointments
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/journal')"
            >
              Newsroom
            </button>
          </div>

          <div>
            <!-- VIP Club in Signature Éclore Champagne Gold -->
            <button
              type="button"
              class="w-full text-left text-[#B6965D] hover:text-[#9A7E4D] font-medium transition-colors py-2"
              @click="handleNavigate('/vip-club')"
            >
              VIP Club
            </button>
          </div>

          <div>
            <button
              type="button"
              class="w-full text-left text-gray-900 hover:text-[#B6965D] transition-colors py-2"
              @click="handleNavigate('/track-order')"
            >
              Track Acquisition
            </button>
          </div>
        </nav>

        <!-- Drawer Footer Info -->
        <div class="px-8 py-8 border-t border-gray-100 flex-shrink-0 text-[10px] font-azeret text-gray-400 tracking-widest uppercase">
          <p>© 2026 ÉCLORE MAISON</p>
          <p class="text-[9px] text-[#B6965D] mt-1">HAUTE JOAILLERIE &bull; MANILA</p>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>
