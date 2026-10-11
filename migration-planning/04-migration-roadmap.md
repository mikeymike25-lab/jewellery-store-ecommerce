# Deliverable 04: Sequenced Migration Roadmap & Effort Estimation

**Project:** Éclore Luxury Jewellery E-Commerce Platform  
**Target Migration:** Laravel 12 Monolith → Laravel 12 API + Ionic Hybrid Frontend  
**Document Version:** 1.1.0-checklist  
**Estimation Units:** Effort Bands & Developer-Days (Assumes 1 Senior Full-Stack Engineer + 1 Frontend Ionic/Mobile Engineer)  

---

## 1. Master Phase Completion Checklist

Use this high-level dashboard to monitor project progression at a glance:

- [x] **Phase 0:** Environment Setup & Backend Defect Remediation *(Days 1 – 4)*
- [ ] **Phase 1:** API Normalization, Sanctum Auth & Security Hardening *(Days 5 – 9)*
- [ ] **Phase 2:** Ionic Shell, Luxury Design System & Core Components *(Days 8 – 14)*
- [ ] **Phase 3:** Product Catalog, Faceted Filtering, Gallery & PDP *(Days 15 – 21)*
- [ ] **Phase 4:** Shopping Bag, Guest Sessions & Wishlist Sync *(Days 22 – 27)*
- [ ] **Phase 5:** Multi-Step Checkout, Philippine PSGC & Xendit Payments *(Days 28 – 36)*
- [ ] **Phase 6:** Customer Account Dashboard, Order Tracking & RMA *(Days 37 – 43)*
- [ ] **Phase 7:** Journal/CMS, Appointments & Institutional Pages *(Days 42 – 47)*
- [ ] **Phase 8:** End-to-End QA, Security Audit & Staged Cutover *(Days 48 – 55)*

---

## 2. Detailed Task Checklists by Phase

### Phase 0: Environment Setup & Backend Defect Remediation
- **Goal:** Eliminate existing database schema mismatches, repair broken SQL queries, and ensure 100% SQLite/MySQL cross-compatibility before building frontend consumers.
- **Dependencies:** None.
- **Effort Range:** 3 – 4 Developer-Days
- **Tasks Checklist:**
  - [x] **0.1 Remove `room_category` queries:** Eliminate legacy furniture queries from `ProductController.php:23-30`, `routes/api.php:59-64`, and `routes/web.php:500-507`.
  - [x] **0.2 Fix SQLite `MONTH()` queries:** Update `Admin\DashboardController.php:104, 110` and `AnalyticsController.php:747` to use driver-agnostic date expressions (`strftime` on SQLite, `MONTH` on MySQL).
  - [x] **0.3 Fix `Admin\MessageController` table name:** Change validation rules in `MessageController.php:87, 139` from `exists:admins,id` to `exists:employees,id`.
  - [x] **0.4 Clean `User::$fillable`:** Align `app/Models/User.php` with `users` schema; add `tags`, `admin_notes`; remove non-existent US address fields and remove invalid `marketing_emails` insert in `AuthController.php:56`.
  - [x] **0.5 Add `Order::items()` alias:** Add `public function items() { return $this->orderItems(); }` in `app/Models/Order.php` to prevent `RelationNotFoundException` in order export and PDF invoice generation.
  - [x] **0.6 Clean orphan `Cart` model & `/api/cart/cleanup`:** Delete orphan `app/Models/Cart.php` and rewrite `/api/cart/cleanup` to target `guest_sessions` and `cart_items`.
  - [x] **0.7 Fix seeders:** Resolve duplicate unique key constraint in `WishlistItemSeeder.php:19` and add `xendit` gateway to `PaymentGatewaySeeder.php`.
- **Exit & Acceptance Criteria:**
  - [x] `php artisan migrate:fresh --seed` runs cleanly on both SQLite and MySQL with 0 errors.
  - [x] All existing PHPUnit tests pass.

---

