# Deliverable 05: Risk Matrix & Comprehensive Verification Strategy

**Project:** Éclore Luxury Jewellery E-Commerce Platform  
**Target Migration:** Laravel 12 Monolith → Laravel 12 API + Ionic Hybrid Frontend  
**Document Version:** 1.0.0-final  
**Scope:** Functional, Visual, Security, Architectural, Database, Platform & Operational Quality Assurance  

---

## 1. Prioritized Risk Assessment Matrix

Every risk identified during the codebase audit is scored by Likelihood (1 = Rare, 5 = Almost Certain) and Severity (1 = Negligible, 5 = Critical), producing an overall Risk Score (1–25).

```
+---------------------------------------------------------------------------------------------------------------------------------------+
|                                                      PRIORITIZED RISK MATRIX                                                          |
+----+-------------------+-------------------------------+------------+----------+-------+-------------------------+--------------------+
| ID | Risk Category     | Specific Identified Threat    | Likelihood | Severity | Score | Primary Mitigation      | Phase / Owner      |
+----+-------------------+-------------------------------+------------+----------+-------+-------------------------+--------------------+
| R1 | **Security**      | Session/Cookie Leak in Cart   | 5 (High)   | 5 (Crit) | 25    | Sanitize CartController | Phase 1 (Backend)  |
| R2 | **Financial/Ops** | Xendit Double Stock Decrement | 4 (High)   | 5 (Crit) | 20    | Atomic Idempotency Lock | Phase 1 (Backend)  |
| R3 | **Data Integrity**| DB Schema Discrepancy Crashes | 4 (High)   | 4 (High) | 16    | Schema & Model Cleanups | Phase 0 (Database) |
| R4 | **Security**      | Insecure Local Token Storage  | 4 (High)   | 4 (High) | 16    | Native Secure Storage   | Phase 2 (Mobile)   |
| R5 | **Visual/Brand**  | Luxury Brand Identity Erosion | 3 (Med)    | 5 (Crit) | 15    | Visual Regression Gates | Phase 2, 8 (UI/UX) |
| R6 | **Functional**    | Guest-to-User Cart Data Loss  | 3 (Med)    | 4 (High) | 12    | Transactional Merge API | Phase 4 (Fullstack)|
| R7 | **Platform/Mobile**| Payment Return Scheme Failure| 3 (Med)    | 4 (High) | 12    | Custom Deep-Link Test   | Phase 5 (Mobile)   |
| R8 | **Performance**   | Heavy Media Mobile Latency    | 4 (High)   | 3 (Med)  | 12    | Asset Bundling & WebP   | Phase 2, 3 (Mobile)|
| R9 | **Architecture**  | Cross-Origin CORS Rejection   | 4 (High)   | 3 (Med)  | 12    | Explicit Origin Config  | Phase 1 (DevOps)   |
| R10| **Security**      | Global Notification Deletion  | 3 (Med)    | 3 (Med)  | 9     | Parameter Scope Grouping| Phase 1 (Backend)  |
+----+-------------------+-------------------------------+------------+----------+-------+-------------------------+--------------------+
```

---

### Detailed Threat Profiles & Mitigations

#### R1: Sensitive Session & Cookie Leakage in `CartController.php`
- **Audit Evidence:** `app/Http/Controllers/CartController.php:64-71, 176-184` serializes `$request->cookies->all()` and `$request->cookie('laravel_session')` into JSON debug attributes.
- **Impact:** Complete circumvention of `HttpOnly` cookie protection; trivial session hijacking if any client-side script is compromised.
- **Mitigation:** Strip the entire `'debug'` array from `CartController.php`. Add automated CI lint checks prohibiting serialization of `request()->cookies` in JSON controllers.
- **Validation:** Automated API contract test asserting `response.data.debug` is undefined and `response.data` contains no cookie strings.

#### R2: Payment Webhook vs. Return Race Condition (Double Stock Decrement)
- **Audit Evidence:** `app/Http/Controllers/Payments/XenditPaymentController.php:233` (`returnSuccess`) and `line 399` (`webhook`) both call `InventoryMovement::recordStockOut(...)` without checking if `$order->payment_status` is already `'paid'`.
- **Impact:** Duplicate stock deduction on one-of-a-kind jewelry items, causing negative inventory levels and duplicate customer emails.
- **Mitigation:** Wrap status transition in a database transaction with a row-level lock (`Order::where('id', $order->id)->lockForUpdate()->first()`). Execute stock decrement only if transitioning from `pending` -> `paid`.
- **Validation:** Concurrently dispatch webhook payload and browser return request; assert `inventory_movements` contains exactly one `'out'` entry.

#### R3: Database Schema-vs-Code Discrepancies
- **Audit Evidence:** 8 verified discrepancies cataloged in Deliverable 01 (e.g. `room_category`, `Cart` model, `Admin\MessageController` table name, `User::$fillable` address columns).
- **Impact:** Unhandled 500 exceptions, broken catalog filters, and failed customer registrations.
- **Mitigation:** Execute Phase 0 defect remediation before developing Ionic API consumers.
- **Validation:** Run automated test calling every refactored endpoint with edge-case parameter payloads across both SQLite and MySQL engines.

