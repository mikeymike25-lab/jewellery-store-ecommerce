<script setup lang="ts">
import { ref } from 'vue';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';

const authStore = useAuthStore();

const fullName = ref(authStore.user?.name || '');
const email = ref(authStore.user?.email || '');
const subject = ref('Bespoke Commission');
const message = ref('');

const isSubmitting = ref(false);
const submitSuccess = ref(false);
const submitError = ref('');

const subjectOptions = [
  'Bespoke Commission',
  'Acquisition & Private Sale',
  'High Jewellery Consultation',
  'After-Sales & Restoration',
  'Press & Partnerships',
  'General Inquiry',
];

async function handleSubmit() {
  submitError.value = '';
  if (!fullName.value.trim() || !email.value.trim() || !message.value.trim()) {
    submitError.value = 'Please provide your full name, email, and inquiry message.';
    return;
  }

  isSubmitting.value = true;
  try {
    await apiClient.post<{ success: boolean; message: string }>('/v1/contact', {
      name: fullName.value.trim(),
      email: email.value.trim(),
      message: `[${subject.value}] ${message.value.trim()}`,
    });

    submitSuccess.value = true;
    message.value = '';
  } catch (err: any) {
    submitError.value = err.response?.data?.message || 'Failed to dispatch inquiry. Please try again.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A]">
        <!-- Hero Header -->
        <header class="bg-[#1A1A1A] text-white py-16 sm:py-20 px-6 text-center border-b border-[#2C2C2C]">
          <div class="max-w-4xl mx-auto space-y-4">
            <span class="font-azeret text-[10px] uppercase tracking-[0.35em] text-[#B6965D]">
              Maison Éclore &bull; Haute Joaillerie
            </span>
            <h1 class="font-playfair text-4xl sm:text-6xl font-light tracking-wide text-white">
              Connect With Us
            </h1>
            <p class="font-azeret text-xs sm:text-sm text-stone-300 uppercase tracking-widest max-w-xl mx-auto leading-relaxed">
              A boutique experience begins with a singular conversation. Our concierge is here to assist with every inquiry.
            </p>
          </div>
        </header>

        <!-- Main Content Area -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <!-- Left: Contact Form -->
            <div class="lg:col-span-7 bg-white border border-[#E2DDD5] p-6 sm:p-10 shadow-sm space-y-6">
              <div>
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Correspondence</span>
                <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mt-1 font-semibold">Submit An Inquiry</h2>
                <p class="font-azeret text-xs text-[#767676] mt-1">Our client advisors typically respond within 24 business hours.</p>
              </div>

              <!-- Success Feedback Banner -->
              <div
                v-if="submitSuccess"
                class="p-5 bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-2 font-azeret"
              >
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                  <p class="text-xs font-semibold uppercase tracking-wider">Inquiry Received</p>
                </div>
                <p class="text-xs font-light">
                  Thank you for reaching out to Maison Éclore. Your message has been routed to our dedicated atelier concierge team.
                </p>
                <button
                  type="button"
                  class="text-[11px] text-emerald-700 underline uppercase tracking-wider pt-2"
                  @click="submitSuccess = false"
                >
                  Send another message &rarr;
                </button>
              </div>

              <!-- Error Banner -->
              <div v-if="submitError" class="p-3.5 bg-red-50 border border-red-200 text-red-700 font-azeret text-xs">
                {{ submitError }}
              </div>

              <!-- Inquiry Form -->
              <form v-if="!submitSuccess" @submit.prevent="handleSubmit" class="space-y-5 font-azeret">
                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Given & Family Name
                  </label>
                  <input
                    v-model="fullName"
                    type="text"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="Claire Laurent"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Email Address
                  </label>
                  <input
                    v-model="email"
                    type="email"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                    placeholder="client@eclore.com"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Subject Of Conversation
                  </label>
                  <select
                    v-model="subject"
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 min-h-[48px] text-sm focus:outline-none focus:border-[#B6965D] transition-colors"
                  >
                    <option v-for="opt in subjectOptions" :key="opt" :value="opt">
                      {{ opt }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider text-[#1A1A1A] font-medium mb-1.5">
                    Your Message & Requirements
                  </label>
                  <textarea
                    v-model="message"
                    rows="5"
                    required
                    class="w-full bg-[#FAF8F5] border border-[#E2DDD5] px-4 py-3 text-sm focus:outline-none focus:border-[#B6965D] transition-colors resize-none leading-relaxed"
                    placeholder="Please specify gem preferences, diamond requirements, bespoke ideas, or consultation details..."
                  ></textarea>
                </div>

                <div class="pt-2">
                  <EcloreButton
                    type="submit"
                    variant="primary"
                    size="md"
                    block
                    :loading="isSubmitting"
                  >
                    Transmit Inquiry to Concierge
                  </EcloreButton>
                </div>
              </form>
            </div>

            <!-- Right: Maison Directory & Ateliers -->
            <div class="lg:col-span-5 space-y-6">
              <!-- Flagship Card -->
              <div class="bg-[#FAF8F5] border border-[#E2DDD5] p-6 sm:p-8 space-y-4 shadow-sm">
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D] font-bold">
                  Flagship Salons
                </span>
                <h3 class="font-playfair text-2xl text-[#1A1A1A] font-semibold">Atelier Locations</h3>

                <div class="space-y-4 pt-2 font-azeret text-xs divide-y divide-[#E2DDD5]">
                  <div class="pt-2">
                    <h4 class="font-semibold text-sm text-[#1A1A1A]">Manila Flagship</h4>
                    <p class="text-stone-600 mt-0.5">8747 Paseo de Roxas, Makati City, Metro Manila</p>
                    <p class="text-stone-500 text-[11px] mt-1">+63 (2) 8888 3256 &bull; Monday &ndash; Saturday</p>
                  </div>
                  <div class="pt-3">
                    <h4 class="font-semibold text-sm text-[#1A1A1A]">Paris Private Salon</h4>
                    <p class="text-stone-600 mt-0.5">18 Place Vendôme, 75001 Paris, France</p>
                    <p class="text-stone-500 text-[11px] mt-1">+33 1 42 68 00 00 &bull; By Appointment Only</p>
                  </div>
                  <div class="pt-3">
                    <h4 class="font-semibold text-sm text-[#1A1A1A]">Tokyo Boutique</h4>
                    <p class="text-stone-600 mt-0.5">6-10-1 Ginza, Chuo-ku, Tokyo, Japan</p>
                    <p class="text-stone-500 text-[11px] mt-1">+81 3 5555 0192 &bull; Daily</p>
                  </div>
                </div>

                <div class="pt-4 border-t border-[#E2DDD5]">
                  <router-link to="/appointments">
                    <EcloreButton variant="outline" size="sm" block class="text-center">
                      Arrange Private Salon Viewing &rarr;
                    </EcloreButton>
                  </router-link>
                </div>
              </div>

              <!-- Direct Inquiries Card -->
              <div class="bg-[#1A1A1A] text-white p-6 sm:p-8 space-y-4 border border-[#2C2C2C] shadow-sm">
                <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Direct Concierge</span>
                <h3 class="font-playfair text-xl text-white">Private Client Services</h3>
                <p class="font-azeret text-xs text-stone-300 leading-relaxed">
                  For immediate assistance regarding urgent high jewellery acquisitions or secure international armored delivery:
                </p>
                <div class="space-y-1 font-azeret text-xs text-[#B6965D]">
                  <p>Email: concierge@eclore.com</p>
                  <p>Client Hotline: +1 (800) 925-3256</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
