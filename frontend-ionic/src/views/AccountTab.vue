<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import type { Order, OrderItem } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';
import EcloreModal from '@/components/EcloreModal.vue';
import OrderHistoryList from '@/components/account/OrderHistoryList.vue';
import OrderDetailModal from '@/components/account/OrderDetailModal.vue';
import RmaRequestModal from '@/components/account/RmaRequestModal.vue';
import { useAuthStore } from '@/stores/auth.store';
import { useOrdersStore } from '@/stores/orders.store';
import { apiClient } from '@/api/client';

const authStore = useAuthStore();
const ordersStore = useOrdersStore();
const router = useRouter();

// Tab state: 'profile' | 'address' | 'orders' | 'appointments' | 'security'
const activeTab = ref<'profile' | 'address' | 'orders' | 'appointments' | 'security'>('orders');

interface UserAppointment {
  id: number;
  location: string;
  service_type: string;
  appointment_date: string;
  appointment_time: string;
  status: string;
  notes?: string | null;
  created_at: string;
}

const appointments = ref<UserAppointment[]>([]);
const loadingAppointments = ref(false);

async function fetchAppointments() {
  loadingAppointments.value = true;
  try {
    const res = await apiClient.get<{ data: UserAppointment[] }>('/v1/account/appointments');
    appointments.value = res.data?.data || [];
  } catch (err) {
    console.warn('Failed to load user appointments:', err);
  } finally {
    loadingAppointments.value = false;
  }
}

// Feedback states
const formSuccess = ref('');
const formError = ref('');
const isSaving = ref(false);

function clearFeedback() {
  formSuccess.value = '';
  formError.value = '';
}

// ====================================================
// 1. Profile / My Details Form State
// ====================================================
const profileFirstName = ref('');
const profileLastName = ref('');
const profileUsername = ref('');
const profileEmail = ref('');
const profilePhone = ref('');

function initProfileForm() {
  if (authStore.user) {
    const u: any = authStore.user;
    profileFirstName.value = u.first_name || '';
    profileLastName.value = u.last_name || '';
    profileUsername.value = u.username || '';
    profileEmail.value = u.email || '';
    profilePhone.value = u.phone || '';
  }
}

async function handleSaveProfile() {
  clearFeedback();
  isSaving.value = true;
  try {
    const res = await authStore.updateProfile({
      first_name: profileFirstName.value.trim(),
      last_name: profileLastName.value.trim(),
      phone: profilePhone.value.trim(),
      username: profileUsername.value.trim() || undefined,
    });
    formSuccess.value = res.message || 'Atelier profile details saved successfully.';
  } catch (err: any) {
    formError.value = err.response?.data?.message || err.message || 'Failed to update profile.';
  } finally {
    isSaving.value = false;
  }
}

// ====================================================
// 2. Philippine Address Book Form State
// ====================================================
const addressStreet = ref('');
const addressRegion = ref('');
const addressProvince = ref('');
const addressCity = ref('');
const addressBarangay = ref('');
const addressZipCode = ref('');

function initAddressForm() {
  if (authStore.user) {
    const u: any = authStore.user;
    addressStreet.value = u.street || '';
    addressRegion.value = u.region || 'National Capital Region (NCR)';
    addressProvince.value = u.province || 'Metro Manila';
    addressCity.value = u.city || '';
    addressBarangay.value = u.barangay || '';
    addressZipCode.value = u.zip_code || '';
  }
}

async function handleSaveAddress() {
  clearFeedback();
  isSaving.value = true;
  try {
    const res = await authStore.updateAddress({
      street: addressStreet.value.trim(),
      region: addressRegion.value.trim(),
      province: addressProvince.value.trim(),
      city: addressCity.value.trim(),
      barangay: addressBarangay.value.trim(),
      zip_code: addressZipCode.value.trim(),
    });
    formSuccess.value = res.message || 'Philippine delivery address saved successfully.';
  } catch (err: any) {
    formError.value = err.response?.data?.message || err.message || 'Failed to update address.';
  } finally {
    isSaving.value = false;
  }
}

