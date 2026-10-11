# Deliverable 02: Éclore Ionic Frontend Architecture & System Design

**Project:** Éclore Luxury Jewellery E-Commerce Platform  
**Target Migration:** Laravel 12 Backend API + Ionic Framework Frontend  
**Architecture Document Version:** 1.0.0-final  
**Scope:** Customer Storefront (Phase 1 Target) with Extensibility for Admin Backoffice  

---

## 1. Executive Framework Selection & Trade-Off Analysis

An architectural evaluation of the three official Ionic framework bindings (**Ionic Vue**, **Ionic React**, and **Ionic Angular**) was conducted against the specific characteristics of the Éclore codebase:

```
+--------------------------------------------------------------------------------------------------+
|                                    FRAMEWORK EVALUATION MATRIX                                   |
+---------------------+-----------------------+-----------------------+----------------------------+
| Evaluation Criteria | Ionic Vue 3           | Ionic React           | Ionic Angular              |
+---------------------+-----------------------+-----------------------+----------------------------+
| Codebase Affinity   | EXCELLENT             | MODERATE              | STEEP                      |
|                     | Direct conceptual     | Requires converting   | Heavy OOP, RxJS, and       |
|                     | match with existing   | templates to JSX and  | Services required; distant |
|                     | Alpine.js directives  | hook paradigms        | from Blade template model  |
+---------------------+-----------------------+-----------------------+----------------------------+
| State Management    | Pinia (concise,       | Zustand / Redux       | NgRx / Signals             |
|                     | reactive, type-safe)  | (state boilerplate)   | (complex ceremony)         |
+---------------------+-----------------------+-----------------------+----------------------------+
| Styling & Tailwind  | NATIVE                | NATIVE                | MODERATE                   |
| Integration         | Scoped CSS + Tailwind | Tailwind + PostCSS    | Component style isolation  |
+---------------------+-----------------------+-----------------------+----------------------------+
| Bundle Size & Perf  | Lightweight, fast     | Moderate bundle size  | Largest initial footprint  |
|                     | virtual DOM & SFCs    |                       |                            |
+---------------------+-----------------------+-----------------------+----------------------------+
| Mobile & Capacitor  | First-class Ionic 8   | First-class Ionic 8   | First-class Ionic 8        |
| Maturity            | support               | support               | support                    |
+---------------------+-----------------------+-----------------------+----------------------------+
```

### 1.1 Recommended Primary Architecture: **Ionic Vue (Vue 3 + TypeScript + Pinia + Vite)**
- **Rationale:**
  1. **Blade & Alpine.js Synergy:** The current customer experience heavily relies on Alpine.js directives (`x-data`, `x-show`, `x-if`, `x-cloak`, `@click`). Vue 3 Single File Components (`.vue`) provide the exact same mental model (`v-if`, `v-show`, `v-model`, `@click`), dramatically reducing cognitive overhead, translation bugs, and refactoring time.
  2. **Pinia State Store:** Pinia stores (`useCartStore`, `useAuthStore`, `useWishlistStore`) seamlessly replicate the centralized JavaScript managers in `public/frontend/js/{auth,api,payment-methods,checkout}.js`.
  3. **Build Tooling Alignment:** The current project already uses Vite (`vite.config.js`). Ionic Vue uses Vite natively, allowing direct reuse of Vite configuration, plugins, asset resolvers, and aliases.

### 1.2 First-Class Alternative: **Ionic React (React 18/19 + TypeScript + Zustand + Vite)**
- If team skill sets or organizational project requirements dictate React, the component architecture and data contracts specified in this document map 1:1 to React functional components and hooks (`useCart()`, `useAuth()`).

