<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import { apiClient } from '@/api/client';

interface ChatProduct {
  id: number;
  name: string;
  slug: string;
  price: number;
  price_formatted: string;
  material?: string;
  gemstone?: string;
  image?: string;
}

interface ChatMessage {
  id: number;
  sender: 'claire' | 'user';
  text: string;
  product?: ChatProduct | null;
  action?: {
    type: string;
    text: string;
    route: string;
  } | null;
  suggestions?: string[];
  time: string;
}

const router = useRouter();
const route = useRoute();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

// Hide when in checkout or when sidebars/modals are active
const isBlockedRoute = computed(() => {
  return (
    route?.path?.startsWith('/checkout') ||
    cartStore.isOpen ||
    wishlistStore.isOpen
  );
});

const isOpen = ref(false);
const inputMessage = ref('');
const isSending = ref(false);
const showQuickPrompts = ref(true);
const showTermsModal = ref(false);
const chatContainer = ref<HTMLElement | null>(null);

const defaultWelcomeMessage: ChatMessage = {
  id: 1,
  sender: 'claire',
  text: "Hi there! 👋 I'm **Claire**! I'm here to help. I can provide you with information on Éclore Jewellery, an exclusive jewellery shop of timeless elegance and their limited edition luxury jewellery. How can I assist you today?",
  suggestions: [
    '💍 Ring sizing & dimensions',
    '✨ Atelier bestsellers & prices',
    '🏛️ Book private salon viewing',
    '📦 Track my consignment',
  ],
  time: 'Claire • Atelier',
};

const messages = ref<ChatMessage[]>([defaultWelcomeMessage]);

const initialQuickPrompts = [
  '💍 Solitaire sizing & ring dimensions',
  '✨ Atelier bestsellers & prices',
  '🏛️ Book private salon viewing',
  '📦 Track my consignment',
  '💎 Care advice for emeralds & diamonds',
];

const STORAGE_KEY = 'eclore_claire_chat_messages';

function loadStoredMessages() {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) {
        messages.value = parsed;
        return;
      }
    }
  } catch {
    // Ignore JSON errors
  }
  messages.value = [defaultWelcomeMessage];
}

function persistMessages() {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(messages.value));
  } catch {
    // Ignore storage quota
  }
}

function openChat() {
  isOpen.value = true;
  scrollToBottom();
}

function closeChat() {
  isOpen.value = false;
}

function resetChat() {
  messages.value = [
    {
      ...defaultWelcomeMessage,
      id: Date.now(),
    },
  ];
  persistMessages();
  scrollToBottom();
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
}

function formatMessageText(text: string): string {
  // Parse simple markdown: **bold**, *italic*, newlines, bullets
  let html = text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br/>');
  return html;
}

async function sendMessage(textToSend?: string) {
  const content = (textToSend || inputMessage.value).trim();
  if (!content || isSending.value) return;

  const now = new Date();
  const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

  // Add user message
  messages.value.push({
    id: Date.now(),
    sender: 'user',
    text: content,
    time: timeStr,
  });

  inputMessage.value = '';
  isSending.value = true;
  persistMessages();
  scrollToBottom();

  try {
    const historyPayload = messages.value
      .filter((m) => m.sender === 'user' || m.sender === 'claire')
      .slice(-8)
      .map((m) => ({
        role: m.sender === 'user' ? 'user' : 'model',
        text: m.text,
      }));

    const res = await apiClient.post<{
      success: boolean;
      reply: string;
      product?: ChatProduct | null;
      action?: { type: string; text: string; route: string } | null;
      suggestions?: string[];
    }>('/v1/concierge/chat', {
      message: content,
      history: historyPayload,
    });

    const reply = res.data?.reply || 'Our master advisors are at your service for all bespoke inquiries.';
    const product = res.data?.product || null;
    const action = res.data?.action || null;
    const suggestions = res.data?.suggestions || [];

    messages.value.push({
      id: Date.now() + 1,
      sender: 'claire',
      text: reply,
      product,
      action,
      suggestions,
      time: 'Claire',
    });
  } catch (err) {
    // Intelligent fallback in case of network issue
    messages.value.push({
      id: Date.now() + 1,
      sender: 'claire',
      text: "Maison Éclore curates handcrafted Haute Joaillerie masterpieces. How may I guide you further with rings, sizing, bespoke commissions, or boutique appointments?",
      action: { type: 'link', text: 'Discover Atelier Catalogue', route: '/tabs/catalogue' },
      suggestions: ['View Rings', 'Ring Sizing Guide', 'Book Private Salon'],
      time: 'Claire',
    });
  } finally {
    isSending.value = false;
    persistMessages();
    scrollToBottom();
  }
}

