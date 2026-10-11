<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';

interface Boutique {
  id: number;
  name: string;
  city: string;
  country: string;
  type: string;
  address: string;
  phone: string;
  hours: string;
  lat: number;
  lng: number;
  image: string;
  services: string[];
}

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const boutiques = ref<Boutique[]>([]);
const loadingBoutiques = ref(false);
const selectedBoutique = ref<Boutique | null>(null);

const serviceTypes = [
  { id: 'HIGH JEWELLERY VIEWING', label: 'High Jewellery Viewing', desc: 'Private salon preview of rare stones and latest high collections.' },
  { id: 'BESPOKE COMMISSION', label: 'Bespoke Commission', desc: 'Direct consultation with our master artisans to co-create a one-of-a-kind piece.' },
  { id: 'BRIDAL CONSULTATION', label: 'Bridal & Solitaire Consultation', desc: 'Selection of exceptional engagement diamonds and wedding bands.' },
  { id: 'VALUATION & RESTORATION', label: 'Valuation & Restoration', desc: 'Official heirloom appraisals and gemstone restoration services.' },
];

const selectedService = ref(serviceTypes[0].id);
const appointmentDate = ref('');
const appointmentTime = ref('14:00');
const availableTimes = ['11:00', '13:00', '14:30', '16:00', '17:30', '19:00'];

const firstName = ref('');
const lastName = ref('');
const email = ref('');
const notes = ref('');

const isSubmitting = ref(false);
const submitError = ref<string | null>(null);
const confirmedAppointment = ref<any | null>(null);

// Minimum date is tomorrow
const minDate = computed(() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toISOString().split('T')[0];
});

async function loadBoutiques() {
  loadingBoutiques.value = true;
  try {
    const res = await apiClient.get<{ data: Boutique[] }>('/v1/boutiques');
    boutiques.value = res.data?.data || [];
    if (boutiques.value.length > 0) {
      // Check query param
      const queryCity = route.query.city?.toString().toLowerCase();
      const match = queryCity
        ? boutiques.value.find((b) => b.city.toLowerCase() === queryCity || b.name.toLowerCase().includes(queryCity))
        : null;
      selectedBoutique.value = match || boutiques.value[0];
    }
  } catch (err) {
    console.warn('Failed to load boutiques from API, using fallback data:', err);
    boutiques.value = [
      {
        id: 1,
        name: 'Paris, Vendôme',
        city: 'Paris',
        country: 'France',
        type: 'FLAGSHIP',
        address: '7 Place Vendôme, 75001 Paris, France',
        phone: '+33 1 42 68 55 00',
        hours: 'Mon - Sat: 10:30 - 19:30',
        lat: 48.867,
        lng: 2.329,
        image: '/assets/appointment.webp',
        services: ['High Jewellery Viewing', 'Bespoke Commission', 'Bridal Salon'],
      },
      {
        id: 2,
        name: 'London, New Bond',
        city: 'London',
        country: 'United Kingdom',
        type: 'BOUTIQUE',
        address: '174 New Bond St, London W1S 4RG, United Kingdom',
        phone: '+44 20 7499 8888',
        hours: 'Mon - Sat: 10:00 - 19:00',
        lat: 51.512,
        lng: -0.142,
        image: '/assets/appointment.webp',
        services: ['Private Salon Viewing', 'Bespoke Atelier'],
      },
      {
        id: 3,
        name: 'New York, 5th Ave',
        city: 'New York',
        country: 'United States',
        type: 'SALON',
        address: '712 5th Ave, New York, NY 10019, United States',
        phone: '+1 212 582 5890',
        hours: 'Mon - Sat: 11:00 - 19:00',
        lat: 40.762,
        lng: -73.974,
        image: '/assets/appointment.webp',
        services: ['VIP Suite', 'Haute Joaillerie Viewing'],
      },
      {
        id: 4,
        name: 'Tokyo, Ginza',
        city: 'Tokyo',
        country: 'Japan',
        type: 'BOUTIQUE',
        address: '5 Chome-2-1 Ginza, Chuo City, Tokyo 104-0061, Japan',
        phone: '+81 3 3567 1100',
        hours: 'Mon - Sun: 11:00 - 20:00',
        lat: 35.671,
        lng: 139.765,
        image: '/assets/appointment.webp',
        services: ['Bespoke Consultation', 'Rare Gemstone Viewing'],
      },
      {
        id: 5,
        name: 'Manila Atelier, Makati',
        city: 'Manila',
        country: 'Philippines',
        type: 'FLAGSHIP ATELIER',
        address: 'Greenbelt 5, Legazpi St, Makati City, Metro Manila, Philippines',
        phone: '+63 2 8812 3456',
        hours: 'Mon - Sun: 11:00 - 21:00',
        lat: 14.552,
        lng: 121.021,
        image: '/assets/appointment.webp',
        services: ['Master Goldsmith Consultation', 'Custom Engagement Solitaires'],
      },
    ];
    selectedBoutique.value = boutiques.value[0];
  } finally {
    loadingBoutiques.value = false;
  }
}