### 1.3 Deployment Target: **Android Mobile via Capacitor & Android Studio (with PWA Support)**
- **Confirmed Primary Platform:** **Android Application** built with `@capacitor/core` and `@capacitor/android`, opened, debugged, and built via **Android Studio**.
- **Android-Specific Architectural Requirements:**
  1. **Network Origin & Local Dev Routing:** In the Android Emulator, the host machine's `localhost` is mapped to `http://10.0.2.2:8000`. The Axios API client environment config dynamically supports `10.0.2.2:8000` for emulation and local LAN IP (`192.168.x.x`) for physical Android devices.
  2. **Cleartext Traffic for Local Testing:** `AndroidManifest.xml` will be configured with `android:usesCleartextTraffic="true"` during development so the WebView can communicate with local HTTP endpoints.
  3. **Hardware Back Button Navigation:** The Ionic router explicitly binds to Capacitor's `App.addListener('backButton')` to dismiss open modals/drawers first, navigating back through history before minimizing the app.
  4. **Deep-Linking for Payment Return:** An Android `<intent-filter>` is registered with `<data android:scheme="eclore" />` so Xendit payment callbacks return directly into the Android application.
  5. **Android Studio Integration:** Direct workflow support for `npx cap sync android` and `npx cap open android` to test directly on Android Studio Virtual Devices (AVD) and physical Android phones via USB debugging.

---

## 2. Target Application Directory Topography

The proposed Ionic project will reside in a dedicated directory (e.g. `frontend-ionic/` or a sibling repository) consuming the Laravel backend via standard REST endpoints:

