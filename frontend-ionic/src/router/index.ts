import { createRouter, createWebHistory } from '@ionic/vue-router';
import { type RouteRecordRaw } from 'vue-router';
import TabsPage from '@/views/TabsPage.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/tabs/home',
  },
  {
    path: '/tabs/',
    component: TabsPage,
    children: [
      {
        path: '',
        redirect: '/tabs/home',
      },
      {
        path: 'home',
        component: () => import('@/views/HomeView.vue'),
      },
      {
        path: 'catalogue',
        component: () => import('@/views/CatalogueView.vue'),
      },
      {
        path: 'coffret',
        component: () => import('@/views/CartView.vue'),
      },
      {
        path: 'cart',
        redirect: '/tabs/coffret',
      },
      {
        path: 'basket',
        redirect: '/tabs/coffret',
      },
      {
        path: 'account',
        component: () => import('@/views/AccountView.vue'),
      },
    ],
  },
  {
    path: '/catalogue',
    redirect: '/tabs/catalogue',
  },
  {
    path: '/coffret',
    redirect: '/tabs/coffret',
  },
  {
    path: '/cart',
    name: 'Cart',
    component: () => import('@/views/CartView.vue'),
    meta: { title: 'Shopping Bag — Éclore' },
  },
  {
    path: '/basket',
    redirect: '/cart',
  },
  {
    path: '/wishlist',
    component: () => import('@/views/WishlistView.vue'),
  },
  {
    path: '/account',
    redirect: '/tabs/account',
  },
  {
    path: '/products/:slug',
    component: () => import('@/views/ProductDetailView.vue'),
  },
  {
    path: '/product/:slug',
    redirect: (to) => `/products/${to.params.slug}`,
  },
  {
    path: '/checkout',
    component: () => import('@/views/CheckoutView.vue'),
  },
  {
    path: '/checkout/confirmation/:orderNumber',
    name: 'checkout.confirmation',
    component: () => import('@/views/OrderConfirmationView.vue'),
  },
  {
    path: '/checkout/confirmation',
    component: () => import('@/views/OrderConfirmationView.vue'),
  },
  {
    path: '/track-order',
    name: 'track.order',
    component: () => import('@/views/OrderTrackingView.vue'),
  },
  {
    path: '/appointments',
    name: 'appointments',
    component: () => import('@/views/AppointmentsView.vue'),
    meta: { title: 'Private Salon Appointments — Éclore' },
  },
  {
    path: '/boutiques',
    name: 'boutiques',
    component: () => import('@/views/AppointmentsView.vue'),
    meta: { title: 'Boutique Salons — Éclore' },
  },
  {
    path: '/journal',
    name: 'journal',
    component: () => import('@/views/JournalView.vue'),
    meta: { title: 'The Journal & Maison Heritage — Éclore' },
  },
  {
    path: '/journal/:slug',
    name: 'journal.story',
    component: () => import('@/views/StoryDetailView.vue'),
  },
  {
    path: '/stories',
    redirect: '/journal',
  },
  {
    path: '/stories/:slug',
    redirect: (to) => `/journal/${to.params.slug}`,
  },
  {
    path: '/terms',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Terms of Service — Éclore' },
  },
  {
    path: '/terms-of-service',
    redirect: '/terms',
  },
  {
    path: '/privacy',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Privacy Policy — Éclore' },
  },
  {
    path: '/privacy-policy',
    redirect: '/privacy',
  },
  {
    path: '/conditions-of-sale',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Conditions of Sale — Éclore' },
  },
  {
    path: '/care-guide',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Jewellery Care Guide — Éclore' },
  },
  {
    path: '/care',
    redirect: '/care-guide',
  },
  {
    path: '/sustainability',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Maison Responsibility & Ethics — Éclore' },
  },
  {
    path: '/corporate-responsibility',
    redirect: '/sustainability',
  },
  {
    path: '/help',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Client Care & Concierge — Éclore' },
  },
  {
    path: '/accessibility',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Accessibility Statement — Éclore' },
  },
  {
    path: '/orders-payments',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Orders & Payments — Éclore' },
  },
  {
    path: '/cookie-policy',
    component: () => import('@/views/CmsPageView.vue'),
    meta: { title: 'Cookie Policy — Éclore' },
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('@/views/ContactView.vue'),
    meta: { title: 'Contact Us — Éclore' },
  },
  {
    path: '/vip-club',
    name: 'vip.club',
    component: () => import('@/views/VipClubView.vue'),
    meta: { title: 'VIP Club & Private Circle — Éclore' },
  },
  {
    path: '/vip',
    redirect: '/vip-club',
  },
  {
    path: '/page/:slug',
    component: () => import('@/views/CmsPageView.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;