// ====================================================
// 3. Security / Password Change State
// ====================================================
const currentPassword = ref('');
const newPassword = ref('');
const confirmNewPassword = ref('');
const showPasswords = ref(false);

const passwordsMatch = computed(() => {
  if (!confirmNewPassword.value) return null;
  return newPassword.value === confirmNewPassword.value;
});

async function handleChangePassword() {
  clearFeedback();
  if (newPassword.value.length < 8) {
    formError.value = 'New password must contain at least 8 characters.';
    return;
  }
  if (!passwordsMatch.value) {
    formError.value = 'New passwords do not match.';
    return;
  }

  isSaving.value = true;
  try {
    const res = await authStore.changePassword({
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: confirmNewPassword.value,
    });
    formSuccess.value = res.message || 'Password updated securely.';
    currentPassword.value = '';
    newPassword.value = '';
    confirmNewPassword.value = '';
  } catch (err: any) {
    formError.value = err.response?.data?.message || err.message || 'Failed to update password. Please verify current password.';
  } finally {
    isSaving.value = false;
  }
}

// ====================================================
// 4. Modal Handlers: Order Details & RMA
// ====================================================
const selectedOrder = ref<Order | null>(null);
const isDetailModalOpen = ref(false);
const isRmaModalOpen = ref(false);
const preselectedRmaItem = ref<OrderItem | null>(null);

function handleViewOrder(order: Order) {
  selectedOrder.value = order;
  isDetailModalOpen.value = true;
}

function handleOpenRma(order: Order, item?: OrderItem) {
  selectedOrder.value = order;
  preselectedRmaItem.value = item || null;
  isDetailModalOpen.value = false;
  isRmaModalOpen.value = true;
}

function handleRmaSubmitted(rmaNumber: string) {
  formSuccess.value = `After-sales claim ${rmaNumber} created successfully. Our concierge will contact you shortly.`;
}

// Sign Out State & Confirmation Modal
const isLogoutModalOpen = ref(false);

function handleLogout() {
  isLogoutModalOpen.value = true;
}

async function handleConfirmLogout() {
  isLogoutModalOpen.value = false;
  await authStore.logout();
  router.push('/');
}

watch(
  () => activeTab.value,
  (newTab) => {
    if (newTab === 'orders') {
      if (authStore.isAuthenticated) {
        ordersStore.fetchOrders(1);
        ordersStore.fetchReturnsRepairs();
      }
    } else if (newTab === 'profile') {
      initProfileForm();
    } else if (newTab === 'address') {
      initAddressForm();
    } else if (newTab === 'appointments') {
      if (authStore.isAuthenticated) {
        fetchAppointments();
      }
    }
  }
);

watch(
  () => authStore.user,
  () => {
    initProfileForm();
    initAddressForm();
  },
  { deep: true }
);

onMounted(() => {
  initProfileForm();
  initAddressForm();
  if (authStore.isAuthenticated) {
    ordersStore.fetchOrders(1);
    ordersStore.fetchReturnsRepairs();
  }
});
</script>