```
frontend-ionic/
|-- android/                          # Native Android Capacitor wrapper
|-- ios/                              # Native iOS Capacitor wrapper
|-- public/
|   |-- assets/
|   |   |-- fonts/                    # Playfair Display, Azeret Mono, Cormorant Garamond, Inter
|   |   |-- images/                   # Editorial jewelry assets, badges, hallmark icons
|   |   `-- video/                    # hero-jewelry.mp4
|   `-- favicon.png
|-- src/
|   |-- api/                          # HTTP client and endpoint service definitions
|   |   |-- client.ts                 # Axios instance with auth/guest interceptors
|   |   |-- auth.api.ts               # Login, register, magic-link, 2FA, password-reset
|   |   |-- products.api.ts           # Catalog, filtering, search, reviews
|   |   |-- cart.api.ts               # Guest/user cart operations, item selection
|   |   |-- wishlist.api.ts           # Wishlist toggles and migration
|   |   |-- checkout.api.ts           # Shipping calculation, order creation, payment validation
|   |   |-- orders.api.ts             # Tracking, history, receipt download
|   |   |-- account.api.ts            # Profile update, Philippine address management, RMA
|   |   `-- psgc.api.ts               # Philippine PSGC cascading address lookup
|   |-- components/                   # Reusable UI component library
|   |   |-- common/
|   |   |   |-- EcloreHeader.vue      # Two-tier luxury topbar & brand wordmark
|   |   |   |-- EcloreFooter.vue      # Editorial footer with newsletter signup
|   |   |   |-- EcloreButton.vue      # High-jewelry tactile button with gold foil accents
|   |   |   |-- EcloreModal.vue       # Glassmorphism modal base wrapper
|   |   |   |-- EcloreToast.vue       # Editorial discreet toast alerts
|   |   |   `-- CurrencyDisplay.vue   # Strict Philippine Peso (PHP ₱) formatting
|   |   |-- catalog/
|   |   |   |-- ProductCard.vue       # Hover reveal, quick-add, wishlist toggle
|   |   |   |-- ProductGallery.vue    # Zoomable carousel, thumbnail strip, lightbox
|   |   |   |-- FilterDrawer.vue      # Slide-over facet filtering (gemstone, gold, price)
|   |   |   `-- QuickViewModal.vue    # Full-featured PDP quick modal
|   |   |-- cart/
|   |   |   |-- CartDrawer.vue        # Offcanvas cart drawer with checkbox selection
|   |   |   `-- CartItemRow.vue       # Quantity controls, material specs, delete
|   |   |-- checkout/
|   |   |   |-- StepProgressBar.vue   # 4-stage acquisition progress indicator
|   |   |   |-- PsgcAddressForm.vue   # Cascading Region -> Province -> City -> Barangay
|   |   |   `-- OrderSummaryCard.vue  # Sticky acquisition breakdown & VAT display
|   |   `-- reviews/
|   |       |-- ReviewCard.vue        # Star rating, verified purchase badge, photo gallery
|   |       `-- ReviewSubmitModal.vue # Rating submission with photo upload
|   |-- stores/                       # Pinia state stores
|   |   |-- auth.store.ts             # User state, Sanctum Bearer tokens, permissions
|   |   |-- cart.store.ts             # Cart state, guest session ID, selection array
|   |   |-- wishlist.store.ts         # Synchronized wishlist items
|   |   |-- checkout.store.ts         # Active checkout step, shipping/billing data
|   |   `-- notification.store.ts     # User alert inbox and unread badge count
|   |-- views/                        # Ionic Screen Pages (IonPage)
|   |   |-- tabs/
|   |   |   |-- TabsLayout.vue        # Bottom navigation tabs for mobile viewports
|   |   |   |-- HomeTab.vue           # Editorial hero, bento grid, appointments
|   |   |   |-- CatalogueTab.vue      # Infinite-scroll / paginated jewellery catalog
|   |   |   |-- CollectionsTab.vue    # High Jewelry lookbook & signature sets
|   |   |   `-- AccountTab.vue        # Customer dashboard, orders, addresses, security
|   |   |-- ProductDetailView.vue     # Complete PDP with reviews and size guide
|   |   |-- CheckoutView.vue          # Multi-step checkout container (Shipping, Payment, Review)
|   |   |-- OrderConfirmationView.vue # Success/failure status & Xendit popup handler
|   |   |-- OrderTrackingView.vue     # Public or authenticated parcel progress tracker
|   |   |-- JournalArticleView.vue    # CMS editorial article with Table of Contents
|   |   `-- BoutiqueAppointmentsView.vue # Salon booking interface
|   |-- router/
|   |   `-- index.ts                  # Vue Router configuration with Ionic transitions
|   |-- theme/
|   |   |-- variables.css             # Éclore brand tokens mapped to Ionic CSS variables
|   |   `-- typography.css            # Playfair Display, Azeret Mono, and Cormorant Garamond
|   |-- services/
|   |   |-- storage.service.ts        # Capacitor Preferences / SecureStorage abstraction
|   |   |-- deep-link.service.ts      # URL scheme listener for Xendit payment returns
|   |   `-- haptics.service.ts        # Subtle tactile feedback on jewelry interactions
|   |-- App.vue                       # Root component with IonApp and global drawers
|   `-- main.ts                       # Entry point, IonicVue initialization, Pinia setup
|-- capacitor.config.ts               # Capacitor native configuration
|-- tailwind.config.js                # Tailwind CSS v4 design tokens configuration
|-- vite.config.ts                    # Vite configuration
`-- package.json
```

---

## 3. Ionic Routing, Navigation & Viewport Adaptation

### 3.1 Adaptive Desktop & Mobile Navigation Layout
The customer storefront must gracefully adapt between desktop wide screens (where Éclore's luxurious two-tier header and mega-menus shine) and mobile touchscreens (where bottom tabs and slide-over drawers provide native fluidity).

```
                      +---------------------------------------+
                      |         Viewport Breakpoint           |
                      +---------------------------------------+
                                          |
                     +--------------------+--------------------+
                     |                                         |
                     v                                         v
        [ Desktop View (>= 1024px) ]              [ Mobile View (< 1024px) ]