function handleNavigate(routePath: string) {
  isOpen.value = false;
  router.push(routePath);
}

onMounted(() => {
  loadStoredMessages();
});
</script>

<template>
  <div class="eclore-concierge-root">
    <!-- ========================================================= -->
    <!-- 1. FLOATING MASCOT LAUNCHER (Placed strictly above tab nav)-->
    <!-- ========================================================= -->
    <transition name="mascot-fade">
      <button
        v-if="!isOpen && !isBlockedRoute"
        type="button"
        class="fixed z-40 group cursor-pointer focus:outline-none transition-all duration-300 hover:scale-105 active:scale-95"
        :class="[
          'bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] md:bottom-8',
          'right-4 md:right-7',
        ]"
        aria-label="Chat with Claire, Éclore Atelier Concierge"
        @click="openChat"
      >
        <!-- Mascot Character Container (Light brown theme matching uploaded design) -->
        <div class="relative flex flex-col items-center">
          <!-- Ambient Glow Effect -->
          <div class="absolute inset-0 bg-[#B6965D]/35 blur-lg rounded-2xl scale-95 group-hover:scale-110 transition-transform duration-500"></div>

          <!-- SVG Character: Light Brown Mascot with Rounded Ears, Big Eyes, and Smile -->
          <div class="relative w-14 h-14 sm:w-16 sm:h-16 drop-shadow-xl select-none transform transition-transform group-hover:-translate-y-1">
            <svg viewBox="0 0 100 95" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full filter drop-shadow-md">
              <defs>
                <linearGradient id="mascotBrownGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#C5A86E" />
                  <stop offset="45%" stop-color="#B6965D" />
                  <stop offset="100%" stop-color="#9E7E46" />
                </linearGradient>
                <radialGradient id="mascotCheek" cx="50%" cy="50%" r="50%">
                  <stop offset="0%" stop-color="#E2CCA4" stop-opacity="0.6" />
                  <stop offset="100%" stop-color="#B6965D" stop-opacity="0" />
                </radialGradient>
              </defs>

              <!-- Mascot Body Shape with Cute Top Rounded Ears -->
              <path
                d="M 24 92
                   C 10 92 6 82 6 68
                   L 6 36
                   C 6 22 16 16 28 16
                   C 33 16 38 10 44 8
                   C 48 6 52 6 56 8
                   C 62 10 67 16 72 16
                   C 84 16 94 22 94 36
                   L 94 68
                   C 94 82 90 92 76 92
                   Z"
                fill="url(#mascotBrownGradient)"
              />

              <!-- Left Ear Bump Highlight -->
              <path
                d="M 32 16 C 36 12 40 8 45 8 C 48 8 50 11 50 14"
                stroke="#E5D1AC"
                stroke-width="2.5"
                stroke-linecap="round"
                opacity="0.6"
              />

              <!-- Left Eye (Big Rounded Capsule with Specular Shine) -->
              <ellipse cx="37" cy="50" rx="9" ry="12" fill="#FFFFFF" />
              <ellipse cx="38" cy="50" rx="5.5" ry="9" fill="#1A1A1A" />
              <circle cx="36" cy="46" r="2.8" fill="#FFFFFF" />
              <circle cx="40.5" cy="53" r="1.2" fill="#FFFFFF" />

              <!-- Right Eye (Big Rounded Capsule with Specular Shine) -->
              <ellipse cx="63" cy="50" rx="9" ry="12" fill="#FFFFFF" />
              <ellipse cx="64" cy="50" rx="5.5" ry="9" fill="#1A1A1A" />
              <circle cx="62" cy="46" r="2.8" fill="#FFFFFF" />
              <circle cx="66.5" cy="53" r="1.2" fill="#FFFFFF" />

              <!-- Soft Cheeks -->
              <circle cx="24" cy="62" r="5" fill="url(#mascotCheek)" />
              <circle cx="76" cy="62" r="5" fill="url(#mascotCheek)" />

              <!-- Cute Smiling Curved Mouth -->
              <path
                d="M 40 65 Q 50 75 60 65"
                stroke="#1A1A1A"
                stroke-width="4.2"
                stroke-linecap="round"
                fill="none"
              />
            </svg>
          </div>

          <!-- Pulsing Status Dot -->
          <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#B6965D] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white shadow-xs"></span>
          </span>
        </div>
      </button>
    </transition>

    <!-- ========================================================= -->
    <!-- 2. NATIVE CHAT MODAL DIALOG (Light Brown Theme, No Iframe)-->
    <!-- ========================================================= -->
    <transition name="chat-modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/45 backdrop-blur-[2px]"
        role="dialog"
        aria-modal="true"
        aria-label="Claire Concierge Chat"
        @click.self="closeChat"
      >
        <!-- Chat Window Card -->
        <div
          class="relative w-full sm:max-w-[420px] bg-[#FFFFFF] shadow-2xl flex flex-col overflow-hidden transition-all duration-300"
          :class="[
            'rounded-t-[26px] sm:rounded-[24px]',
            'h-[86vh] sm:h-[620px] max-h-[92vh]',
            'border border-[#E5E0D5]',
            'mb-[env(safe-area-inset-bottom,0px)]',
          ]"
        >
          <!-- ---------------------------------------------------- -->
          <!-- HEADER: Single Light Brown Bar with Mascot & Close   -->
          <!-- ---------------------------------------------------- -->
          <header class="bg-gradient-to-r from-[#B6965D] via-[#A8874E] to-[#997943] text-white px-4 py-3 flex items-center justify-between shrink-0 shadow-sm relative">
            <!-- Left Side: Mascot Avatar matching the Launcher icon 100% -->
            <div class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center relative shrink-0">
                <svg viewBox="0 0 100 95" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full filter drop-shadow-sm">
                  <defs>
                    <linearGradient id="headerMascotGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#E2CCA4" />
                      <stop offset="45%" stop-color="#C5A86E" />
                      <stop offset="100%" stop-color="#B6965D" />
                    </linearGradient>
                    <radialGradient id="headerMascotCheek" cx="50%" cy="50%" r="50%">
                      <stop offset="0%" stop-color="#F2DEC2" stop-opacity="0.8" />
                      <stop offset="100%" stop-color="#B6965D" stop-opacity="0" />
                    </radialGradient>
                  </defs>

                  <path
                    d="M 24 92
                       C 10 92 6 82 6 68
                       L 6 36
                       C 6 22 16 16 28 16
                       C 33 16 38 10 44 8
                       C 48 6 52 6 56 8
                       C 62 10 67 16 72 16
                       C 84 16 94 22 94 36
                       L 94 68
                       C 94 82 90 92 76 92
                       Z"
                    fill="url(#headerMascotGradient)"
                  />
                  <ellipse cx="37" cy="50" rx="9" ry="12" fill="#FFFFFF" />
                  <ellipse cx="38" cy="50" rx="5.5" ry="9" fill="#1A1A1A" />
                  <circle cx="36" cy="46" r="2.8" fill="#FFFFFF" />
                  <ellipse cx="63" cy="50" rx="9" ry="12" fill="#FFFFFF" />
                  <ellipse cx="64" cy="50" rx="5.5" ry="9" fill="#1A1A1A" />
                  <circle cx="62" cy="46" r="2.8" fill="#FFFFFF" />
                  <circle cx="24" cy="62" r="5" fill="url(#headerMascotCheek)" />
                  <circle cx="76" cy="62" r="5" fill="url(#headerMascotCheek)" />
                  <path d="M 40 65 Q 50 75 60 65" stroke="#1A1A1A" stroke-width="4.2" stroke-linecap="round" fill="none" />
                </svg>
              </div>

              <div>
                <div class="flex items-center gap-1.5">
                  <h3 class="font-playfair font-semibold text-sm tracking-wide text-white leading-tight">
                    Claire
                  </h3>
                  <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <p class="text-[9.5px] font-azeret uppercase tracking-wider text-white/90">
                  Éclore Concierge • Online
                </p>
              </div>
            </div>

            <!-- Right Controls: Restart & Close -->
            <div class="flex items-center space-x-1.5">
              <button
                type="button"
                class="w-7 h-7 rounded-full flex items-center justify-center text-white/80 hover:text-white hover:bg-black/15 transition-all cursor-pointer"
                title="Restart conversation"
                aria-label="Restart conversation"
                @click="resetChat"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>

              <button
                type="button"
                class="w-8 h-8 rounded-full flex items-center justify-center text-white/90 hover:text-white hover:bg-black/15 active:scale-95 transition-all cursor-pointer"
                aria-label="Close chat"
                @click="closeChat"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </header>

          <!-- ---------------------------------------------------- -->
          <!-- BODY: Conversation Feed                              -->
          <!-- ---------------------------------------------------- -->
          <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-[#FFFFFF]">
            <!-- Messages Loop -->
            <div
              v-for="msg in messages"
              :key="msg.id"
              class="flex flex-col"
              :class="msg.sender === 'user' ? 'items-end' : 'items-start'"
            >
              <!-- Assistant Message Bubble -->
              <div v-if="msg.sender === 'claire'" class="max-w-[92%] space-y-2">
                <div class="p-4 text-[13px] leading-relaxed text-[#1A1A1A] bg-[#FAF8F5] border border-[#EBE6DC] rounded-2xl rounded-tl-xs shadow-xs font-sans">
                  <!-- Formatted HTML text for bold keywords & linebreaks -->
                  <div v-html="formatMessageText(msg.text)" class="space-y-1"></div>

                  <!-- Optional Product Recommendation Card -->
                  <div
                    v-if="msg.product"
                    class="mt-3 p-3 bg-white border border-[#E5DFD3] rounded-xl flex items-center gap-3 hover:border-[#B6965D] transition-colors cursor-pointer group"
                    @click="handleNavigate(`/products/${msg.product.slug}`)"
                  >
                    <img
                      :src="msg.product.image || '/assets/products/ring-1.jpg'"
                      :alt="msg.product.name"
                      class="w-14 h-14 object-cover rounded-lg border border-[#EBE6DC] shrink-0 bg-[#FAF8F5]"
                    />
                    <div class="min-w-0 flex-1">
                      <p class="font-azeret text-[9px] uppercase tracking-widest text-[#B6965D] truncate">
                        {{ msg.product.material || 'Fine Creation' }}
                      </p>
                      <h4 class="font-playfair text-xs font-medium text-[#1A1A1A] truncate group-hover:text-[#B6965D] transition-colors">
                        {{ msg.product.name }}
                      </h4>
                      <p class="font-azeret text-xs font-bold text-[#1A1A1A] mt-0.5">
                        {{ msg.product.price_formatted }}
                      </p>
                    </div>
                    <span class="text-stone-400 group-hover:text-[#B6965D] text-xs font-bold shrink-0">&rarr;</span>
                  </div>

                  <!-- Interactive Action CTA Button -->
                  <div v-if="msg.action" class="mt-3 pt-2.5 border-t border-[#EAE5DA]">
                    <button
                      type="button"
                      class="w-full py-2.5 px-3.5 bg-[#B6965D] hover:bg-[#A8874E] active:scale-[0.98] text-white text-[11px] font-azeret uppercase tracking-[0.08em] font-medium rounded-xl flex items-center justify-between gap-3 transition-all shadow-xs cursor-pointer leading-normal text-left"
                      @click="handleNavigate(msg.action.route)"
                    >
                      <span class="flex-1 text-center font-medium">{{ msg.action.text }}</span>
                      <span class="text-sm shrink-0 font-bold">&rarr;</span>
                    </button>
                  </div>

                  <!-- Dynamic Suggestions from Claire -->
                  <div v-if="msg.suggestions && msg.suggestions.length > 0" class="mt-3 pt-2.5 border-t border-[#EAE5DA]">
                    <p class="font-azeret text-[9.5px] uppercase tracking-wider text-stone-400 mb-1.5">You can ask:</p>
                    <div class="flex flex-wrap gap-1.5">
                      <button
                        v-for="(sug, sIdx) in msg.suggestions"
                        :key="sIdx"
                        type="button"
                        class="text-[11px] font-sans px-2.5 py-1 bg-white hover:bg-[#FAF8F5] border border-[#E2DDD5] hover:border-[#B6965D] rounded-full text-stone-700 transition-colors cursor-pointer"
                        @click="sendMessage(sug)"
                      >
                        {{ sug }}
                      </button>
                    </div>
                  </div>

                  <!-- Atelier Concierge Subtitle on welcome message -->
                  <div v-if="msg.id === 1" class="mt-3 pt-2 border-t border-[#EAE5DA] flex items-center justify-between text-stone-500 text-[10.5px] font-sans">
                    <span class="flex items-center gap-1.5">
                      <span class="text-[#B6965D]">✨</span>
                      <span>Atelier Concierge Service</span>
                    </span>
                    <button
                      type="button"
                      class="text-[10px] text-[#B6965D] hover:underline font-medium cursor-pointer"
                      @click="showTermsModal = true"
                    >
                      AI Care Terms
                    </button>
                  </div>
                </div>

                <span class="text-[9px] text-stone-400 font-azeret tracking-wider px-1">
                  {{ msg.time }}
                </span>
              </div>

              <!-- User Message Bubble (Warm Luxury Gold) -->
              <div v-else class="max-w-[85%] space-y-1">
                <div class="p-3.5 text-[13px] leading-relaxed text-white bg-gradient-to-r from-[#B6965D] to-[#A8874E] rounded-2xl rounded-tr-xs shadow-xs font-sans">
                  <p class="whitespace-pre-line">{{ msg.text }}</p>
                </div>
                <span class="text-[9px] text-stone-400 font-azeret tracking-wider px-1 text-right block">
                  {{ msg.time }}
                </span>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="isSending" class="flex items-center space-x-2 text-stone-400 font-azeret text-[10px] tracking-widest uppercase pl-2 py-1">
              <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D] animate-bounce"></span>
              <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D] animate-bounce" style="animation-delay: 0.2s"></span>
              <span class="w-1.5 h-1.5 rounded-full bg-[#B6965D] animate-bounce" style="animation-delay: 0.4s"></span>
              <span class="text-[#B6965D] font-medium">Claire is composing...</span>
            </div>

            <!-- Initial Quick Inquiry Chips (Interactive prompts) -->
            <div v-if="showQuickPrompts && messages.length <= 2" class="pt-2">
              <p class="text-[10px] font-azeret uppercase tracking-wider text-stone-400 mb-2">Suggested Inquiries</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="(prompt, idx) in initialQuickPrompts"
                  :key="idx"
                  type="button"
                  class="py-2 px-3.5 bg-[#FAF8F5] hover:bg-[#F2ECE1] border border-[#E5DFD3] hover:border-[#B6965D] text-xs font-sans text-stone-700 rounded-full transition-all text-left flex-shrink-0 cursor-pointer shadow-2xs leading-normal"
                  @click="sendMessage(prompt.replace(/^[^\s]+\s/, ''))"
                >
                  {{ prompt }}
                </button>
              </div>
            </div>
          </div>

          <!-- ---------------------------------------------------- -->
          <!-- INPUT AREA: Rounded Pill with Paperclip & Up-Arrow   -->
          <!-- ---------------------------------------------------- -->
          <div class="p-3 bg-white border-t border-[#F0ECE4] shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
              <!-- Pill Input Container matching Image 3 -->
              <div class="flex-1 bg-[#F2EFE9] border border-[#E2DDD3] rounded-full px-3.5 py-1.5 flex items-center gap-2.5 transition-all focus-within:border-[#B6965D] focus-within:bg-[#FAF8F5]">
                <!-- Paperclip Icon (Quick prompts toggle) -->
                <button
                  type="button"
                  class="text-stone-500 hover:text-[#B6965D] p-1 transition-colors cursor-pointer"
                  title="Suggested Inquiries"
                  @click="showQuickPrompts = !showQuickPrompts"
                >
                  <svg class="w-4 h-4 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                </button>

                <input
                  v-model="inputMessage"
                  type="text"
                  placeholder="Type here..."
                  class="flex-1 bg-transparent border-none text-xs text-[#1A1A1A] placeholder-stone-400 focus:outline-none py-1.5 font-sans"
                  :disabled="isSending"
                />

                <!-- Circular Send Button (Up-Arrow in Warm Gold) -->
                <button
                  type="submit"
                  class="w-7 h-7 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                  :class="[
                    inputMessage.trim() && !isSending
                      ? 'bg-[#B6965D] hover:bg-[#A8874E] text-white active:scale-90'
                      : 'bg-[#DCD7CD] text-stone-500 cursor-not-allowed',
                  ]"
                  :disabled="!inputMessage.trim() || isSending"
                  aria-label="Send message"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                  </svg>
                </button>
              </div>
            </form>

            <!-- Footer AI Terms & Concierge Care Policy Disclaimer -->
            <div class="text-center pt-2 pb-0.5 px-3 text-[10.5px] font-sans text-stone-500 leading-tight">
              <span>This chat is recorded. By chatting, you agree to our </span>
              <button
                type="button"
                class="underline text-[#B6965D] hover:text-[#997943] font-medium transition-colors cursor-pointer"
                @click="showTermsModal = true"
              >
                concierge care policy &amp; AI terms
              </button>.
            </div>
          </div>

          <!-- ---------------------------------------------------- -->
          <!-- CONCIERGE CARE POLICY & AI TERMS OVERLAY MODAL       -->
          <!-- ---------------------------------------------------- -->
          <transition name="fade">
            <div
              v-if="showTermsModal"
              class="absolute inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-3"
              @click.self="showTermsModal = false"
            >
              <div class="bg-white w-full max-h-[92%] rounded-2xl p-5 shadow-2xl flex flex-col border border-[#E5E0D5] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE4] shrink-0">
                  <div>
                    <h4 class="font-playfair font-semibold text-base text-[#1A1A1A]">Concierge Care Policy &amp; AI Terms</h4>
                    <p class="text-[10px] font-azeret uppercase tracking-wider text-stone-400 mt-0.5">Maison Éclore Client Privacy</p>
                  </div>
                  <button
                    type="button"
                    class="w-7 h-7 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center cursor-pointer transition-colors"
                    aria-label="Close policy"
                    @click="showTermsModal = false"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto py-3 space-y-3.5 text-xs text-stone-600 leading-relaxed font-sans pr-1">
                  <div>
                    <h5 class="font-semibold text-stone-900 text-[12.5px] mb-1">1. Dedicated Atelier Concierge</h5>
                    <p>Claire is an automated luxury styling and jewellery concierge designed exclusively for Maison Éclore to guide collectors through our Haute Joaillerie collections, diamond specifications, sizing inquiries, and boutique reservations.</p>
                  </div>
                  <div>
                    <h5 class="font-semibold text-stone-900 text-[12.5px] mb-1">2. Chat Recording &amp; Personalization</h5>
                    <p>Conversations in this concierge salon are securely processed to preserve your styling notes, refine bespoke recommendations, and uphold our Maison's high standards of personalized service. Client records are protected under strict confidentiality standards.</p>
                  </div>
                  <div>
                    <h5 class="font-semibold text-stone-900 text-[12.5px] mb-1">3. Catalogue Information &amp; Estimates</h5>
                    <p>All pricing in Philippine Pesos (₱), precious metal karats, and gemstone descriptions are synchronized directly with our live boutique vault. Bespoke commissions, custom sizing, and formal appraisals are confirmed with our master goldsmiths.</p>
                  </div>
                  <div>
                    <h5 class="font-semibold text-stone-900 text-[12.5px] mb-1">4. Payment &amp; Security Caution</h5>
                    <p>For your financial safety, never transmit credit card numbers, OTPs, or bank passwords within this chat. All acquisitions are finalized through encrypted, PCI-compliant checkout gateways.</p>
                  </div>
                  <div>
                    <h5 class="font-semibold text-stone-900 text-[12.5px] mb-1">5. Master Artisan Salon Access</h5>
                    <p>You may request to speak with a human jewellery advisor at any time or reserve a private consultation at our Manila flagship salon in Greenbelt 5, Ayala Center, Makati.</p>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 border-t border-[#F0ECE4] shrink-0">
                  <button
                    type="button"
                    class="w-full py-2.5 bg-[#B6965D] hover:bg-[#A8874E] text-white text-xs font-medium uppercase tracking-wider rounded-xl transition-all cursor-pointer shadow-xs active:scale-[0.98]"
                    @click="showTermsModal = false"
                  >
                    I Understand &amp; Agree
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Mascot button transition */
.mascot-fade-enter-active,
.mascot-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.mascot-fade-enter-from,
.mascot-fade-leave-to {
  opacity: 0;
  transform: scale(0.7) translateY(12px);
}

/* Modal open/close transition */
.chat-modal-enter-active,
.chat-modal-leave-active {
  transition: all 0.32s cubic-bezier(0.16, 1, 0.3, 1);
}
.chat-modal-enter-from,
.chat-modal-leave-to {
  opacity: 0;
  transform: translateY(24px) scale(0.97);
}

/* Fade transition for policy overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