### Phase 1: API Normalization, Sanctum Auth & Security Hardening
- **Goal:** Provide a secure, stateless, versioned REST API (`/api/v1/*`) decoupled from browser session cookies.
- **Dependencies:** Phase 0 complete.
- **Effort Range:** 4 – 5 Developer-Days
- **Tasks Checklist:**
  - [x] **1.1 Install Laravel Sanctum:** Run `composer require laravel/sanctum` and add `HasApiTokens` trait to `App\Models\User`.
  - [x] **1.2 Update CORS configuration:** Update `config/cors.php` to allow `http://localhost:8100`, `capacitor://localhost`, and `ionic://localhost` with `supports_credentials: true`.
  - [x] **1.3 Sanitize CartController debug leak:** Remove `$request->cookies->all()` and `session_cookie` from `CartController.php:64-71, 176-184`.
  - [x] **1.4 Consolidate & version API routes:** Deduplicate overlapping routes between `routes/web.php` and `routes/api.php` under standardized `/api/v1/*` routes.
  - [x] **1.5 Implement Sanctum auth endpoints:** Build `POST /api/v1/auth/token`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/user`.
  - [x] **1.6 Implement guest cart header:** Add `X-Guest-Session-Id` header resolution middleware for anonymous shopper tracking.
  - [x] **1.7 Fix Xendit double stock decrement:** Wrap status update in `XenditPaymentController.php` with `lockForUpdate()` and verify `payment_status !== 'paid'` before recording stock out.
  - [x] **1.8 Fix Notification query scope:** Add parameter grouping to `NotificationController.php` so `recipient_type = 'all'` does not corrupt single-user notification queries.
- **Exit & Acceptance Criteria:**
  - [x] Automated API test verifies Sanctum Bearer token issuance and 401 on missing token.
  - [x] Cart response returns zero cookie strings or debug payloads.

---

### Phase 2: Ionic Shell, Luxury Design System & Core Components
- **Goal:** Scaffold the Ionic Vue application, establish typography and brand color tokens, and build reusable UI primitives.
- **Dependencies:** Can run in parallel with Phase 1.
- **Effort Range:** 5 – 7 Developer-Days
- **Tasks Checklist:**
  - [x] **2.1 Initialize Ionic Vue project:** Scaffold `frontend-ionic/` with Vue 3, TypeScript, Vite, and Tailwind CSS v4.
  - [x] **2.2 Map Éclore luxury tokens:** Port `#F3EFE7` cream, `#B6965D` gold, `#1A1A1A` ink, and typography (`Playfair Display`, `Azeret Mono`, `Inter`) into `src/theme/variables.css`.
  - [x] **2.3 Bundle local media assets:** Bundle all 18 verified local luxury assets from `public/frontend/assets/` into `frontend-ionic/public/assets/`.
  - [x] **2.4 Build core UI components:** Create `EcloreHeader`, `EcloreFooter`, `EcloreButton`, `EcloreModal`, and `CurrencyDisplay` (PHP `₱`).
  - [x] **2.5 Configure Pinia & Axios client:** Setup root Pinia stores and Axios client interceptors for Bearer auth and `X-Guest-Session-Id`.
  - [x] **2.6 Implement adaptive navigation:** Create desktop two-tier navigation and mega-menus with mobile bottom `ion-tab-bar` adaptation.
- **Exit & Acceptance Criteria:**
  - [x] Visual baseline tests confirm fonts, color swatches, button states, and header layout match existing web storefront.
  - [x] Zero CDN dependencies (Google Fonts and Lucide Icons bundled locally).

---