function prefillUser() {
  if (authStore.user) {
    const parts = (authStore.user.name || '').split(' ');
    firstName.value = (authStore.user as any).first_name || parts[0] || '';
    lastName.value = (authStore.user as any).last_name || parts.slice(1).join(' ') || '';
    email.value = authStore.user.email || '';
  }
}

async function handleBookAppointment() {
  if (!selectedBoutique.value) {
    submitError.value = 'Please select a salon location.';
    return;
  }
  if (!appointmentDate.value) {
    submitError.value = 'Please select your preferred date.';
    return;
  }
  if (!firstName.value || !lastName.value || !email.value) {
    submitError.value = 'Please complete your contact details.';
    return;
  }

  isSubmitting.value = true;
  submitError.value = null;

  try {
    const payload = {
      first_name: firstName.value,
      last_name: lastName.value,
      email: email.value,
      location: selectedBoutique.value.name,
      service_type: selectedService.value,
      appointment_date: appointmentDate.value,
      appointment_time: appointmentTime.value,
      notes: notes.value || null,
    };

    const res = await apiClient.post<{ success: boolean; message: string; data: any }>('/v1/appointments/book', payload);
    confirmedAppointment.value = res.data?.data || payload;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err: any) {
    submitError.value = err.response?.data?.message || 'Failed to reserve appointment. Please verify details.';
  } finally {
    isSubmitting.value = false;
  }
}

function downloadIcsCalendar() {
  if (!confirmedAppointment.value) return;
  const appt = confirmedAppointment.value;
  const dateFormatted = (appt.appointment_date || '').replace(/-/g, '');
  const timeFormatted = (appt.appointment_time || '14:00').replace(':', '') + '00';
  const startStr = `${dateFormatted}T${timeFormatted}`;

  const icsContent = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Maison Eclore//Salon Appointment//EN',
    'BEGIN:VEVENT',
    `SUMMARY:Éclore Salon Appointment - ${appt.service_type}`,
    `DESCRIPTION:Private appointment with Maison Éclore artisans.\\nLocation: ${appt.location}`,
    `LOCATION:${appt.location}`,
    `DTSTART:${startStr}`,
    `DTEND:${startStr}`,
    'STATUS:CONFIRMED',
    'END:VEVENT',
    'END:VCALENDAR',
  ].join('\r\n');

  const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', 'eclore-salon-appointment.ics');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function resetForm() {
  confirmedAppointment.value = null;
  appointmentDate.value = '';
  notes.value = '';
}