<template>
  <div class="space-y-6">
    <!-- Atelier Header Profile Banner -->
    <div class="bg-white border border-[#E2DDD5] p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
      <div class="flex items-center space-x-4">
        <!-- Avatar circle with gold ring -->
        <div class="w-14 h-14 bg-[#F3EFE7] border-2 border-[#B6965D] rounded-full flex items-center justify-center text-[#B6965D] font-playfair text-xl font-bold">
          {{ authStore.userName.charAt(0).toUpperCase() }}
        </div>
        <div>
          <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Maison Client</span>
          <h1 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A] mt-0.5">{{ authStore.userName }}</h1>
          <p class="font-azeret text-xs text-[#767676]">{{ authStore.userEmail }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-3 w-full sm:w-auto justify-end pt-2 sm:pt-0 border-t sm:border-t-0 border-[#E2DDD5]">
        <router-link to="/track-order" class="hidden sm:inline-block">
          <EcloreButton variant="outline" size="sm">
            Parcel Tracker
          </EcloreButton>
        </router-link>
        <button
          type="button"
          class="border border-[#D1CCC2] hover:border-[#B6965D] bg-white hover:bg-[#FAF8F5] text-stone-700 hover:text-[#B6965D] active:scale-95 px-4 py-2 min-h-[38px] text-[11px] font-azeret uppercase tracking-[0.15em] font-medium transition-all shadow-2xs select-none cursor-pointer"
          @click="handleLogout"
        >
          Sign Out
        </button>
      </div>
    </div>

    <!-- Feedback Banner -->
    <div v-if="formSuccess" class="p-3.5 text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-azeret flex items-center justify-between">
      <span>{{ formSuccess }}</span>
      <button type="button" @click="formSuccess = ''" class="text-emerald-600 hover:text-emerald-800 ml-2">&times;</button>
    </div>
    <div v-if="formError" class="p-3.5 text-xs bg-red-50 text-red-800 border border-red-200 font-azeret flex items-center justify-between">
      <span>{{ formError }}</span>
      <button type="button" @click="formError = ''" class="text-red-600 hover:text-red-800 ml-2">&times;</button>
    </div>

    <!-- Navigation Tabs (Sleek luxury bar with no scrollbar clutter) -->
    <div class="bg-white border border-[#E2DDD5] shadow-2xs overflow-x-auto no-scrollbar">
      <nav class="flex min-w-full text-xs font-azeret uppercase tracking-wider" aria-label="Account navigation">
        <button
          type="button"
          class="flex-1 min-w-[140px] sm:min-w-0 py-3.5 px-3 sm:px-4 text-center border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider transition-all cursor-pointer whitespace-nowrap -mb-[1px]"
          :class="activeTab === 'orders'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D] bg-[#FAF8F5]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:bg-stone-50/60'"
          @click="activeTab = 'orders'; clearFeedback()"
        >
          My Orders & Claims
        </button>

        <button
          type="button"
          class="flex-1 min-w-[120px] sm:min-w-0 py-3.5 px-3 sm:px-4 text-center border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider transition-all cursor-pointer whitespace-nowrap -mb-[1px]"
          :class="activeTab === 'profile'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D] bg-[#FAF8F5]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:bg-stone-50/60'"
          @click="activeTab = 'profile'; clearFeedback(); initProfileForm()"
        >
          My Details
        </button>

        <button
          type="button"
          class="flex-1 min-w-[130px] sm:min-w-0 py-3.5 px-3 sm:px-4 text-center border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider transition-all cursor-pointer whitespace-nowrap -mb-[1px]"
          :class="activeTab === 'address'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D] bg-[#FAF8F5]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:bg-stone-50/60'"
          @click="activeTab = 'address'; clearFeedback(); initAddressForm()"
        >
          Address Book
        </button>

        <button
          type="button"
          class="flex-1 min-w-[160px] sm:min-w-0 py-3.5 px-3 sm:px-4 text-center border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider transition-all cursor-pointer whitespace-nowrap -mb-[1px]"
          :class="activeTab === 'appointments'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D] bg-[#FAF8F5]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:bg-stone-50/60'"
          @click="activeTab = 'appointments'; clearFeedback(); fetchAppointments()"
        >
          Salon Appointments
        </button>

        <button
          type="button"
          class="flex-1 min-w-[110px] sm:min-w-0 py-3.5 px-3 sm:px-4 text-center border-b-2 font-azeret text-[11px] sm:text-xs tracking-wider transition-all cursor-pointer whitespace-nowrap -mb-[1px]"
          :class="activeTab === 'security'
            ? 'text-[#1A1A1A] font-bold border-[#B6965D] bg-[#FAF8F5]'
            : 'text-[#767676] hover:text-[#1A1A1A] border-transparent hover:bg-stone-50/60'"
          @click="activeTab = 'security'; clearFeedback()"
        >
          Security
        </button>
      </nav>
    </div>

    <!-- TAB CONTENT: MY ORDERS -->
    <div v-if="activeTab === 'orders'">
      <OrderHistoryList
        @view-order="handleViewOrder"
        @open-rma="handleOpenRma"
      />

      <!-- Active RMA Claims Section -->
      <div v-if="ordersStore.returnsRepairs.length > 0" class="mt-8 bg-white border border-[#E2DDD5] p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#EAE6DF] pb-3">
          <div>
            <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">After-Sales Registry</span>
            <h3 class="font-playfair text-lg text-[#1A1A1A] font-semibold">Active Service & Return Claims</h3>
          </div>
          <span class="text-xs font-azeret text-[#767676]">{{ ordersStore.returnsRepairs.length }} Active</span>
        </div>

        <div class="divide-y divide-[#EAE6DF]">
          <div
            v-for="claim in ordersStore.returnsRepairs"
            :key="claim.id"
            class="py-3 flex flex-wrap items-center justify-between gap-3 text-xs font-azeret"
          >
            <div>
              <div class="flex items-center space-x-2">
                <span class="font-bold text-[#1A1A1A]">{{ claim.rma_number }}</span>
                <span class="px-2 py-0.5 text-[9px] uppercase font-semibold bg-amber-50 text-amber-800 border border-amber-200 rounded-full">
                  {{ claim.status }}
                </span>
              </div>
              <p class="text-[#767676] text-[11px] mt-0.5 capitalize">Reason: {{ claim.reason.replace(/_/g, ' ') }}</p>
            </div>

            <div class="text-right text-[10px] text-[#767676]">
              <span>Filed {{ new Date(claim.created_at).toLocaleDateString() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB CONTENT: PROFILE / MY DETAILS -->
    <div v-else-if="activeTab === 'profile'" class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-6 shadow-sm">
      <div class="border-b border-[#E2DDD5] pb-4">
        <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Personal Record</span>
        <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">My Atelier Details</h2>
        <p class="font-azeret text-xs text-[#767676] mt-0.5">Manage your personal identity and correspondence details.</p>
      </div>

      <form @submit.prevent="handleSaveProfile" class="space-y-5 max-w-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              First Name
            </label>
            <input
              v-model="profileFirstName"
              type="text"
              required
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>

          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              Last Name
            </label>
            <input
              v-model="profileLastName"
              type="text"
              required
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Username
          </label>
          <input
            v-model="profileUsername"
            type="text"
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Email Address (Verified)
          </label>
          <input
            v-model="profileEmail"
            type="email"
            disabled
            class="w-full bg-[#F3EFE7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#767676] cursor-not-allowed"
          />
          <span class="text-[10px] text-[#767676] font-azeret mt-1 block">Account email is tied to verified Maison certificates.</span>
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Contact Telephone / Mobile
          </label>
          <input
            v-model="profilePhone"
            type="tel"
            placeholder="0917-000-0000"
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div class="pt-3">
          <EcloreButton variant="primary" size="md" type="submit" :disabled="isSaving">
            {{ isSaving ? 'Saving Changes...' : 'Save Profile Changes' }}
          </EcloreButton>
        </div>
      </form>
    </div>

    <!-- TAB CONTENT: PHILIPPINE ADDRESS BOOK -->
    <div v-else-if="activeTab === 'address'" class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-6 shadow-sm">
      <div class="border-b border-[#E2DDD5] pb-4">
        <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Delivery Destination</span>
        <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">Philippine Address Book</h2>
        <p class="font-azeret text-xs text-[#767676] mt-0.5">Authoritative address for insured courier parcel deliveries.</p>
      </div>

      <form @submit.prevent="handleSaveAddress" class="space-y-4 max-w-xl">
        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Street Address, Building, Suite or Unit
          </label>
          <input
            v-model="addressStreet"
            type="text"
            required
            placeholder="e.g. Unit 24B, Grand Hyatt Residences, 8th Avenue"
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              Region
            </label>
            <input
              v-model="addressRegion"
              type="text"
              required
              placeholder="e.g. National Capital Region (NCR)"
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>

          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              Province (Optional for NCR)
            </label>
            <input
              v-model="addressProvince"
              type="text"
              placeholder="e.g. Metro Manila or Cebu"
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              City / Municipality
            </label>
            <input
              v-model="addressCity"
              type="text"
              required
              placeholder="e.g. Makati City, Taguig, Cebu City"
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>

          <div>
            <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
              Barangay
            </label>
            <input
              v-model="addressBarangay"
              type="text"
              required
              placeholder="e.g. Bel-Air, Fort Bonifacio"
              class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
            />
          </div>
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Postal / ZIP Code
          </label>
          <input
            v-model="addressZipCode"
            type="text"
            required
            placeholder="e.g. 1209"
            maxlength="10"
            class="w-full sm:w-48 bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div class="pt-3">
          <EcloreButton variant="primary" size="md" type="submit" :disabled="isSaving">
            {{ isSaving ? 'Saving Address...' : 'Update Address Book' }}
          </EcloreButton>
        </div>
      </form>
    </div>

    <!-- TAB CONTENT: SALON APPOINTMENTS -->
    <div v-else-if="activeTab === 'appointments'" class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-6 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#E2DDD5] gap-4">
        <div>
          <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Private Salon Consultations</span>
          <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">My Salon Appointments</h2>
          <p class="font-azeret text-xs text-[#767676] mt-0.5">Scheduled boutique previews and bespoke jewellery commissions</p>
        </div>
        <EcloreButton variant="primary" size="sm" @click="router.push('/appointments')">
          Book New Consultation
        </EcloreButton>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loadingAppointments" class="py-10 space-y-3">
        <div v-for="i in 2" :key="i" class="animate-pulse p-4 bg-[#FDFBF7] border border-[#E2DDD5]">
          <div class="h-4 bg-stone-200 w-1/3 mb-2 rounded"></div>
          <div class="h-3 bg-stone-200 w-2/3 rounded"></div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="appointments.length === 0" class="py-12 text-center space-y-4 bg-[#FDFBF7] border border-[#E2DDD5] p-6">
        <div class="w-12 h-12 mx-auto rounded-full bg-[#B6965D]/10 flex items-center justify-center text-[#B6965D]">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
        </div>
        <h3 class="font-playfair text-xl text-[#1A1A1A]">No Salon Consultations Scheduled</h3>
        <p class="font-azeret text-xs text-[#767676] max-w-sm mx-auto">
          Reserve a private viewing suite in Paris, London, New York, Tokyo, or Manila for high jewellery previews and bespoke ring commissions.
        </p>
        <div class="pt-2">
          <EcloreButton variant="outline" size="sm" @click="router.push('/appointments')">
            Arrange A Private Viewing
          </EcloreButton>
        </div>
      </div>

      <!-- Appointments list -->
      <div v-else class="space-y-4">
        <article
          v-for="appt in appointments"
          :key="appt.id"
          class="p-5 bg-[#FDFBF7] border border-[#E2DDD5] flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <span class="font-playfair text-lg text-[#1A1A1A]">{{ appt.service_type }}</span>
              <span
                class="font-azeret text-[9px] uppercase tracking-wider px-2 py-0.5 rounded font-bold"
                :class="appt.status === 'confirmed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-[#B6965D]/10 text-[#B6965D] border border-[#B6965D]/20'"
              >
                {{ appt.status }}
              </span>
            </div>
            <p class="font-azeret text-xs text-[#767676]">
              {{ appt.location }} &bull; {{ appt.appointment_date }} at {{ appt.appointment_time }}
            </p>
            <p v-if="appt.notes" class="font-azeret text-[11px] text-stone-500 italic mt-1">
              "{{ appt.notes }}"
            </p>
          </div>
          <div class="flex sm:flex-col items-end gap-2 shrink-0">
            <EcloreButton variant="outline" size="sm" @click="router.push('/appointments')">
              Reschedule
            </EcloreButton>
          </div>
        </article>
      </div>
    </div>

    <!-- TAB CONTENT: SECURITY & PASSWORD -->
    <div v-else-if="activeTab === 'security'" class="bg-white border border-[#E2DDD5] p-6 sm:p-8 space-y-6 shadow-sm">
      <div class="border-b border-[#E2DDD5] pb-4">
        <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Account Integrity</span>
        <h2 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">Security & Authentication</h2>
        <p class="font-azeret text-xs text-[#767676] mt-0.5">Update password and safeguard your fine jewellery client portfolio.</p>
      </div>

      <form @submit.prevent="handleChangePassword" class="space-y-4 max-w-xl">
        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Current Password
          </label>
          <input
            v-model="currentPassword"
            :type="showPasswords ? 'text' : 'password'"
            required
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            New Password (min 8 characters)
          </label>
          <input
            v-model="newPassword"
            :type="showPasswords ? 'text' : 'password'"
            required
            minlength="8"
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
        </div>

        <div>
          <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1 font-semibold">
            Confirm New Password
          </label>
          <input
            v-model="confirmNewPassword"
            :type="showPasswords ? 'text' : 'password'"
            required
            class="w-full bg-[#FDFBF7] border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
          />
          <span v-if="passwordsMatch === false" class="text-xs text-red-600 font-azeret mt-1 block">
            Passwords do not match.
          </span>
        </div>

        <div class="flex items-center space-x-2 pt-1">
          <input
            id="toggleShowPass"
            v-model="showPasswords"
            type="checkbox"
            class="accent-[#B6965D]"
          />
          <label for="toggleShowPass" class="text-xs font-azeret text-[#767676] cursor-pointer">
            Show passwords in plain text
          </label>
        </div>

        <div class="pt-3">
          <EcloreButton variant="primary" size="md" type="submit" :disabled="isSaving || !currentPassword || !newPassword || passwordsMatch === false">
            {{ isSaving ? 'Securing...' : 'Change Password' }}
          </EcloreButton>
        </div>
      </form>
    </div>

    <!-- Modals -->
    <OrderDetailModal
      :is-open="isDetailModalOpen"
      :order="selectedOrder"
      @close="isDetailModalOpen = false"
      @open-rma="handleOpenRma"
    />

    <RmaRequestModal
      :is-open="isRmaModalOpen"
      :order="selectedOrder"
      :preselected-item="preselectedRmaItem"
      @close="isRmaModalOpen = false"
      @submitted="handleRmaSubmitted"
    />

    <!-- Sign Out Confirmation Modal -->
    <EcloreModal
      :is-open="isLogoutModalOpen"
      title="Sign Out Confirmation"
      subtitle="Maison Éclore Account"
      @close="isLogoutModalOpen = false"
    >
      <div class="space-y-3 py-2 text-[#1A1A1A]">
        <p class="font-sans text-sm leading-relaxed text-stone-700">
          Are you sure you wish to sign out of your Maison Éclore client portal?
        </p>
        <p class="font-azeret text-xs text-stone-500">
          Your curated cart creations, bespoke wishlist, and order history will remain securely preserved for your next visit.
        </p>
      </div>

      <template #footer>
        <button
          type="button"
          class="px-5 py-2.5 text-xs font-azeret uppercase tracking-wider text-stone-600 hover:text-[#1A1A1A] border border-[#E2DDD5] bg-white hover:bg-stone-50 transition-colors cursor-pointer"
          @click="isLogoutModalOpen = false"
        >
          Cancel
        </button>
        <button
          type="button"
          class="px-5 py-2.5 text-xs font-azeret uppercase tracking-wider bg-[#B6965D] hover:bg-[#9E7E46] text-white transition-colors font-semibold cursor-pointer shadow-xs active:scale-95"
          @click="handleConfirmLogout"
        >
          Confirm Sign Out
        </button>
      </template>
    </EcloreModal>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