### Phase 3: Product Catalog, Faceted Filtering, Gallery & PDP
- **Goal:** Deliver the complete product browsing, search, and luxury detail experience.
- **Dependencies:** Phase 1 and Phase 2 complete.
- **Effort Range:** 6 – 7 Developer-Days
- **Tasks Checklist:**
  - [x] **3.1 Build `HomeTab.vue`:** Implement hero video banner, scrolling brand marquee, 4-card Bento grid, curated product carousel, and Maison heritage teaser.
  - [x] **3.2 Build `CatalogueTab.vue`:** Implement 4-column product grid (2-column on mobile), sorting dropdown, and infinite scroll.
  - [x] **3.3 Build `FilterDrawer.vue`:** Implement slide-over drawer with facet checkboxes for Gold Material, Diamond Carat, Gemstones, and Price Range.
  - [x] **3.4 Build `ProductDetailView.vue`:** Implement fullscreen zoomable image gallery, thumbnail strip, SKU, star rating, specifications pills, ring size selector, and size guide modal.
  - [x] **3.5 Build `SearchModal.vue`:** Implement debounced autocomplete search against `/api/v1/search` with recent search tags.
  - [x] **3.6 Build `ReviewsSection.vue`:** Implement customer reviews display with star distribution bars and photo preview modal.
- **Exit & Acceptance Criteria:**
  - [x] Catalog filtering updates results dynamically in <300ms.
  - [x] Product gallery allows smooth swipe gestures and pinch-to-zoom on touch devices.

---

### Phase 4: Shopping Bag, Guest Sessions & Wishlist Sync
- **Goal:** Implement the shopping bag and wishlist with seamless guest-to-authenticated account migration.
- **Dependencies:** Phase 1 and Phase 3 complete.
- **Effort Range:** 5 – 6 Developer-Days
- **Tasks Checklist:**
  - [x] **4.1 Implement `useCartStore`:** Manage `guestSessionId`, item quantities, and selected items with local persistence.
  - [x] **4.2 Build `CartDrawer.vue`:** Implement offcanvas bag with per-item selection checkboxes (`.cart-item-checkbox`) and live subtotal calculation.
  - [x] **4.3 Implement `useWishlistStore` & drawer:** Build one-tap heart toggle with instant badge counter updates.
  - [x] **4.4 Implement post-login cart migration:** Dispatch `POST /api/v1/cart/migrate` and `POST /api/v1/wishlist/migrate` upon login to merge guest items into user account.
  - [x] **4.5 Implement "Buy Now" flow:** Support single-item checkout bypass that isolates the selected item without emptying the bag.
- **Exit & Acceptance Criteria:**
  - [x] Adding items as a guest, logging in, and verifying all guest items merge into user bag without duplicates.
  - [x] Unchecking an item in the bag recalculates subtotal and excludes it from checkout payload.

---

### Phase 5: Multi-Step Checkout, Philippine PSGC & Xendit Payments
- **Goal:** Deliver the business-critical 4-stage acquisition checkout flow with strict Philippine tax and payment integrity.
- **Dependencies:** Phase 4 complete.
- **Effort Range:** 8 – 9 Developer-Days
- **Tasks Checklist:**
  - [x] **5.1 Build `CheckoutView.vue` container:** Implement step progress indicator (`Shipping` -> `Payment` -> `Review` -> `Confirmation`).
  - [x] **5.2 Implement Step 1 (`ShippingStep.vue`):** Build cascading Philippine PSGC address picker (`Region` -> `Province` -> `City` -> `Barangay`) with NCR auto-adaptation and offline fallback dictionary.
  - [x] **5.3 Implement Step 2 (`PaymentStep.vue`):** Build radio selector for Xendit (Credit/Debit, GCash, Maya) and COD (with auto-disable if total > ₱3,000).
  - [x] **5.4 Implement Step 3 (`ReviewStep.vue`):** Build authoritative order summary breakdown displaying line items, shipping fee, 12% VAT, and total in Philippine Pesos (`₱`).
  - [x] **5.5 Implement Step 4 (`OrderConfirmationView.vue`):** Handle Xendit hosted invoice redirect (Web) or in-app browser tab with custom scheme deep link `eclore://` (Capacitor).
  - [x] **5.6 Implement confirmation status polling:** Poll `GET /api/v1/orders/{orderNumber}/status` to detect webhook completion.
