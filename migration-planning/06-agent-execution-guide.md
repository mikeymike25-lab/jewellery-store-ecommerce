# Deliverable 06: Autonomous Coding Agent Execution Guide

**Project:** Éclore Luxury Jewellery E-Commerce Platform  
**Target Migration:** Laravel 12 Backend API + Ionic Hybrid Frontend  
**Document Version:** 1.1.0-checklist  
**Target Audience:** Autonomous Coding Agents, Pair-Programming Assistants & Software Engineers  

---

## 1. Prime Directives & System Boundaries

Any future coding agent executing this migration plan **must adhere strictly to the following non-negotiable operating rules**:

```
+----------------------------------------------------------------------------------------------------+
|                                    AGENT OPERATING BOUNDARIES                                      |
+----------------------------------------------------------------------------------------------------+
| 1. NO BROAD UNCONTROLLED REWRITES:                                                                 |
|    Never replace entire directories or rewrite files from scratch unless explicitly instructed.    |
|    Make surgical, contiguous, atomic diffs with clear regression tests.                            |
| 2. PRESERVE LARAVEL AS AUTHORITATIVE SOURCE OF TRUTH:                                              |
|    Pricing, taxes, VAT, inventory decrement, order state transitions, and RBAC policies must       |
|    remain exclusively on the Laravel backend. Do not duplicate business logic to the client.      |
| 3. DATABASE SAFETY FIRST:                                                                          |
|    Never drop tables, execute destructive raw SQL without backups, or alter data columns in a way  |
|    that invalidates existing seeders or SQLite/MySQL cross-compatibility.                          |
| 4. STRICT ZERO-SECRET LOGGING:                                                                     |
|    Never expose `.env` values, API keys, tokens, or customer data in commit messages, logs, or     |
|    markdown deliverables.                                                                          |
| 5. MANDATORY 2-SENTENCE LOGGING TO `logger.md`:                                                    |
|    After completing every task or change, append a concise log entry to                            |
|    `migration-planning/logger.md`. Keep each entry to a MAXIMUM OF TWO SENTENCES.                  |
| 6. PHASE GATES & CHECKLIST TICKING:                                                                |
|    Mark completed items as [x] in both `04-migration-roadmap.md` and this document.                |
|    Do not proceed to a subsequent phase until the current phase's tests pass cleanly.              |
| 7. NEVER PUSH TO GITHUB REPOSITORY:                                                                |
|    Never execute `git push` or publish branches/commits to any remote repository.                  |
|    All development, staging, and commits must remain strictly local to this machine.               |
| 8. MANDATORY DUAL-LAYER AUDIT & LIVE VALIDATION AT EVERY STAGE FINISH:                             |
|    Before ticking checklists or declaring any stage complete:                                      |
|    a) Execute automated unit & feature tests with 0 failures and 0 regressions.                    |
|    b) Perform live integration verification against active servers and database to prove real-world |
|       correctness. Never sign off on assumptions or static code inspection alone.                  |
| 9. PROPORTIONATE ERGONOMICS & BALANCED PADDING (ZERO COLLAPSED CONTROLS):                           |
|    Avoid squished, flat, or zero-padding buttons and form controls. All interactive buttons must   |
|    have balanced, symmetrical internal padding with centered icons/dots(only if needed) and text. Ensure inline    |
|    inputs retain ample width so typed content is fully visible without button crowding.            |
| 10. STRICT 1:1 PIXEL-ACCURATE UI/UX MIRRORING OF THE ORIGINAL STOREFRONT:                           |
|     Always inspect and replicate the exact visual design, layout hierarchy, color palette,        |
|     editorial sections, typography, interactive states, and micro-animations from the Blade views |
|     (`resources/views/`) and CSS (`public/frontend/style.css`). Never compromise on brand fidelity. |
+----------------------------------------------------------------------------------------------------+
```

---

## 2. Mandatory Stage Audit & Live Verification Protocol

To prevent regressions, broken links, or runtime defects from compounding across stages, **every stage completion must undergo a dual-layer audit**:

1. **Layer 1: Automated Test Suite Execution:**
   - Execute the stage-specific and project-wide automated test suite (e.g. `php artisan test`, `npm run test:unit`).
   - Every assertion must pass with zero errors before moving forward.

2. **Layer 2: Live Runtime / Integration Verification:**
   - Test endpoints, components, or builds live against the active development server (`http://127.0.0.1:8000`) and SQLite database.
   - For backend stages: Execute real HTTP requests to verify status codes, payload structures, headers (`X-Guest-Session-Id`, `Authorization: Bearer`), and zero security leaks.
   - For frontend stages: Verify production build (`npm run build`), Capacitor sync (`npx cap sync android`), and asset resolution.
   - Verify that changes do NOT break the live fallback storefront (`/catalogue`, `/`, `/products`).

Only after **both Layer 1 and Layer 2 pass with concrete evidence** may the agent check off tasks as `[x]` and log completion in `logger.md`.

---

## 3. Mandatory Activity Logger Requirement (`logger.md`)

Whenever an agent finishes an implementation task, configuration update, or defect fix:
1. Open or append to: [`migration-planning/logger.md`](file:///c:/Users/Mike%20Angelo/OneDrive/Documents/jewellery-store/jewellery-store-ecommerce\migration-planning\logger.md).
2. Append a timestamped entry in the following format:
   ```markdown
   ### [YYYY-MM-DD HH:MM] Stage X: Task Name
   - First sentence describing exactly what was modified or completed. Second sentence stating the test result or verification status.
   ```
3. **Length Constraint:** Strictly **maximum 2 sentences per log entry**. No lengthy explanations.

---

## 4. Mandatory Luxury UI/UX Button & Control Ergonomics Standards

To guarantee a premium atelier experience and prevent collapsed, flat, or unbalanced controls on mobile and web viewports:
1. **Balanced, Proportional Internal Padding & Centering:**
   - Buttons must NEVER collapse into flat strips or display asymmetrical padding where dots or icons stick to the outer edge.
   - Always guarantee balanced padding on both horizontal and vertical axes with centered alignment (`[ • BUTTON TEXT ]`, never `[• BUTTON TEXT ]`).
   - Order CSS imports and resets deliberately so framework base styles (such as Ionic `normalize.css`) cannot strip or override component padding.
2. **Proportional Inline Form Ergonomics:**
   - In combined input-and-button layouts (such as newsletter subscriptions, search bars, promo codes), size the action button proportionally so it never dominates or crowds the text field.
   - Always ensure the input field has sufficient flexible width (`flex-1`) so users can type and view their entire email address or search query without text truncation.
3. **Harmonious Theme States & Curved Borders:**
   - Facet chips, filter pills, and selection buttons should feature curved/rounded borders and warm theme-coordinated backgrounds (stone/cream/gold) rather than harsh stark-white highlights or heavy black fills.
4. **Context-Aware Empty & Error States:**
   - Never render a "Clear All Filters" button if no filters are active (`activeFiltersCount === 0`).
   - Clearly differentiate between filtered empty results (where filters can be cleared) and network/connection offline states (where a retry action should be offered).

---

## 5. Mandatory 1:1 Luxury UI/UX Storefront Mirroring Standards

To preserve the brand's haute horlogerie and fine jewellery atelier identity, **every Ionic screen and component must replicate the exact UI/UX of the original web storefront**:

1. **Brand Aesthetic & Palette Alignment:**
   - **Backgrounds:** Authentic Éclore warm cream/linen (`#F3EFE7`, `#F8F7F2`), pure atelier white (`#FFFFFF`) for cards and elevated panels.
   - **Accents:** Champagne gold (`#B6965D`, `#C5A880`) and polished brass gold (`#D4AF37`) for highlights, active tabs, and badges.
   - **Text & Contrasts:** Deep charcoal/black (`#1A1A1A`) for primary headings, rich stone (`#4A4A4A`, `#767676`) for editorial subtitles.
   - **Action Buttons:** Sage green (`#8C9B8C` / `#7A8B7A`) for primary checkout CTAs, atelier black (`#1A1A1A`) for "Add To Shopping Bag", and warm outline borders (`#E2DDD5`) for secondary actions.

2. **Typography Standards:**
   - **Titles & Headings:** `Playfair Display` serif font with generous line-height (`1.3 – 1.4`) for all luxury titles, editorial headers, and collection names.
   - **Brand Mark:** `Lexend Peta` with wide letter-spacing (`0.25rem` / `tracking-[0.25em]`) for the Éclore logotype.
   - **Metadata & Data:** `Azeret Mono` or clean sans-serif for prices, SKUs, carats, size pills, and technical specifications.

3. **Section-by-Section Parity:**
   - **Home:** Hero video/banner with orbital motif, calendar strip, rotating circular badges, The Éclore Icons carousel, Our Creations Bento grid, Legacy grid, interactive customizer, and Partners & Friends showcase.
   - **Catalogue / Atelier:** Sticky glassmorphic filter bar, multi-attribute faceted drawer, 4-column responsive grid, and infinite scroll.
   - **Product Detail (PDP):** Sticky editorial thumbnail strip, high-res image lightbox zoom, US ring size guide modal, specifications accordion, VAT breakdown, and verified customer review breakdown with star distribution.
   - **Shopping Bag & Wishlist:** Offcanvas drawers matching `offcanvas-cart.blade.php` and `offcanvas-wishlist.blade.php`, with per-item checkboxes, dynamic subtotal calculation, and instant badge synchronisation.
   - **Checkout & Account:** Cascading Philippine PSGC selectors, secure Xendit payment selection, order tracking timelines, and PDF receipt downloads.

4. **Audit Requirement Before Stage Sign-Off:**
   - Prior to marking any frontend task complete, inspect the corresponding Blade view in `resources/views/` and CSS rules in `public/frontend/style.css` to confirm that all visual elements, typography, spacing, and micro-interactions match 1:1.

---

## 6. Repository Boundaries & Working Directories

- **Git Repository Root:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce`
- **Backend Application Root:** Same as repository root (`app/`, `bootstrap/`, `config/`, `database/`, `routes/`).
- **Target Ionic Project Directory:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce\frontend-ionic` (Dedicated isolated workspace directory).
- **Existing Frontend Preserved:** `public/frontend/` and `resources/views/` must remain functional and intact throughout the migration to maintain the live fallback storefront.
- **Android Studio & Physical Device Testing (USB Debugging):**
  - When testing on a physical phone via USB debugging instead of an emulator, run `adb reverse tcp:8000 tcp:8000`. This maps the physical phone's `localhost:8000` to the PC's Laravel server without needing Wi-Fi or LAN IP reconfiguration.
  - The plan and architecture remain unchanged; `VITE_API_BASE_URL` in `frontend-ionic/.env` supports both `http://localhost:8000/api` (via `adb reverse`) and LAN IP `http://<PC_IP>:8000/api`.

---

## 7. Master Implementation Checklists by Stage

### Stage 0: Database & Schema Defect Remediation
- **Goal:** Fix the 8 verified schema discrepancies to ensure clean runtime execution on both SQLite and MySQL.
- **Tasks Checklist:**
  - [x] **0.1** Remove `room_category` queries from `ProductController.php:23-30`, `routes/api.php:59-64`, and `routes/web.php:500-507`.
  - [x] **0.2** Fix SQLite `MONTH()` queries in `Admin\DashboardController.php:104, 110` and `AnalyticsController.php:747`.
  - [x] **0.3** Change `exists:admins,id` to `exists:employees,id` in `Admin\MessageController.php:87, 139`.
  - [x] **0.4** Clean `User::$fillable` in `app/Models/User.php` and remove invalid `marketing_emails` insert from `AuthController.php:56`.
  - [x] **0.5** Add `items()` alias to `orderItems()` in `app/Models/Order.php`.
  - [x] **0.6** Delete orphan `app/Models/Cart.php` and rewrite `/api/cart/cleanup` to target `guest_sessions` and `cart_items`.
  - [x] **0.7** Deduplicate keys in `WishlistItemSeeder.php:19` and add `xendit` gateway to `PaymentGatewaySeeder.php`.
- **Required Verification Command:**
  ```bash
  php artisan migrate:fresh --seed
  php artisan test
  ```
- [x] **Stage 0 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 1: API Normalization & Sanctum Bearer Authentication
- **Goal:** Establish stateless Bearer token authentication and sanitize API response payloads.
- **Tasks Checklist:**
  - [x] **1.1** Install `laravel/sanctum` and add `HasApiTokens` trait to `App\Models\User`.
  - [x] **1.2** Add Ionic origins to `config/cors.php` (`http://localhost:8100`, `capacitor://localhost`, `ionic://localhost`).
  - [x] **1.3** Strip session/cookie `debug` payload from `CartController.php:64-71, 176-184`.
  - [x] **1.4** Consolidate duplicate `/api/*` routes into versioned `routes/api_v1.php`.
  - [x] **1.5** Implement Sanctum endpoints: `POST /api/v1/auth/token`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/user`.
  - [x] **1.6** Add `X-Guest-Session-Id` header handling middleware for guest cart operations.
  - [x] **1.7** Fix Xendit idempotency bug in `XenditPaymentController.php` with `lockForUpdate()`.
  - [x] **1.8** Fix parameter grouping in `NotificationController.php` for `recipient_type = 'all'`.
- **Required Verification Command:**
  ```bash
  php artisan test --filter Api
  ```
- [x] **Stage 1 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 2: Ionic Project Scaffolding & Design System
- **Goal:** Initialize the Ionic Vue frontend and establish luxury visual tokens.
- **Tasks Checklist:**
  - [x] **2.1** Scaffold `frontend-ionic/` using `@ionic/cli` with Vue 3, Vite, and TypeScript.
  - [x] **2.2** Install dependencies (`pinia`, `axios`, `lucide-vue-next`, `tailwindcss`, `@tailwindcss/vite`) and Capacitor Android (`@capacitor/core`, `@capacitor/android`, `@capacitor/app`, `@capacitor/browser`, `@capacitor/preferences`).
  - [x] **2.3** Add Android platform via `npx cap add android` and configure `AndroidManifest.xml` (`usesCleartextTraffic="true"` and `eclore://` deep-linking).
  - [x] **2.4** Configure `src/theme/variables.css` with Éclore design tokens (`#F3EFE7`, `#B6965D`, `#1A1A1A`, typography).
  - [x] **2.5** Copy verified media assets from `public/frontend/assets/` into `frontend-ionic/public/assets/`.
  - [x] **2.6** Build core UI components: `EcloreHeader`, `EcloreFooter`, `EcloreButton`, `EcloreModal`, `CurrencyDisplay`.
  - [x] **2.7** Setup Pinia root store and Axios client with Bearer auth, `X-Guest-Session-Id`, and Android Emulator host (`10.0.2.2:8000`).
- **Required Verification Command:**
  ```bash
  cd frontend-ionic && npm run build && npx cap sync android
  ```
- [x] **Stage 2 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 3: Product Catalog, Faceted Filters & PDP
- **Goal:** Deliver the high-fidelity jewellery discovery and product detail views.
- **Tasks Checklist:**
  - [x] **3.1** Build `HomeTab.vue` with hero video banner, marquee, 4-card Bento grid, and product carousel.
  - [x] **3.2** Build `CatalogueTab.vue` with 4-column product grid and infinite scroll.
  - [x] **3.3** Build `FilterDrawer.vue` with Gold Material, Diamond Carat, Gemstones, and Price Range facets.
  - [x] **3.4** Build `ProductDetailView.vue` with zoomable gallery, specifications, and ring size selector.
  - [x] **3.5** Build `SearchModal.vue` with debounced search and quick filter tags.
  - [x] **3.6** Build `ReviewsSection.vue` with star ratings breakdown and photo lightbox.
- **Required Verification Command:**
  ```bash
  cd frontend-ionic && npm run build && npx cap sync android
  ```
- [x] **Stage 3 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 4: Shopping Bag, Guest Sessions & Post-Auth Migration
- **Goal:** Implement cart drawer, checkbox selection, and transactional guest-to-user cart merging.
- **Tasks Checklist:**
  - [x] **4.1** Implement `useCartStore` with `guestSessionId` and selected items persistence.
  - [x] **4.2** Build `CartDrawer.vue` with per-item checkboxes (`.cart-item-checkbox`) and dynamic subtotal calculation.
  - [x] **4.3** Implement `useWishlistStore` with one-tap heart toggle and badge counter.
  - [x] **4.4** Implement post-login cart migration calling `POST /api/v1/cart/migrate` and `POST /api/v1/wishlist/migrate`.
  - [x] **4.5** Implement "Buy Now" flow isolating selected item without emptying cart.
- **Required Verification Command:**
  - Automated or manual guest-to-user cart migration test.
- [x] **Stage 4 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 5: Multi-Step Checkout & Xendit Payments
- **Goal:** Deliver the 4-stage acquisition flow with Philippine PSGC address picker and Xendit payment integration.
- **Tasks Checklist:**
  - [x] **5.1** Build `CheckoutView.vue` container with step progress indicator.
  - [x] **5.2** Build `PsgcAddressPicker.vue` with Region -> Province -> City -> Barangay cascading logic and NCR adaptation.
  - [x] **5.3** Build `PaymentStep.vue` with Xendit and COD options (auto-disabled if total > ₱3,000).
  - [x] **5.4** Build `ReviewStep.vue` with authoritative line items, shipping, 12% VAT, and total in `₱`.
  - [x] **5.5** Build `OrderConfirmationView.vue` with Xendit redirect and custom scheme deep link (`eclore://`).
  - [x] **5.6** Implement confirmation status polling to detect webhook completion.
- **Required Verification Command:**
  - End-to-end checkout execution verifying single stock decrement and valid order creation.
- [x] **Stage 5 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 6: Customer Account, Tracking & After-Sales RMA
- **Goal:** Implement order history, official tax receipts, and after-sales claims.
- **Tasks Checklist:**
  - [x] **6.1** Build `AccountTab.vue` with Profile, Address Book, and My Orders sections.
  - [x] **6.2** Build `OrderHistoryList.vue` and `OrderDetailModal.vue` with timeline tracking.
  - [x] **6.3** Build `OrderTrackingView.vue` public parcel lookup form (`order_number` + `email`).
  - [x] **6.4** Implement PDF receipt download calling `/api/v1/account/orders/{orderNumber}/receipt-pdf`.
  - [x] **6.5** Build `RmaRequestModal.vue` return/repair claim wizard with photo upload.
- **Required Verification Command:**
  - Verify receipt download generates readable A4 tax invoice and RMA record is created.
- [x] **Stage 6 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

### Stage 7: Production QA, Security Verification & Canary Cutover
- **Goal:** Execute full end-to-end automated verification and release to canary traffic.
- **Tasks Checklist:**
  - [ ] **7.1** Run automated Playwright E2E suite.
  - [ ] **7.2** Run visual regression diffing: pixel delta < 1.0% across desktop, tablet, and mobile viewports.
  - [ ] **7.3** Sync and open in Android Studio via `npx cap sync android` and `npx cap open android`. Build signed release APK/AAB and test on Android Virtual Device (AVD).
  - [ ] **7.4** Deploy PWA build to canary subdomain (`app.eclorejewellery.shop`) with 10% traffic routing.
- **Required Verification Command:**
  ```bash
  npx playwright test && npx cap sync android
  ```
- [ ] **Stage 7 Verification Checkpoint Passed:** Log completion in `logger.md` (max 2 sentences).

---

## 6. Clear Stop Conditions & Escalation Triggers

An autonomous agent **must pause execution and request human review** if any of the following occur:
1. **Unresolvable Database Conflict:** Running a migration or query fails with an integrity constraint violation that cannot be solved without deleting customer order records.
2. **Missing Payment Credentials:** Inability to test live Xendit webhooks due to missing sandbox keys (agent must mark tests as mocked and request credentials).
3. **Severe Visual Degradation:** Visual regression diff exceeds 5.0% on critical brand elements (logo, hero typography, gold buttons).
4. **Scope Creep Request:** Any instruction to delete the existing Laravel Blade views or remove the Admin Backoffice prior to Phase 8 completion.