+------------------------------------------+  +--------------------------------+
| Top Utility Bar (Currency, Search, Pins) |  | Compact Header (Logo, Drawer)  |
+------------------------------------------+  +--------------------------------+
| Brand Logo + Main Links + Mega Menus     |  | Active Screen View (IonRouter) |
+------------------------------------------+  +--------------------------------+
| Active Screen View (Router View)         |  | Bottom Tab Bar                 |
+------------------------------------------+  | [Home] [Catalog] [Bag] [Account|
| Editorial Footer                         |  +--------------------------------+
+------------------------------------------+
```

### 3.2 Route Definitions (`src/router/index.ts`)
```typescript
import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth.store';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    component: () => import('@/views/tabs/TabsLayout.vue'),
    children: [
      { path: '', redirect: '/home' },
      { path: 'home', name: 'home', component: () => import('@/views/tabs/HomeTab.vue') },
      { path: 'catalogue', name: 'catalogue', component: () => import('@/views/tabs/CatalogueTab.vue') },
      { path: 'collections', name: 'collections', component: () => import('@/views/tabs/CollectionsTab.vue') },
      { 
        path: 'account', 
        name: 'account', 
        component: () => import('@/views/tabs/AccountTab.vue'),
        meta: { requiresAuth: true }
      },
    ]
  },
  {
    path: '/products/:slug',
    name: 'product.detail',
    component: () => import('@/views/ProductDetailView.vue'),
    props: true
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: () => import('@/views/CheckoutView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/checkout/confirmation/:orderNumber',
    name: 'checkout.confirmation',
    component: () => import('@/views/OrderConfirmationView.vue'),
    props: true
  },
  {
    path: '/track-order',
    name: 'track-order',
    component: () => import('@/views/OrderTrackingView.vue')
  },
  {
    path: '/journal/:slug',
    name: 'journal.article',
    component: () => import('@/views/JournalArticleView.vue'),
    props: true
  },
  {
    path: '/boutiques-appointments',
    name: 'appointments',
    component: () => import('@/views/BoutiqueAppointmentsView.vue')
  },
  // Catch-all 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue')
  }
];

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

// Global Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Save intended destination for post-login redirect
    authStore.setIntendedRoute(to.fullPath);
    authStore.openLoginModal();
    return next(false);
  }
  next();
});
```

---

## 4. Design System Preservation & Ionic Theme Mapping

To ensure high-fidelity visual parity with the Éclore luxury brand identity, Éclore design tokens are directly mapped to Ionic CSS variables in `src/theme/variables.css`:

```css
/**
 * Éclore High Jewellery — Design Tokens & Ionic CSS Mapping
 */
:root {
  /* Éclore Brand Palette */
  --eclore-cream: #F3EFE7;          /* Primary editorial canvas */
  --eclore-cream-light: #F9F8F6;    /* Surface background */
  --eclore-charcoal: #1A1A1A;       /* Primary ink typography */
  --eclore-taupe: #6E6A64;          /* Secondary metadata text */
  --eclore-gold: #B6965D;           /* Signature Champagne Atelier Gold */
  --eclore-gold-dark: #8B7355;      /* Deep antique bronze accent */
  --eclore-sand: #EAE6DF;           /* Subtle 1px borders */
  --eclore-white: #FFFFFF;          /* Pure card surface */
  --eclore-error: #9B2C2C;          /* Muted burgundy error */

  /* Typography Families */
  --font-serif: 'Playfair Display', Georgia, serif;
  --font-editorial: 'Cormorant Garamond', Garamond, serif;
  --font-mono: 'Azeret Mono', 'Courier New', monospace;
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

  /* Ionic Core Palette Overrides */
  --ion-background-color: var(--eclore-cream);
  --ion-text-color: var(--eclore-charcoal);
  --ion-font-family: var(--font-sans);

  --ion-color-primary: var(--eclore-charcoal);
  --ion-color-primary-rgb: 26, 26, 26;
  --ion-color-primary-contrast: var(--eclore-white);

  --ion-color-secondary: var(--eclore-gold);
  --ion-color-secondary-rgb: 182, 150, 93;
  --ion-color-secondary-contrast: var(--eclore-white);

  --ion-item-background: var(--eclore-white);
  --ion-card-background: var(--eclore-white);
  --ion-border-color: var(--eclore-sand);

  /* Touch Target & Mobile Conventions */
  --ion-safe-area-top: env(safe-area-inset-top);
  --ion-safe-area-bottom: env(safe-area-inset-bottom);
}

/* Luxury Editorial Component Classes */
.font-playfair {
  font-family: var(--font-serif);
}

.font-azeret {
  font-family: var(--font-mono);
  letter-spacing: 0.15em;
  text-transform: uppercase;
}