onMounted(() => {
  appointmentDate.value = minDate.value;
  prefillUser();
  loadBoutiques();
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A] pb-28">
        <!-- Hero Section -->
        <section class="relative bg-[#1A1A1A] text-white py-16 px-6 sm:px-12 overflow-hidden border-b border-[#2C2C2C]">
          <div class="absolute inset-0 opacity-20 pointer-events-none">
            <img
              src="/assets/appointment.webp"
              alt="Maison Éclore Flagship"
              class="w-full h-full object-cover"
            />
          </div>
          <div class="relative max-w-5xl mx-auto space-y-4">
            <!-- Back Navigation -->
            <div class="text-left mb-6">
              <button
                type="button"
                class="inline-flex items-center gap-2 font-azeret text-xs uppercase tracking-widest text-stone-300 hover:text-[#B6965D] transition-colors py-1 group"
                @click="router.push('/catalogue')"
              >
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Atelier</span>
              </button>
            </div>

            <div class="text-center space-y-4">
              <nav class="font-azeret text-[10px] uppercase tracking-[0.35em] text-[#B6965D]">
                Maison Éclore &bull; Private Salons
              </nav>
              <h1 class="font-playfair text-4xl sm:text-6xl font-light tracking-wide">
                Boutique & Salon Appointments
              </h1>
              <p class="font-azeret text-xs sm:text-sm text-stone-300 max-w-2xl mx-auto uppercase tracking-wider leading-relaxed">
                Experience the pinnacle of high joaillerie within our private residences in Paris, London, New York, Tokyo, and Manila.
              </p>
            </div>
          </div>
        </section>

        <!-- Booking Confirmation State -->
        <section v-if="confirmedAppointment" class="max-w-3xl mx-auto px-4 py-16">
          <div class="bg-[#FDFBF7] border border-[#B6965D] p-8 sm:p-12 shadow-xl text-center space-y-6">
            <div class="w-16 h-16 mx-auto rounded-full bg-[#B6965D]/10 flex items-center justify-center text-[#B6965D]">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>

            <div class="space-y-2">
              <span class="font-azeret text-xs uppercase tracking-[0.25em] text-[#B6965D]">Reservation Confirmed</span>
              <h2 class="font-playfair text-3xl sm:text-4xl text-[#1A1A1A]">
                We Await Your Visit
              </h2>
            </div>

            <p class="font-azeret text-xs text-stone-600 max-w-lg mx-auto leading-relaxed">
              Our salon director will prepare a curated selection of creations for your appointment. A formal confirmation dossier has been dispatched to <strong>{{ confirmedAppointment.email }}</strong>.
            </p>

            <!-- Appointment Receipt Card -->
            <div class="bg-white border border-[#E2DDD5] p-6 max-w-md mx-auto text-left space-y-3 font-azeret text-xs">
              <div class="flex justify-between border-b border-[#E2DDD5] pb-2">
                <span class="text-stone-400 uppercase tracking-widest text-[10px]">Location</span>
                <span class="font-semibold text-stone-900">{{ confirmedAppointment.location }}</span>
              </div>
              <div class="flex justify-between border-b border-[#E2DDD5] pb-2">
                <span class="text-stone-400 uppercase tracking-widest text-[10px]">Experience</span>
                <span class="font-semibold text-stone-900">{{ confirmedAppointment.service_type }}</span>
              </div>
              <div class="flex justify-between border-b border-[#E2DDD5] pb-2">
                <span class="text-stone-400 uppercase tracking-widest text-[10px]">Date & Time</span>
                <span class="font-semibold text-[#B6965D]">{{ confirmedAppointment.appointment_date }} at {{ confirmedAppointment.appointment_time }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-stone-400 uppercase tracking-widest text-[10px]">Guest Patron</span>
                <span class="font-semibold text-stone-900">{{ confirmedAppointment.first_name }} {{ confirmedAppointment.last_name }}</span>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
              <EcloreButton variant="primary" size="md" @click="downloadIcsCalendar">
                Add To Calendar (.ics)
              </EcloreButton>
              <EcloreButton variant="outline" size="md" @click="resetForm">
                Book Another Appointment
              </EcloreButton>
            </div>
          </div>
        </section>

        <!-- Booking Wizard -->
        <section v-else class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 space-y-16">
          <!-- Step 1: Select Boutique -->
          <div class="space-y-6">
            <div class="border-b border-[#E2DDD5] pb-4">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">Step 01</span>
              <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A]">Select Your Residence</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="b in boutiques"
                :key="b.id"
                class="bg-[#FDFBF7] border p-6 transition-all cursor-pointer relative flex flex-col justify-between"
                :class="selectedBoutique?.id === b.id ? 'border-[#B6965D] ring-2 ring-[#B6965D]/30 shadow-md' : 'border-[#E2DDD5] hover:border-[#B6965D]/50'"
                @click="selectedBoutique = b"
              >
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="font-azeret text-[9px] uppercase tracking-widest bg-[#B6965D]/10 text-[#B6965D] px-2 py-0.5 rounded">
                      {{ b.type }}
                    </span>
                    <span v-if="selectedBoutique?.id === b.id" class="text-[#B6965D]">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </div>

                  <h3 class="font-playfair text-xl text-[#1A1A1A]">{{ b.name }}</h3>
                  <p class="font-azeret text-[11px] text-stone-500 uppercase tracking-wide leading-relaxed">
                    {{ b.address }}
                  </p>
                </div>

                <div class="pt-4 border-t border-[#E2DDD5]/60 mt-4 space-y-1 font-azeret text-[10px] text-stone-600">
                  <p class="flex items-center gap-1.5">
                    <span>&#9742;</span> {{ b.phone }}
                  </p>
                  <p class="flex items-center gap-1.5 text-[#B6965D]">
                    <span>&#9201;</span> {{ b.hours }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Select Service -->
          <div class="space-y-6">
            <div class="border-b border-[#E2DDD5] pb-4">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">Step 02</span>
              <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A]">Consultation Purpose</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div
                v-for="s in serviceTypes"
                :key="s.id"
                class="bg-[#FDFBF7] border p-5 cursor-pointer transition-all"
                :class="selectedService === s.id ? 'border-[#B6965D] bg-white ring-1 ring-[#B6965D]' : 'border-[#E2DDD5] hover:border-stone-400'"
                @click="selectedService = s.id"
              >
                <div class="flex items-center justify-between mb-2">
                  <h4 class="font-playfair text-lg text-[#1A1A1A]">{{ s.label }}</h4>
                  <span v-if="selectedService === s.id" class="w-3 h-3 rounded-full bg-[#B6965D]"></span>
                </div>
                <p class="font-azeret text-xs text-stone-500 leading-relaxed">{{ s.desc }}</p>
              </div>
            </div>
          </div>

          <!-- Step 3: Date & Time Picker -->
          <div class="space-y-6">
            <div class="border-b border-[#E2DDD5] pb-4">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">Step 03</span>
              <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A]">Date & Preferred Time</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
              <div>
                <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                  Select Date
                </label>
                <input
                  v-model="appointmentDate"
                  type="date"
                  :min="minDate"
                  required
                  class="w-full bg-[#FDFBF7] border border-[#E2DDD5] p-3 font-azeret text-sm text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                />
              </div>

              <div>
                <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                  Preferred Time Slot (Local Salon Time)
                </label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    v-for="time in availableTimes"
                    :key="time"
                    type="button"
                    class="py-2.5 px-3 font-azeret text-xs border text-center transition-all"
                    :class="appointmentTime === time ? 'border-[#B6965D] bg-[#B6965D] text-white font-bold' : 'border-[#E2DDD5] bg-[#FDFBF7] text-stone-700 hover:border-stone-400'"
                    @click="appointmentTime = time"
                  >
                    {{ time }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Patron Details & Special Inquiries -->
          <div class="space-y-6">
            <div class="border-b border-[#E2DDD5] pb-4">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">Step 04</span>
              <h2 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A]">Patron Information</h2>
            </div>

            <div v-if="submitError" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs font-azeret">
              {{ submitError }}
            </div>

            <form @submit.prevent="handleBookAppointment" class="space-y-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                    First Name
                  </label>
                  <input
                    v-model="firstName"
                    type="text"
                    required
                    placeholder="GIVEN NAME"
                    class="w-full bg-[#FDFBF7] border border-[#E2DDD5] p-3 font-azeret text-sm text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                  />
                </div>
                <div>
                  <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                    Last Name
                  </label>
                  <input
                    v-model="lastName"
                    type="text"
                    required
                    placeholder="SURNAME"
                    class="w-full bg-[#FDFBF7] border border-[#E2DDD5] p-3 font-azeret text-sm text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                  />
                </div>
              </div>

              <div>
                <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                  Email Address for Confirmation Dossier
                </label>
                <input
                  v-model="email"
                  type="email"
                  required
                  placeholder="CLIENT@MAISON.COM"
                  class="w-full bg-[#FDFBF7] border border-[#E2DDD5] p-3 font-azeret text-sm text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                />
              </div>

              <div>
                <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#1A1A1A] mb-2 font-semibold">
                  Special Inquiries / Specific Gemstones or Solitaires of Interest (Optional)
                </label>
                <textarea
                  v-model="notes"
                  rows="3"
                  placeholder="e.g. Inquiring regarding 3.5 carat oval cut diamond solitaire or anniversary bespoke suite..."
                  class="w-full bg-[#FDFBF7] border border-[#E2DDD5] p-3 font-azeret text-sm text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                ></textarea>
              </div>

              <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="font-azeret text-[10px] text-stone-500 uppercase tracking-wide">
                  Appointment confirmation is strictly confidential and complimentary.
                </p>
                <EcloreButton
                  variant="primary"
                  size="lg"
                  type="submit"
                  :disabled="isSubmitting"
                  class="w-full sm:w-auto"
                >
                  {{ isSubmitting ? 'Reserving...' : 'Confirm Private Reservation' }}
                </EcloreButton>
              </div>
            </form>
          </div>
        </section>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