#### R4: Insecure Token Storage in Native Mobile Builds
- **Audit Evidence:** Standard browser SPAs store tokens in `localStorage`, which is unencrypted and accessible to WebView XSS.
- **Impact:** Extraction of long-lived customer authentication tokens on compromised or jailbroken mobile devices.
- **Mitigation:** Utilize `@aparajita/capacitor-secure-storage` on native iOS (Keychain) and Android (EncryptedSharedPreferences). Enforce 30-day token expiration with automatic refresh.
- **Validation:** Inspect device filesystem sandbox on physical iOS and Android test devices to confirm tokens are not stored in plaintext SQLite or XML preferences.

#### R5: Luxury Brand Identity Degradation
- **Audit Evidence:** Replacing Éclore's custom editorial typography (`Playfair Display`, `Azeret Mono`, `Cormorant Garamond`) and colors (`#F3EFE7`, `#B6965D`, `#1A1A1A`) with generic Material Design or Cupertino Ionic defaults would destroy brand equity.
- **Impact:** Negative brand perception, reduced customer trust in high-value fine jewelry transactions (orders exceeding ₱100,000).
- **Mitigation:** Map Éclore design tokens strictly into `theme/variables.css`. Implement automated visual regression testing with Playwright and pixelmatch.
- **Validation:** Automated side-by-side screenshot diffing with a strict threshold (pixel delta < 1.0%).

---

## 2. Multi-Tiered Verification & Test Strategy

```
                                  +------------------------------+
                                  |     End-to-End & Device      |  (Playwright, iOS Simulator,
                                  |     (Simulated Acquisition)  |   Android Emulator, Physical)
                                  +------------------------------+
                                                 |
                                  +------------------------------+
                                  |    Visual Regression & A11y  |  (Pixelmatch, Percy,
                                  |   (Viewport Parity & WCAG)   |   Axe-core automated)
                                  +------------------------------+
                                                 |
                                  +------------------------------+
                                  |    UI & Component Level      |  (Vitest, Vue Test Utils,
                                  |   (Pinia Stores & Modals)    |   Ionic Web Components)
                                  +------------------------------+
                                                 |
                                  +------------------------------+
                                  |      API Contract Tests      |  (Pest PHP / PHPUnit,
                                  |     (Schema & Status Codes)  |   Sanctum Auth Assertions)
                                  +------------------------------+
                                                 |
                                  +------------------------------+
                                  |    Unit & Service Level      |  (PHPUnit, Business Rules,
                                  |  (Tax, VAT & Inventory Calc) |   Stock Decrement Ledger)
                                  +------------------------------+
```

---

### Tier 1: Backend Unit & Feature Tests (Laravel 12 / PHPUnit)
All tests run against in-memory SQLite and MySQL via `tests/Feature/` and `tests/Unit/`.

1. **`AuthApiTest.php`:**
   - Registration creates user in `users` table without `marketing_emails` error.
   - Successful login returns valid Sanctum plain-text Bearer token.
   - Calling `/api/v1/auth/user` with invalid Bearer token returns HTTP 401 Unauthorized.
2. **`CartApiTest.php`:**
   - `POST /api/v1/cart/add` with `X-Guest-Session-Id` header creates item in `cart_items` with `session_id`.
   - Response payload **does not contain** `'debug'`, `'request_cookies'`, or `'session_cookie'`.
   - `POST /api/v1/cart/migrate` transactionally reassigns guest items to authenticated `user_id`.
3. **`CheckoutTaxAndPriceTest.php`:**
   - Authoritative verification that 12% Philippine VAT is calculated server-side.
   - COD payment option is rejected with HTTP 422 if order total exceeds ₱3,000 threshold.
   - Order creation appends `OrderedCartItemIds` to order metadata and deducts inventory inside transaction.
4. **`XenditIdempotencyTest.php`:**
   - Simulated webhook payload marks order `'paid'` and records stock out.
   - Secondary arrival on return URL (`returnSuccess`) detects existing `'paid'` status and bypasses second inventory deduction.

---

### Tier 2: Frontend Unit & Component Tests (Vitest + Vue Test Utils)
1. **`CurrencyDisplay.spec.ts`:**
   - Asserts numeric value `125000` formats strictly as `₱125,000.00` (Philippine Peso), never Euro (`€`) or USD (`$`).
2. **`CartStore.spec.ts`:**
   - Unchecking a cart item removes its price from the checkout subtotal.
   - Clearing the bag resets state and updates badge counter to 0.
3. **`PsgcAddressPicker.spec.ts`:**
   - Selecting Region NCR (`1300000000`) hides Province select and populates City dropdown with NCR municipalities.
   - Simulating network failure on `psgc.cloud` seamlessly triggers fallback to bundled local JSON dictionary.

---

### Tier 3: Visual Regression Testing Protocol

Visual parity is verified by capturing full-page screenshots of existing Laravel Blade screens against the new Ionic screens under identical conditions:

```
+----------------------------------------------------------------------------------------------------+
|                                    VISUAL REGRESSION MATRIX                                        |
+-------------------+--------------------+------------------------+----------------------------------+
| Screen / Workflow | Viewport Sizes     | Representative State   | Acceptance Criteria              |
+-------------------+--------------------+------------------------+----------------------------------+
| **Homepage**      | 1440x900 (Desktop) | Hero, Bento, Carousel  | Font rendering, logo position,   |
|                   | 375x812 (Mobile)   | Full viewport capture  | button gold colors match <1.0%   |
+-------------------+--------------------+------------------------+----------------------------------+
| **Catalogue**     | 1440x900, 375x812  | Filter drawer open;    | 4-col desktop, 2-col mobile;     |
|                   |                    | 8 product cards loaded | aspect ratio of cards preserved  |
+-------------------+--------------------+------------------------+----------------------------------+
| **PDP**           | 1440x900, 375x812  | Solitaire Ring details;| Gallery thumbnail strip layout,  |
|                   |                    | Size selector active   | specifications table alignment   |
+-------------------+--------------------+------------------------+----------------------------------+
| **Cart Drawer**   | 1440x900, 375x812  | 2 items selected;      | Drawer width (480px on desktop,  |
|                   |                    | 1 unselected item      | 100% on mobile); checkbox styling|
+-------------------+--------------------+------------------------+----------------------------------+
| **Checkout**      | 1440x900, 375x812  | Step 1 Shipping;       | 2-col desktop layout, 4-step     |
|                   |                    | Step 3 Review summary  | progress bar styling intact      |
+-------------------+--------------------+------------------------+----------------------------------+
```

- **Capture Methodology:** Playwright test scripts launch headless Chromium at fixed DPI (2.0) and capture PNG screenshots stored in `tests/visual-baselines/`.
- **Comparison Engine:** `pixelmatch` compares baseline (Blade) against target (Ionic). Any deviation exceeding 1.0% mismatch threshold fails CI.

---

### Tier 4: Accessibility & Compliance Verification (WCAG 2.1 Level AA)

1. **Automated Axe-Core Audits:** Every Ionic view is scanned using `@axe-core/playwright`. Zero critical or serious accessibility violations permitted.
2. **Color Contrast Verification:**
   - Primary Ink `#1A1A1A` on Warm Cream `#F3EFE7`: Contrast ratio **13.8:1** (Exceeds WCAG AAA requirement of 7:1).
   - Champagne Gold `#B6965D` on White `#FFFFFF`: Contrast ratio **3.2:1** (Fails for normal text). **Mitigation:** Gold `#B6965D` is reserved strictly for borders, graphical icons, and button backgrounds with dark ink text `#1A1A1A`; body text uses `#1A1A1A` or high-contrast taupe `#4A4640`.
3. **Screen Reader & Keyboard Navigation:**
   - All interactive controls (`EcloreButton`, quantity adjusters, modal dismiss buttons) have explicit `aria-label` attributes.
   - Focus rings (`outline: 2px solid var(--eclore-gold)`) visible during tab navigation.

---

### Tier 5: Native Platform & Device Verification

| Target Platform | Test Environment | Key Verification Focus |
|---|---|---|
| **PWA / Responsive Web** | Chrome, Safari 17+, Firefox, Edge | Service Worker caching, offline fallback, desktop mega-menu hover states. |
| **iOS Native** | iOS 17 / 18 Simulator & Physical iPhone 14/15 | Safe-area inset padding (`env(safe-area-inset-top)`), Keychain token storage, Xendit custom URL scheme return. |
| **Android Native** | Android 14 / 15 Emulator & Physical Samsung Galaxy | Android back button hardware navigation, EncryptedSharedPreferences storage, Chrome Custom Tabs for Xendit. |

---

## 3. Test Automation vs. Manual Verification Categorization

To maintain development velocity without sacrificing rigor, tests are clearly categorized by execution mode:

| Verification Category | Automated (CI Pipeline) | Manual / Device Required | Prerequisites & Tooling |
|---|---|---|---|
| **Backend Route & API Contract** | **Yes** (100% Automated) | No | PHPUnit, SQLite `:memory:` |
| **Security Leakage Audit** | **Yes** (100% Automated) | No | Custom regex & JSON assertions |
| **Frontend Unit & Store Logic** | **Yes** (100% Automated) | No | Vitest, Vue Test Utils |
| **Visual Regression Diffing** | **Yes** (100% Automated) | Review flagged diffs | Playwright, pixelmatch |
| **End-to-End Checkout Flow** | **Yes** (Automated Mock) | Real sandbox testing | Playwright with Xendit mock |
| **Live Xendit Webhook & Return**| Partially (Mocks in CI) | **Yes (Manual Sandbox)** | Xendit Sandbox API Keys in `.env` |
| **Google OAuth Single Sign-On** | Partially (Mocked Token) | **Yes (Manual Device)** | Google Cloud Client ID/Secret |
| **Physical Haptics & Biometrics**| No | **Yes (Physical Devices)** | Physical iOS & Android hardware |