.btn-eclore-gold {
  background-color: var(--eclore-gold);
  color: #FFFFFF;
  font-family: var(--font-mono);
  font-size: 0.75rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  border-radius: 0px;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-eclore-gold:hover {
  background-color: var(--eclore-gold-dark);
}
```

---

## 5. Authentication Architecture: PWA vs. Native Mobile

Because the current backend uses stateful session cookies (`laravel_session`) and CSRF tokens, a hybrid client (especially running on `capacitor://localhost` on iOS or `http://localhost:8100` in dev) requires a decoupled authentication mechanism.

```
                      +---------------------------------------+
                      |         Target Client Origin          |
                      +---------------------------------------+
                                          |
                     +--------------------+--------------------+
                     |                                         |
                     v                                         v
         [ Web / PWA Browser ]                     [ Native iOS / Android ]
      (Origin: eclorejewellery.shop)               (Origin: capacitor://localhost)
+---------------------------------------+  +---------------------------------------+
| Transport: First-Party Cookie / Token |  | Transport: Authorization: Bearer <T>  |
| CSRF: Handled via Sanctum             |  | CSRF: Not required for Bearer auth    |
| Storage: HttpOnly Session Cookie      |  | Storage: Capacitor Preferences /      |
|                                       |  |          SecureStorage (AES-256)      |
+---------------------------------------+  +---------------------------------------+
                     |                                         |
                     +--------------------+--------------------+
                                          |
                                          v
                      +---------------------------------------+
                      |  Laravel 12 API (Sanctum Middleware)  |
                      |  - Validates Bearer Token or Cookie   |
                      |  - Authorizes User via 'web' Guard    |
                      +---------------------------------------+
```

### 5.1 Token Storage Security
- **Web / PWA:** Uses standard secure session storage or encrypted memory token.
- **Native Capacitor:** In accordance with prompt requirements, long-lived access tokens are **never stored in unencrypted browser `localStorage`**. Instead, `@capacitor/preferences` with native biometric/keychain backing (`@aparajita/capacitor-secure-storage` or `Capacitor Secure Storage`) is used to store the token in the iOS Keychain and Android EncryptedSharedPreferences.

### 5.2 API Interceptor & Client Design (`src/api/client.ts`)
```typescript
import axios, { AxiosRequestConfig, AxiosResponse, AxiosError } from 'axios';
import { useAuthStore } from '@/stores/auth.store';
import { useCartStore } from '@/stores/cart.store';

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15000,
});

// Request Interceptor: Inject Auth Token & Guest Session ID
apiClient.interceptors.request.use(async (config) => {
  const authStore = useAuthStore();
  const cartStore = useCartStore();

  // 1. Inject Sanctum Bearer Token if authenticated
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }

  // 2. Inject Guest Session Identifier for anonymous cart/wishlist tracking
  if (!authStore.isAuthenticated && cartStore.guestSessionId) {
    config.headers['X-Guest-Session-Id'] = cartStore.guestSessionId;
  }

  return config;
}, (error) => Promise.reject(error));

// Response Interceptor: Handle Token Expiry & Sanitization
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    // Security defense-in-depth: strip any residual backend debug payload
    if (response.data && response.data.debug) {
      delete response.data.debug;
    }
    return response;
  },
  async (error: AxiosError) => {
    const authStore = useAuthStore();
    
    if (error.response?.status === 401) {
      // Token expired or revoked
      await authStore.handleSessionExpired();
    }
    return Promise.reject(error);
  }
);
```

---

## 6. Guest-to-Authenticated Cart & Wishlist Strategy

Preserving anonymous shopper selections without data loss upon login is critical for e-commerce conversion.

### 6.1 Guest Lifecycle & Storage
1. When an unauthenticated customer visits the app, the frontend checks for an existing `guest_session_id` in storage. If none exists, it generates a cryptographically random UUID v4 (`crypto.randomUUID()`) and saves it.
2. Every cart/wishlist operation sends this UUID in the `X-Guest-Session-Id` header.
3. The Laravel backend verifies this UUID against `guest_sessions` table and queries `cart_items` where `session_id = ?` and `user_id IS NULL`.

### 6.2 Transactional Account Migration Sequence
```mermaid
sequenceDiagram
    autonumber
    actor Customer
    participant Ionic as Ionic App
    participant API as Laravel 12 API
    participant DB as Database (SQLite/MySQL)

    Customer->>Ionic: Adds Jewellery (Guest Cart)
    Ionic->>API: POST /api/cart/add [Header: X-Guest-Session-Id: UUID]
    API->>DB: INSERT into cart_items (session_id=UUID, user_id=NULL)
    DB-->>API: Saved
    API-->>Ionic: Cart Updated

    Customer->>Ionic: Clicks "Proceed to Acquisition" & Enters Login Credentials
    Ionic->>API: POST /api/login { email, password }
    API->>DB: Verify credentials & generate Sanctum Token
    DB-->>API: User Record
    API-->>Ionic: 200 OK { token, user }

    Note over Ionic,API: Automatic Post-Auth Migration
    Ionic->>API: POST /api/cart/migrate [Header: Bearer Token, Body: { guest_session_id: UUID }]
    API->>DB: BEGIN TRANSACTION
    API->>DB: MERGE cart_items WHERE session_id=UUID INTO user_id=User.id
    API->>DB: DELETE FROM guest_sessions WHERE session_id=UUID
    API->>DB: COMMIT TRANSACTION
    DB-->>API: Success
    API-->>Ionic: 200 OK { migrated_items: 2, subtotal: 85000 }

    Ionic->>API: POST /api/wishlist/migrate { guest_session_id: UUID }
    API->>DB: MERGE wishlist_items WHERE session_id=UUID INTO user_id=User.id
    DB-->>API: Success
    API-->>Ionic: 200 OK { success: true }
    Ionic->>Ionic: Clear local guest_session_id; switch to user state
```

---

## 7. Multi-Step Checkout & Philippine PSGC Address Architecture

The checkout experience preserves the exact 4-stage acquisition flow from `resources/views/checkout/`:

```
[ Step 1: Shipping Address ] ---> [ Step 2: Payment Method ] ---> [ Step 3: Order Review ] ---> [ Step 4: Acquisition Confirm ]
 (PSGC Cascading Picker)          (COD / Saved / Xendit)          (12% VAT, Currency: PHP)       (Xendit Invoice / Deep-Link)
```

### 7.1 Philippine Standard Geographic Code (PSGC) Implementation
In the current Blade views, PSGC address cascading is fetched client-side from `https://psgc.cloud/api/v2/`.
In the Ionic application:
1. **Component:** `PsgcAddressPicker.vue` manages 4 select fields (`Region` -> `Province` -> `City/Municipality` -> `Barangay`).
2. **NCR Handling:** If Region is NCR (`1300000000`), the Province selector is automatically hidden, and the City selector loads municipalities directly from `/regions/1300000000/cities-municipalities`.
3. **Resilience & Offline Fallback:** A local JSON lookup dictionary containing all 17 Philippine regions and NCR cities is packaged in `src/assets/data/psgc-fallback.json` to prevent checkout blocking if `psgc.cloud` experiences downtime or network latency.

### 7.2 Strict Currency Formatting
- **Defect Remediation:** As discovered in Deliverable 01, `checkout/layout.blade.php` mistakenly renders prices in Euros (`€`). The Ionic app strictly formats all monetary amounts in **Philippine Pesos (`₱` / `PHP`)** via an authoritative utility:
  ```typescript
  export const formatPhp = (amount: number): string => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 2,
    }).format(amount);
  };
  ```

---

## 8. Payment Handling, Return Flows & Native Deep Linking

### 8.1 Xendit Flow: Web vs. Capacitor Native
- **Web / PWA:**
  1. Frontend submits order via `POST /api/checkout/process`.
  2. Backend creates pending `Order` and calls Xendit API to generate invoice URL.
  3. Frontend redirects customer to Xendit hosted invoice.
  4. Upon payment completion, Xendit redirects browser to `https://eclorejewellery.shop/payments/xendit/return/success?order=ORD-...`.
- **Capacitor Mobile (iOS / Android):**
  1. Frontend calls `@capacitor/browser` to open the Xendit invoice URL in an in-app browser tab (`Browser.open({ url: invoiceUrl })`).
  2. Custom URL scheme is configured in `capacitor.config.ts`: `eclore://`.
  3. Success return URL provided to Xendit: `eclore://payments/xendit/return/success?order=ORD-...`.
  4. Capacitor listens for app URL open events:
     ```typescript
     import { App } from '@capacitor/app';
     import { Browser } from '@capacitor/browser';
     import { router } from '@/router';

     App.addListener('appUrlOpen', async (data) => {
       if (data.url.startsWith('eclore://payments/xendit/return/success')) {
         await Browser.close();
         const url = new URL(data.url);
         const orderNumber = url.searchParams.get('order');
         router.push({ name: 'checkout.confirmation', params: { orderNumber } });
       } else if (data.url.startsWith('eclore://payments/xendit/return/failed')) {
         await Browser.close();
         router.push({ name: 'checkout.payment', query: { retry: 'true' } });
       }
     });
     ```

### 8.2 Order Status Reconciliation & Polling
On the confirmation screen (`OrderConfirmationView.vue`), the app polls `GET /api/orders/{orderNumber}/status` every 3 seconds (up to 30 seconds) until the webhook sets `payment_status: 'paid'`. Once paid, confetti animation triggers, and the local cart store is cleared.

---

## 9. Media & Asset Pipeline Strategy

### 9.1 Asset Inventory & Local Bundling
All 18 high-resolution luxury assets verified in `public/frontend/assets/` (`hero-jewelry.mp4`, `category-rings.webp`, `category-necklaces.webp`, `craftsmanship-ring.webp`, etc.) will be bundled directly into `public/assets/` in the Ionic project. This guarantees instant rendering of hero branding and categories, even offline.

### 9.2 Product Catalog Media
1. Seeded products reference external Unsplash URLs. The image component `EcloreImage.vue` implements lazy loading, skeleton shimmer loading states, and automatic fallback to `assets/images/placeholder-jewelry.webp` if an image fails to resolve.
2. In production, image uploads are served from Laravel's `/storage/products/` or AWS S3 CDN. The API returns full absolute URLs (`asset_url`) generated by `Storage::url()`.

---

## 10. Third-Party Integrations & Scope Boundaries

| Third-Party Integration | PWA / Web Treatment | Capacitor Native Treatment | Status & Fallback |
|---|---|---|---|
| **Noupe AI Chatbot** | Embedded via script tag in `index.html` or conditional iframe. | Rendered inside an in-app WebView modal or linked out via `@capacitor/browser`. | Optional in Phase 1; can be toggled via remote config. |
| **Google OAuth SSO** | Standard OAuth redirect via Laravel Socialite. | `@codetrix-studio/capacitor-google-auth` native plugin passing Google ID token to `/api/auth/google/token`. | Fallback to email/password authentication if Google keys are absent. |
| **Pusher Realtime** | `laravel-echo` + `pusher-js` WebSocket client for order notifications. | WebSockets over cellular/Wi-Fi or native push notifications via `@capacitor/push-notifications`. | Fallbacks to HTTP polling when Pusher keys are unset. |
| **DomPDF Receipt** | Direct PDF download link (`GET /api/account/receipt/{orderNumber}/download`). | Downloaded via `@capacitor/filesystem` and viewed via `@capacitor/action-sheet` or native share sheet. | Ready; reuses Laravel DomPDF engine. |

---

## 11. Preserving Authoritative Backend Responsibilities

The Ionic application is strictly an **interface presentation client**. Under no circumstances will business calculations or authoritative state mutations be delegated to frontend code:

1. **Pricing & VAT Calculation:** The frontend displays prices and estimated subtotals, but the backend `CheckoutController` is the **sole authority** for item line totals, discounts, shipping fees, and 12% Philippine VAT.
2. **Inventory Decrement:** Stock is decremented **only on the backend** inside database transactions (`InventoryMovement::recordStockOut`) upon verified payment or COD order confirmation.
3. **Order State Transitions:** State transitions (`pending` -> `processing` -> `shipped` -> `delivered` -> `refunded`) are protected by backend policies and RBAC middleware.
4. **Zero Direct Database Connections:** The Ionic application possesses **no database drivers, credentials, or SQL query capabilities**. All interactions route through typed HTTP API contracts.