- **Exit & Acceptance Criteria:**
  - [x] Multi-step checkout creates valid order in database; decrements inventory exactly once; dispatches email confirmation.
  - [x] Currency strictly formatted in `₱` (PHP), fixing the legacy Euro (`€`) bug.

---

### Phase 6: Customer Account Dashboard, Order Tracking & RMA
- **Goal:** Deliver customer profile management, order history, official tax receipts, and after-sales repair requests.
- **Dependencies:** Phase 1 and Phase 5 complete.
- **Effort Range:** 6 – 7 Developer-Days
- **Tasks Checklist:**
  - [x] **6.1 Build `AccountTab.vue`:** Implement tabbed customer portal (Profile, Address Book, My Orders, Security).
  - [x] **6.2 Build `OrderHistoryList.vue`:** Implement order cards with status badges and timeline tracking.
  - [x] **6.3 Build `OrderTrackingView.vue`:** Implement public lookup form (`order_number` + `email`) with visual 4-stage delivery timeline.
  - [x] **6.4 Implement receipt download:** Call `/api/v1/account/orders/{orderNumber}/receipt-pdf` to download or share tax-compliant A4 invoice PDF.
  - [x] **6.5 Build `RmaRequestModal.vue`:** Implement after-sales return/repair claim submission with multi-photo attachment.
  - [x] **6.6 Implement password & address updates:** Support password change and Philippine address book management.
- **Exit & Acceptance Criteria:**
  - [x] Customer can view previous orders and download readable PDF receipt.
  - [x] RMA claim creates record in `returns_repairs` with photos accessible in admin backoffice.

---

### Phase 7: Journal/CMS, Appointments & Institutional Pages
- **Goal:** Migrate editorial storytelling, private salon bookings, and legal documentation.
- **Dependencies:** Phase 2 complete.
- **Effort Range:** 5 – 6 Developer-Days
- **Tasks Checklist:**
  - [ ] **7.1 Build `JournalArticleView.vue`:** Implement editorial story presentation with DOM-parsed Table of Contents and scroll-spy.
  - [ ] **7.2 Build `BoutiqueAppointmentsView.vue`:** Implement flagship salon directory and appointment booking form with date/time picker.
  - [ ] **7.3 Build `StaticPageView.vue`:** Implement high-fidelity rendering for About Maison, Care & Sizing, RJC Ethics, Terms, and Privacy Policy.
  - [ ] **7.4 Implement optional Noupe chatbot wrapper:** Provide client remote toggle for concierge chat assistant.
- **Exit & Acceptance Criteria:**
  - [ ] Salon appointment creates record in `appointments` table and dispatches customer confirmation.
  - [ ] All legal documents render with crisp typography and verified navigation links.

---

### Phase 8: End-to-End QA, Security Audit & Staged Cutover
- **Goal:** Execute comprehensive functional, visual regression, accessibility, security, and performance verification prior to production launch.
- **Dependencies:** All previous phases complete.
- **Effort Range:** 7 – 8 Developer-Days
- **Tasks Checklist:**
  - [ ] **8.1 Run Playwright E2E suite:** Verify complete guest-to-purchase lifecycle across desktop and mobile.
  - [ ] **8.2 Execute visual regression tests:** Ensure pixel delta < 1.0% against Blade storefront baseline.
  - [ ] **8.3 Conduct security audit:** Verify token storage in iOS Keychain / Android EncryptedSharedPreferences; validate CORS and rate limits.
  - [ ] **8.4 Run WCAG 2.1 AA accessibility audit:** Verify color contrast and keyboard navigation using Axe-core.
  - [ ] **8.5 Build native Capacitor binaries:** Generate iOS `.ipa` and Android `.apk` / `.aab` packages.
  - [ ] **8.6 Execute canary release:** Route 10% of traffic to Ionic PWA; monitor errors for 7 days before 100% cutover.
- **Exit & Acceptance Criteria:**
  - [ ] 100% of Phase 1 critical user journeys pass without manual intervention.
  - [ ] Lighthouse Performance score >= 90; Accessibility score >= 95.
