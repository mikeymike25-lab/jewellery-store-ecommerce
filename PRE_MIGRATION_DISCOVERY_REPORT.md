# PRE-MIGRATION DISCOVERY REPORT: Éclore Jewellery Store (`PHP + XAMPP + MySQL → Ionic Framework`)

**Document Purpose:** Pre-Migration Read-Only Discovery & Audit Readiness Report  
**Workspace Directory:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store`  
**Git Repository Root:** `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce`  
**Remote Origin:** `https://github.com/haniluvr/jewellery-store-ecommerce.git` (branch: `main`)  
**Target Architecture:** Laravel 12 Backend API + Ionic Framework Frontend  
**Overall Migration Audit Readiness Status:** **READY WITH LIMITATIONS**

---

## 1. Executive Summary

A read-only architectural, source code, database, runtime environment, security, and UI/UX investigation was conducted on the cloned **Éclore Jewellery Store** repository (`jewellery-store-ecommerce`).

Contrary to a minimal or procedural XAMPP/PHP project, this repository contains a **complete, feature-rich Laravel 12 monolithic e-commerce platform** (PHP `^8.2`) comprising:
1. **Customer Luxury Storefront (`eclore.test`)** — Server-rendered Blade views coupled with a hybrid AJAX JSON API (`/api/*`) for product discovery, cart, wishlist, notifications, multi-step checkout, Xendit payment processing, Philippine PSGC address lookups, order tracking, PDF receipts/invoices, product reviews, and returns/repairs.
2. **Subdomain Admin Backoffice (`admin.eclore.test` / `/admin/*`)** — 18 admin controllers and 50+ Blade templates managing KPIs, analytics, inventory movements, order fulfillment, returns/repairs (RMA), CMS/Newsroom pages, staff RBAC permissions, customer inquiries, and system settings.

### Readiness Verdict Summary
- **Overall Audit Readiness:** **READY WITH LIMITATIONS**
- **Database Readiness:** **READY** (Complete 35-table Laravel migration suite, 21 seeders with realistic Philippine luxury jewellery data, and a populated 774 KB `database/database.sqlite` file are present in the repository, alongside documented MySQL/XAMPP configuration in `.env` and `.env.example`—though 8 code-vs-schema discrepancies were discovered and are documented in Section 5).
- **UI/UX Audit Readiness:** **READY** (All 86+ Blade templates, custom CSS, Tailwind v4/CDN configurations, Google/Bunny typography definitions, luxury brand color tokens, interactive JS modules, and WebP/AVIF jewellery assets are present on disk).
- **Why "Ready with Limitations" Instead of Unconditional "Ready":**
  While **100% of the source code, database migrations, seeders, and frontend assets are present** to execute the full migration-planning master prompt immediately, **architectural scope decisions** (e.g., whether the Ionic migration targets only the Customer Storefront or also the 18-module Admin Backoffice, and which frontend framework flavor of Ionic—Angular, React, or Vue—is desired) and **third-party sandbox credentials** (Xendit, Google OAuth, AWS S3, Pusher) are unverified or blank in `.env`.

---

## 2. Repository Overview (Phase 1)

### 2.1 Workspace vs. Repository Root
- **Current Working Directory (`c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store`):** Acts as a parent workspace wrapper folder containing a single subdirectory (`jewellery-store-ecommerce`) and no `.git` folder.
- **Actual Git Repository Root (`c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce`):** Contains `.git/HEAD` (`ref: refs/heads/main`), `.git/config` (`https://github.com/haniluvr/jewellery-store-ecommerce.git`), application source code, dependencies, and configuration files.

### 2.2 Project Identity & Codebase Lineage
- **Brand Name & Domain:** **Éclore** (*"Handcrafted luxury jewellery with timeless design"*), configured locally for `eclore.test` and `admin.eclore.test` (`.apache/eclore.conf:1-60`, `README.md:113-114`) and in production templates for `eclorejewellery.shop` (`env.production.template:6`).
- **Confirmed Codebase Lineage (Fork Artifact):** The codebase was forked and adapted from an earlier furniture e-commerce platform (`davids-wood-furniture`). Leftover references remain across configuration and source files and must be accounted for during migration:
  - `README.md:160,210` (`git clone https://github.com/haniluvr/davids-wood-furniture.git`, `DocumentRoot "C:/xampp/htdocs/davids-wood-furniture/public"`)
  - `docker-compose.yml:6,15,40` (`container_name: davids-wood-app`, `DB_DATABASE=davids_wood_furniture`)
  - `public/health.php:13` (`'service' => 'davids-wood-furniture'`)
  - `env.production.template:17,63` (`DB_DATABASE=davids_wood_furniture`, `AWS_BUCKET=davids-wood-furniture-Bucket`)
  - `public/frontend/style.css:112-127` (`.featured-furniture` CSS class)
  - `app/Http/Controllers/ProductController.php:22-30`, `routes/api.php:58-64`, `routes/web.php:500-507` (legacy `room_category` filter queries)

### 2.3 Technology Stack & Dependency Managers
| Layer | Technology / Package | Version / Evidence |
|---|---|---|
| **Backend Language** | PHP | `^8.2` (`composer.json:9`, `Dockerfile:1`) |
| **Backend Framework** | Laravel Framework | `^12.0` (`composer.json:13`, `composer.lock`) |
| **Database Engine** | SQLite (active local file) & MySQL 8.0 / XAMPP MariaDB | `.env:11` (`DB_CONNECTION=sqlite`), `.env.example:11` (`DB_CONNECTION=mysql`), `database/database.sqlite` |
| **PHP Dependency Manager** | Composer | `composer.json`, `composer.lock` (10,024 lines), `vendor/` installed on disk |
| **Key Composer Packages** | Socialite, DomPDF, Flysystem S3, Pusher, Predis, Adyen | `laravel/socialite: ^5.23`, `barryvdh/laravel-dompdf: ^3.1`, `league/flysystem-aws-s3-v3: ^3.30`, `pusher/pusher-php-server: ^7.2`, `predis/predis: ^3.2`, `adyen/php-api-library: ^28.2` (`composer.json:10-18`) |
| **Frontend Build Tool** | Node.js / npm + Vite | `package.json`, `package-lock.json`, `vite.config.js`, `node_modules/` and `public/build/manifest.json` installed on disk |
| **CSS Styling** | Tailwind CSS v4 (Vite) + Tailwind v3 CDN + Custom CSS | `package.json:10,20` (`@tailwindcss/vite: ^4.0.0`), `resources/views/layouts/app.blade.php:15` (`cdn.tailwindcss.com`), `public/frontend/style.css` (3,226 lines) |
| **Frontend JS Libraries** | Vanilla ES6 Modules, jQuery 4.0 / 3.7.1, Alpine.js 3.x, Preline UI, AOS, Chart.js, Quill.js, Lucide Icons, Laravel Echo | `package.json:11-21`, `resources/js/app.js:1-76`, `resources/views/layouts/app.blade.php`, `resources/views/admin/layouts/app.blade.php` |

### 2.4 Top-Level Repository Structure (`jewellery-store-ecommerce/`)
- `.apache/eclore.conf` — Local XAMPP/Apache VirtualHost definitions for `eclore.test` and `admin.eclore.test`.
- `.docker/` & `Dockerfile`, `docker-compose.yml`, `docker-compose.prod.yml` — Containerized Apache + PHP 8.2, MySQL 8.0, Redis, Mailhog, and Adminer definitions.
- `.github/workflows/ci.yml` — Automated CI/CD pipeline.
- `app/` — Core Laravel application logic (`Console/`, `Events/`, `Helpers/`, `Http/Controllers/`, `Http/Middleware/`, `Listeners/`, `Mail/`, `Models/`, `Providers/`, `Services/`).
- `bootstrap/app.php` & `bootstrap/providers.php` — Laravel 12 application bootstrap, middleware aliases (`admin.auth`, `admin.permission`, `2fa`), and CSRF webhook exceptions (`webhooks/xendit`).
- `config/` — 12 Laravel configuration files (`app.php`, `auth.php`, `cors.php`, `database.php`, `filesystems.php`, `mail.php`, `Payment.php`, `queue.php`, `services.php`, `session.php`, etc.).
- `database/` — `database.sqlite`, `migrations/` (3 migration files), `seeders/` (21 seeders), `factories/UserFactory.php`.
- `public/` — Web root (`index.php`, `.htaccess`, `health.php`, `build/`, `css/admin.css`, `frontend/assets/`, `frontend/js/`, `frontend/style.css`).
- `resources/` — Uncompiled CSS (`resources/css/`), JS (`resources/js/`), and Blade templates (`resources/views/`).
- `routes/` — `web.php` (1,073 lines), `api.php` (690 lines), `console.php` (26 lines).
- `storage/` — Local file storage (`storage/app/public/`), framework cache/sessions, and `storage/logs/laravel.log`.
- `tests/` — PHPUnit test suite (`tests/Feature/`, `tests/Unit/`).

---

## 3. Source Code Completeness Assessment (Phase 2 & Phase 7)

### 3.1 Inventory of Verified Source Components
| Component Category | Count / Location | Completeness Status |
|---|---|---|
| **Controllers** | 31 controllers (`13` customer/shared in `app/Http/Controllers/`, `18` admin in `app/Http/Controllers/Admin/`) | **Complete** |
| **Eloquent Models** | 29 models in `app/Models/` (`Product`, `Category`, `Order`, `OrderItem`, `User`, `Admin`, `Employee`, `CartItem`, `WishlistItem`, `ReturnRepair`, `CmsPage`, etc.) | **Complete** (except unreferenced `Coupon` model relation in `Order.php:81`) |
| **Middleware & Services** | 11 middleware classes (`app/Http/Middleware/`), 4 services (`app/Services/`), 3 helper classes (`app/Helpers/` + `app/helpers.php`) | **Complete** |
| **Events, Listeners & Mailable Classes** | 9 Events (`app/Events/`), 9 Listeners (`app/Listeners/`), 14 Mailables (`app/Mail/`) | **Complete** |
| **Blade Templates** | 36 Customer views + 12 partials + 15 email views + 50+ Admin views in `resources/views/` | **Complete** |
| **Frontend JS & CSS** | `public/frontend/style.css`, `public/frontend/js/*.js`, `resources/js/legacy/*.js`, `public/css/admin.css` | **Complete** (duplicated across `public/frontend/js/` and `resources/js/legacy/`) |
| **Dependencies & Build Output** | `vendor/`, `node_modules/`, `composer.lock`, `package-lock.json`, `public/build/manifest.json` | **Complete & Installed on Disk** |

### 3.2 What Can Be Analyzed Immediately
- **100% of Customer Storefront Workflows:** Home showcase, product catalog filtering/sorting/pagination, product detail & gallery, guest + authenticated cart, wishlist, customer login/registration/magic-link/2FA, multi-step checkout (shipping, payment, review, confirmation), Philippine PSGC address cascading selector, order tracking, PDF receipt/invoice generation, product reviews, returns/repairs requests, and boutique appointment booking.
- **100% of Admin Backoffice Workflows:** Dashboard analytics, product/category CRUD, inventory tracking, order fulfillment, return/repair RMA management, CMS page/blog publishing, review moderation, staff RBAC permissions, shipping rules, payment gateway config, and audit logging.
- **Database Schema & Seed Data:** Every table structure, foreign key, index, and seeded dataset is readable directly from `database/migrations/` and `database/seeders/`.

### 3.3 What Can Only Be Partially Analyzed (Without External Credentials or Stakeholder Input)
- **Live Third-Party Integrations:**
  - **Xendit Hosted Checkout & Webhooks (`app/Http/Controllers/XenditPaymentController.php`):** Code implementation (`https://api.xendit.co/v2/invoices`) and database settings lookups (`PaymentGateway` / `Setting` models) are fully visible, but live webhook payload signatures and sandbox redirect behavior cannot be tested without Xendit API keys.
  - **Google OAuth SSO (`app/Http/Controllers/AuthController.php`, `app/Http/Controllers/Admin/AuthController.php`):** Uses `laravel/socialite`, but `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` are unset in `.env`.
  - **AWS S3 Dynamic Media Storage (`app/Services/DynamicStorageService.php`):** Falls back gracefully to local disk when S3 credentials are absent, though `config/filesystems.php:50-51` has an env variable naming mismatch (`AWS_S3_ACCESS_KEY_ID` vs. `AWS_ACCESS_KEY_ID`).
  - **Pusher Real-Time Admin Notifications (`config/broadcasting` / `resources/views/admin/layouts/app.blade.php:40-41`):** `PUSHER_APP_KEY` is blank in `.env`.
  - **External Chatbot Widget (`resources/views/layouts/app.blade.php:16`):** Embeds an external script (`https://www.noupe.com/embed/019d659c5e1473f7bd0f24cf93f8f30305c9.js`) whose server-side configuration lives outside the repository.

### 3.4 What Cannot Be Verified From the Repository Alone
- **Target Migration Scope & Platform Requirements:**
  - Whether the Ionic migration applies **only to the Customer Storefront** (converting the customer web experience into an Ionic mobile/PWA app consuming the Laravel backend) or **also requires migrating the 18-module Admin Backoffice** into Ionic.
  - Which **Ionic UI framework binding** is required (**Ionic Angular**, **Ionic React**, or **Ionic Vue**) and whether native mobile builds (**Capacitor iOS/Android**) or **PWA/Responsive Web** are targeted.

---

## 4. Preliminary Feature and Architecture Findings (Phase 2)

### 4.1 Dual-Portal Monolithic Architecture
The application serves two portals from a single Laravel 12 codebase:
1. **Customer Storefront (`routes/web.php:377-845`):**
   - Uses standard `web` guard (`users` table, `App\Models\User`).
   - Renders Blade layouts (`resources/views/layouts/app.blade.php`) with global slide-over offcanvas drawers (`offcanvas-cart`, `offcanvas-wishlist`, `offcanvas-notification`) and modals (`modal-login`, `modal-signup`, `modal-search`, `modal-quick-view`).
2. **Admin Portal (`routes/web.php:42-375` & `routes/web.php:848-1065`):**
   - Uses a separate `admin` guard (`config/auth.php:43-46`) backed by the `employees` table (`App\Models\Admin`).
   - Registered **twice** in `routes/web.php`: once under `Route::domain('admin.' . ...)` (`L42-358`) and once under `Route::prefix('admin')` (`L848-1065`) so it works via subdomain (`admin.eclore.test`) or path prefix (`/admin/dashboard`).

### 4.2 Hybrid Stateful API Pattern (Critical for Ionic Migration)
- **Duplicated API Routes:** Almost all JSON endpoints in `routes/api.php` (`L35-690`) are duplicated inside `routes/web.php` under `Route::prefix('api')` (`L487-754`).
- **Session-Cookie Coupling:** The frontend JavaScript client (`public/frontend/js/api.js:30-45`) sends `credentials: 'include'` and `X-CSRF-TOKEN` headers to `/api/*`. Controllers such as `CartController`, `WishlistController`, `AuthController`, and `NotificationController` rely on **stateful browser session cookies** (`session()->getId()` and `Auth::guard('web')->user()`).
- **Absence of Stateless Token Auth:** Neither `laravel/sanctum` nor `laravel/passport` is installed in `composer.json`. For an Ionic application (especially when running in Capacitor on `capacitor://localhost` or `http://localhost:8100`), stateful session cookies and CSRF tokens across origins will fail unless **Laravel Sanctum** (Bearer token authentication) and explicit guest cart token headers (`X-Guest-Session-Id`) are introduced.

### 4.3 Duplicated Frontend JavaScript Assets
The repository maintains two copies of the storefront JavaScript modules:
- `resources/js/legacy/{config,api,auth,payment-methods,checkout,app}.js` (imported by `resources/js/app.js:71-76` and compiled by Vite into `public/build/assets/app-DWuMtrud.js`).
- `public/frontend/js/{config,api,auth,payment-methods,checkout,app}.js` (loaded directly via `<script>` tags or referenced as static assets).
- Additionally, `script.js` at the repository root (`jewellery-store-ecommerce/script.js`) is a leftover static prototype file with hardcoded mock data.

---

## 5. Database Readiness Assessment (Phase 3)

### Readiness Status: **READY**

Even though no raw `.sql` phpMyAdmin export file exists in the repository, the database structure is **fully defined, verifiable, and pre-seeded** through:
1. **Complete Laravel Schema Migrations (`database/migrations/`):**
   - `2026_03_25_000000_create_unified_production_schema.php` (643 lines) defining **35 tables** with complete column types, enums, JSON fields, foreign keys, and composite indexes.
   - `2026_03_28_150722_update_cms_pages_type_enum.php` updating `cms_pages.type` to support `'page'`, `'blog'`, `'press_release'`, `'exhibition'`, `'insight'`.
   - `2026_04_21_000000_update_2fa_default_on_employees.php` setting `employees.two_factor_enabled` default to `false`.
2. **21 Database Seeders (`database/seeders/`):**
   - `DatabaseSeeder.php` orchestrates 20 seeders including `CategorySeeder` (5 main jewellery categories, 25 subcategories), `ProductSeeder` (100 luxury jewellery items with PHP prices, SKU, weight, material, gemstone, care instructions), `EmployeeSeeder`, `FilipinoUserSeeder`, `OrderSeeder`, `OrderItemSeeder`, `ProductReviewSeeder`, `CmsNewsroomSeeder`, `ShippingMethodSeeder`, `PaymentGatewaySeeder`, `AppointmentSeeder`, and `NewsletterSubscriptionSeeder`.
3. **Populated Local SQLite Database (`database/database.sqlite`):**
   - A 774 KB pre-migrated and seeded SQLite database file is already present on disk and active in `.env:11`.

### 5.1 Complete Verified Table Inventory (35 Tables)
- **Identity, Auth & RBAC (5):** `users`, `employees`, `admin_permissions`, `magic_link_tokens`, `archived_users`
- **Product Catalogue & Social Proof (4):** `categories`, `products`, `product_reviews`, `product_popularity`
- **Shopping Cart & Wishlist (3):** `guest_sessions`, `cart_items`, `wishlist_items`
- **Orders, Fulfillment & After-Sales (6):** `orders`, `order_items`, `order_fulfillment`, `order_activities`, `inventory_movements`, `returns_repairs`
- **CMS, Boutique & Customer Engagement (5):** `cms_pages`, `contact_messages`, `notifications`, `newsletter_subscriptions`, `appointments`
- **Payments, Shipping, System Config & Audit (5):** `payment_gateways`, `payment_methods`, `shipping_methods`, `settings`, `audit_logs`
- **Laravel Framework Infrastructure (7):** `sessions`, `password_reset_tokens`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

### 5.2 Confirmed Code-vs-Schema Discrepancies (Must Be Resolved in Migration Plan)
Comparing the schema in `database/migrations/` against Eloquent models and controllers in `app/` revealed **8 concrete discrepancies**:

1. **Orphan `Cart` Model & Broken `/api/cart/cleanup` Route:**
   - `app/Models/Cart.php:15-56` and `routes/api.php:348-359` query a `carts` table and `cart_items.cart_id` column. Neither exists in `2026_03_25_000000_create_unified_production_schema.php` (`cart_items` uses `user_id` and `session_id` directly).
2. **Orphan `Wishlist` Model:**
   - `app/Models/Wishlist.php:11-28` references table `wishlists`, whereas the migration creates `wishlist_items` (used by `app/Models/WishlistItem.php`).
3. **Missing `Coupon` Model & Tables:**
   - `app/Models/Order.php:79-84` defines a `coupons()` `BelongsToMany` relationship referencing `App\Models\Coupon` and pivot table `coupon_usage`, neither of which exists in the codebase or migrations.
4. **Legacy `products.room_category` Queries (SQL Error if `room` Filter Used):**
   - `app/Http/Controllers/ProductController.php:23-30`, `routes/api.php:58-64`, and `routes/web.php:500-507` execute `->whereNotNull('room_category')->whereJsonContains('room_category', $roomValue)`. The `products` table (`L152-197`) has no `room_category` column (leftover from `davids-wood-furniture`).
5. **Mismatched `employees` Columns & Broken `Employee::active()` Scope:**
   - `app/Models/Employee.php:15,26-28,128-131` and `app/Models/Admin.php:17,28-29,38` reference `employee_id`, `hire_date`, `salary`, `employment_status`, and `notification_preferences`. The `employees` migration (`L103-126`) only defines `status` (`enum('active','inactive','suspended')`) and omits those 5 columns.
6. **Missing Columns & Wrong Table Name in `Admin\MessageController`:**
   - `app/Http/Controllers/Admin/MessageController.php:17,65,85-87,139` and `app/Models/ContactMessage.php:16-21` query/update `internal_notes`, `tags`, `assigned_to`, `responded_at`, `responded_by` on `contact_messages` and validate `'assigned_to' => 'exists:admins,id'`. The `contact_messages` table (`L346-358`) only has `admin_notes` and `read_at`, and the admin table is `employees`, not `admins`.
7. **Unmapped Address Columns in `User::$fillable`:**
   - `app/Models/User.php:32-42` includes `date_of_birth`, `address_line_1`, `address_line_2`, `state`, `postal_code`, `country` in `$fillable`, whereas `users` (`L74-101`) uses Philippine address columns (`street`, `barangay`, `city`, `province`, `region`, `zip_code`).
8. **MySQL-Specific `MONTH()` SQL Function on SQLite:**
   - `app/Http/Controllers/Admin/DashboardController.php:104,110` executes `selectRaw('MONTH(created_at) as month, ...')`, which works on MySQL/MariaDB (XAMPP) but throws a SQL error when `DB_CONNECTION=sqlite` is active.

---

## 6. XAMPP and Runtime Environment Assessment (Phase 4)

### 6.1 Known Runtime Requirements
- **PHP Version:** `^8.2` (`composer.json:9`, `Dockerfile:1`).
- **Required PHP Extensions:** `pdo_mysql`, `pdo_sqlite`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `intl`, `curl`, `openssl` (`Dockerfile:7-22`).
- **Web Server / URL Rewriting:** Apache with `mod_rewrite` and `mod_vhost_alias` enabled (`public/.htaccess`, `.apache/eclore.conf`, `README.md:238-245`).
- **Node.js & Asset Bundler:** Node.js `>=18.x` with npm and Vite `^7.0.4` (`package.json:21`, `README.md:141`).

### 6.2 Available Configuration Resources
- `.env` and `.env.example` (`jewellery-store-ecommerce/.env`, `.env.example`).
- `.apache/eclore.conf` — Apache VirtualHost configuration for `eclore.test` and `admin.eclore.test`.
- `public/.htaccess` — Standard Laravel rewrite rules.
- `Dockerfile`, `docker-compose.yml`, `docker-compose.prod.yml`, `nixpacks.toml`, `railway.json`, `Procfile`.
- `README.md` (2,457 lines) — Contains detailed Windows XAMPP setup instructions, hosts file rules, SSL generation steps, and database migration commands.

### 6.3 Potential Runtime Blockers & Environment Mismatches
1. **SQLite vs. XAMPP MySQL Switch:**
   - `.env:11` is currently set to `DB_CONNECTION=sqlite` (with `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=eclore_jewellery`, `DB_USERNAME=root`, `DB_PASSWORD=` commented out on lines 12–16).
   - If running against XAMPP MySQL, `.env` must be switched to `DB_CONNECTION=mysql` and `php artisan migrate --seed` executed against the `eclore_jewellery` MySQL schema. Conversely, if staying on SQLite locally, `DashboardController.php:104` (`MONTH(created_at)`) will fail on admin dashboard load.
2. **Hardcoded Paths in `.apache/eclore.conf` and `README.md`:**
   - `.apache/eclore.conf:10,13,34,37` hardcodes `DocumentRoot "d:/Projects/eclore/public"`.
   - `README.md:210,225` hardcodes `DocumentRoot "C:/xampp/htdocs/davids-wood-furniture/public"`.
   - The actual workspace path on this machine is `c:/Users/Mike Angelo/OneDrive/Documents/jewellery-store/jewellery-store-ecommerce/public`.
3. **Subdomain & Port Routing Assumptions (`APP_URL=http://eclore.test:8080`):**
   - `.env:5` sets `APP_URL=http://eclore.test:8080`.
   - `app/Providers/AppServiceProvider.php:24-63` dynamically rewrites `app.url` based on the incoming request host/port, and `routes/web.php:42` registers admin routes on `admin.{domain}` (while also providing `/admin/*` fallback routes at `routes/web.php:848`).
4. **Public Storage Symlink (`public/storage`):**
   - Listed in `.gitignore:18`; `php artisan storage:link` is required for locally uploaded images in `storage/app/public` to resolve under `/storage/*`.

---

## 7. Configuration and Security Considerations (Phase 5)

*(Note: All configuration files were inspected in read-only mode. No secret values, keys, or tokens are reproduced in this report.)*

### 7.1 Sensitive & Configuration Files Inventory
- `jewellery-store-ecommerce/.env` — Present on disk, properly excluded via `.gitignore:3`. Contains a generated `APP_KEY` and local development settings (`DB_CONNECTION=sqlite`, `MAIL_MAILER=log`, `FILESYSTEM_DISK=local`, `SESSION_DRIVER=database`). External credentials (`AWS_*`, `PUSHER_*`) are empty.
- `jewellery-store-ecommerce/.env.example` — Template for local XAMPP/MySQL setup (`DB_DATABASE=eclore_jewellery`, `DB_USERNAME=root`, empty `DB_PASSWORD`, placeholder keys for AWS, Pusher, and Google OAuth).
- `jewellery-store-ecommerce/env.production.template` — Template for production deployment (`eclorejewellery.shop`), containing placeholder strings only.

### 7.2 Security & API Findings for the Migration Plan
1. **Cross-Origin Resource Sharing (`config/cors.php:18-33`):**
   - Currently configured with `'paths' => ['api/*', 'sanctum/csrf-cookie']`, `'allowed_origins' => ['*']`, and `'supports_credentials' => false`.
   - **Migration Impact:** An Ionic frontend running on a separate origin (`http://localhost:8100`, `capacitor://localhost`, or `ionic://localhost`) cannot use cookie-based sessions when `supports_credentials` is `false` and `allowed_origins` is `['*']`. The migration plan should introduce **Laravel Sanctum Bearer Token Authentication** for Ionic API consumption.
2. **Sensitive Session/Cookie Debug Leak in `CartController` (`app/Http/Controllers/CartController.php:65-71,177-184`):**
   - Both `GET /api/cart` and `POST /api/cart/add` return a `debug` payload in their JSON response containing `session_id`, `user_id`, `request_cookies` (all request cookies), and `session_cookie`. This should be stripped during API refactoring for Ionic.
3. **AWS S3 Environment Key Mismatch (`config/filesystems.php:50-51`):**
   - `config/filesystems.php:50-51` reads `env('AWS_S3_ACCESS_KEY_ID')` and `env('AWS_S3_SECRET_ACCESS_KEY')`, whereas `.env`, `.env.example`, and `env.production.template` define `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`.
4. **CSRF Exemption for Payment Webhooks (`bootstrap/app.php:23-25`):**
   - Properly exempts `webhooks/xendit` from CSRF verification; Xendit webhook callback token verification is handled in `XenditPaymentController.php`.
5. **External Chatbot Script Injection (`resources/views/layouts/app.blade.php:16`):**
   - Loads `https://www.noupe.com/embed/019d659c5e1473f7bd0f24cf93f8f30305c9.js` globally on all storefront pages. The migration plan must decide whether this third-party widget should be retained or excluded in the Ionic app.

---

## 8. UI/UX Audit Readiness (Phase 6)

### Readiness Status: **READY**

The repository contains all source templates, stylesheets, design tokens, typography definitions, icons, and visual assets required to perform a comprehensive UI/UX inventory and component mapping to Ionic without needing external design files.

### 8.1 Verified Design System & Tokens (Customer Storefront)
- **Visual Identity:** Luxury editorial jewellery house (**Éclore**), characterized by high-contrast minimalist layouts, generous whitespace, sharp 1px borders, and editorial photography.
- **Typography (`resources/views/layouts/app.blade.php:17-27`, `public/frontend/style.css:11-23`):**
  - **Primary Editorial Serif:** `'Playfair Display', serif` (`.font-playfair`) — used for hero headlines, product titles, and section headers.
  - **Primary Technical Monospace:** `'Azeret Mono', monospace` (`.font-azeret`) — used for navigation links, category tabs, uppercase micro-copy (`tracking-[0.2em]`), SKU/metadata, and buttons.
- **Color Palette (`resources/views/home.blade.php:8-120`, `public/frontend/style.css:14`, `resources/views/partials/navbar.blade.php:22`):**
  - **Primary Background:** Warm Cream `#F3EFE7` (`rgb(243, 239, 231)`) and Off-White `#FCFCFC`.
  - **Primary Gold Accent:** `#B6965D` (interactive hover states, active category borders, price highlights) and muted gold `#C5B391`.
  - **Primary Ink / Dark Surface:** `#1A1A1A` and `#000000`.
- **Navigation & Interactive Patterns (`resources/views/partials/navbar.blade.php`, `offcanvas-*.blade.php`, `modal-*.blade.php`):**
  - Two-tier fixed header (`h-16` top utility bar with live search + centered logo + action badges; bottom category bar with full-width hover mega-menus).
  - Slide-over offcanvas drawers (`ion-menu` / `ion-modal` sheet equivalents in Ionic) for Cart (with per-item checkout selection checkboxes), Wishlist, and Notifications.
  - 6 global modals (Login, Signup with real-time validation & password strength meter, Search, Quick View, Verify Email, Alert).

### 8.2 Verified Design System (Admin Portal)
- **Layout (`resources/views/admin/layouts/app.blade.php:1-250`, `public/css/admin.css`):**
  - Responsive collapsible sidebar + top header with **Light/Dark mode** (`darkMode: 'class'`), using `Figtree` font and custom brand palette (`brand-dark: #0D1E1E`, `brand-green: #52734F`, `brand-beige: #D3D0CF`, `brand-brown: #6C464E`, `brand-rose: #96616B`).

### 8.3 Verified Media & Static Assets
- **Local Luxury Jewellery Assets (`public/frontend/assets/`):**
  - 14 high-resolution WebP/AVIF/JPG assets (`hero-bg.webp`, `about-hero.webp`, `craftsmanship.webp`, `sketch.webp`, `necklace.webp`, `ring.webp`, `earrings.webp`, `bracelet.webp`, `category-necklaces.webp`, `category-rings.webp`, `category-earrings.webp`, `category-bracelets.webp`, `favicon.png`, `placeholder-jewelry.jpg`).
- **External Product Image URLs in Seeders (`database/seeders/ProductSeeder.php`):**
  - Seeded products reference curated external image URLs (with automatic fallback to `/frontend/assets/placeholder-jewelry.jpg` in `app/Models/Product.php:154-201`), requiring an active internet connection when rendering seeded product thumbnails in a browser.

---

## 9. Missing Resources and Their Impact (Phase 7)

| Resource / Information | Current State in Repository | Impact on Migration-Planning Audit | Severity |
|---|---|---|---|
| **Target Ionic Scope & Framework Decision** | Unspecified (Customer Storefront only vs. Customer + Admin Portal; Ionic Angular vs. React vs. Vue; Capacitor Native vs. Web/PWA) | Affects component mapping, routing architecture, and effort estimation in the migration plan | **High (Architectural Clarification)** |
| **Laravel Sanctum / Stateless API Auth** | Not installed (`composer.json` lacks `laravel/sanctum`; `/api/*` uses stateful web session cookies) | Must be designed as a core backend refactoring task in the migration plan | **Medium (Planned Refactoring)** |
| **8 Schema-vs-Code Discrepancies** | Present in codebase (see Section 5.2: `room_category`, `Employee::active()`, `MessageController` columns, `Cart`/`Wishlist`/`Coupon` models) | Could cause runtime 500 errors if affected endpoints are called without cleanup; must be listed in backend remediation plan | **Medium (Documented in Audit)** |
| **Raw `.sql` Dump File** | Absent (replaced by 3 Laravel migrations, 21 seeders, and `database/database.sqlite`) | **Zero blocker impact** — Laravel migrations and seeders provide superior, version-controlled schema and seed fidelity | **None** |
| **Live Third-Party Sandbox Keys** (`.env`) | Blank for Google OAuth, AWS S3, Pusher; Xendit configured via DB/env | Prevents live end-to-end sandbox testing of OAuth/webhooks, but code paths are 100% inspectable | **Low (For Planning Phase)** |

---

## 10. Required, Recommended, and Optional Preparation Tasks (Phase 8)

### Category A: Required Before (or as Inputs to) the Full Audit
*No missing source code or database files block the audit.* However, providing answers to the following **3 architectural scope questions** when running the full migration-planning master prompt will prevent an ambiguous or mis-scoped migration plan:

1. **Portal Migration Scope (Customer Storefront Only vs. Customer + Admin Backoffice):**
   - *Why it is useful:* The repository contains 36 Customer views and 50+ Admin views across 18 admin modules. Often, e-commerce migrations convert the **Customer Storefront** to an Ionic mobile/hybrid app while keeping the **Laravel Admin Backoffice** on web Blade (or migrating it in a separate phase).
   - *Which part of the audit it supports:* Screen inventory, route mapping, API gap analysis, and effort estimation.
   - *Can the audit proceed without it?* Yes, by auditing both portals and separating Customer Storefront (Phase 1) from Admin Backoffice (Phase 2), but confirming upfront focuses the plan.
   - *How to provide it:* Specify in the master prompt whether the Ionic app targets Customer only or Customer + Admin.
2. **Target Ionic Framework Flavor (Angular, React, or Vue):**
   - *Why it is useful:* Determines state management patterns (e.g., NgRx/Signals vs. Zustand/Redux/React Query vs. Pinia), routing syntax (`IonRouterOutlet`), and project scaffolding structure.
   - *Which part of the audit it supports:* Target architecture design, component hierarchy, and state management mapping.
   - *Can the audit proceed without it?* Yes (using framework-agnostic Ionic Web Components `ion-*` specifications), but selecting the framework produces directly actionable architectural blueprints.
   - *How to provide it:* State your preferred framework (`Ionic + Angular`, `Ionic + React`, or `Ionic + Vue`) when invoking the master prompt.
3. **Target Deployment Platforms (Capacitor iOS/Android Native, PWA, or Both):**
   - *Why it is useful:* Native Capacitor apps require deep-link schemes for Xendit payment redirects (`/payments/xendit/return/*`), native Google OAuth plugins, and token storage via Capacitor Preferences/SecureStorage rather than browser cookies.
   - *Which part of the audit it supports:* Authentication redesign, payment flow redirection, and native plugin architecture.
   - *Can the audit proceed without it?* Yes, by planning for Capacitor + Web compatibility.
   - *How to provide it:* Confirm target platforms when launching the master prompt.

### Category B: Strongly Recommended
1. **Verify Which Database Engine to Target in Local Runtime (XAMPP MySQL vs. Included SQLite):**
   - *Why it is useful:* `.env` currently uses `database/database.sqlite` (`DB_CONNECTION=sqlite`), while `DashboardController.php:104` uses MySQL's `MONTH()` function.
   - *Which part of the audit it supports:* Backend runtime verification and SQL dialect compatibility.
   - *Can the audit proceed without it?* Yes — static schema and query analysis is already complete.
   - *How to obtain it safely:* Decide whether local API testing during migration will run against XAMPP MySQL (`DB_CONNECTION=mysql`, `DB_DATABASE=eclore_jewellery`) or SQLite.
2. **Confirm Disposition of Legacy `davids-wood-furniture` Artifacts & Broken Schema References:**
   - *Why it is useful:* Confirms that leftover `room_category` filters (`ProductController.php:23`), unused `Adyen` SDK (`composer.json:10`), and orphan `Cart`/`Wishlist`/`Coupon` models can be safely deprecated rather than migrated to Ionic.
   - *Which part of the audit it supports:* API surface pruning and dead-code elimination.
   - *Can the audit proceed without it?* Yes — the audit can flag them for deprecation.
   - *How to obtain it safely:* Review Section 5.2 of this report and confirm cleanup approval.

### Category C: Optional (Useful Later During Execution, Not Needed for Planning)
1. **Sandbox Credentials for Xendit, Google OAuth, Pusher, and AWS S3:**
   - *Why it is useful:* Enables live end-to-end integration testing of hosted payment redirects, social login, real-time notifications, and cloud media uploads during the implementation phase.
   - *Which part of the audit it supports:* Post-migration integration testing.
   - *Can the audit proceed without it?* Yes — 100% of the integration code is readable in `app/Http/Controllers/` and `app/Services/`.
   - *How to obtain it safely:* Configure sandbox keys in local `.env` (never commit secrets to Git) when implementation begins.
2. **Live Browser Screenshots of Current Storefront & Admin Screens:**
   - *Why it is useful:* Provides visual regression baselines alongside the CSS/Blade code.
   - *Which part of the audit it supports:* Visual QA comparison after Ionic screens are built.
   - *Can the audit proceed without it?* Yes — all styling, fonts, colors, and layouts are explicitly defined in `resources/views/` and `public/frontend/style.css`.
   - *How to obtain it safely:* Run `php artisan serve` locally and capture screenshots prior to UI implementation.

---

## 11. Overall Readiness Status (Phase 9)

### Status: **READY WITH LIMITATIONS**

#### Evidence Supporting Readiness
1. **Complete Application Codebase:** All 31 controllers, 29 models, 11 middleware classes, 86+ Blade templates, custom CSS/JS modules, and route files (`routes/web.php`, `routes/api.php`) are present and readable in `c:\Users\Mike Angelo\OneDrive\Documents\jewellery-store\jewellery-store-ecommerce`.
2. **Complete Database Schema & Seeders:** All 35 tables are defined in `database/migrations/`, backed by 21 seeders in `database/seeders/` and an existing `database/database.sqlite` database.
3. **Complete Frontend Design System & Assets:** Typography (`Playfair Display` + `Azeret Mono`), color tokens (`#F3EFE7`, `#B6965D`, `#1A1A1A`), responsive layouts, drawers, modals, and local WebP luxury jewellery imagery (`public/frontend/assets/`) are fully intact.
4. **Installed Local Dependencies:** `vendor/`, `node_modules/`, and Vite build artifacts (`public/build/manifest.json`) are already present on disk.

#### Documented Limitations
1. **Architectural Scope Decisions Pending:** Target Ionic framework flavor (Angular/React/Vue), portal scope (Customer Storefront vs. Customer + Admin), and native Capacitor requirements are not specified in the repo and should be stated when invoking the master migration prompt.
2. **Stateful Cookie API Architecture:** Existing `/api/*` routes depend on Laravel web session cookies (`credentials: 'include'`) and CSRF tokens rather than stateless Bearer tokens (`laravel/sanctum`), requiring an API authentication refactoring track in the migration plan.
3. **8 Code-vs-Schema Inconsistencies:** Minor model/query mismatches (detailed in Section 5.2) exist in the current PHP codebase and must be remediated as part of the backend API preparation phase.

---

## 12. Recommended Next Steps Before Running the Master Prompt

1. **Do Not Install or Modify Anything Yet:** The repository already has all files, migrations, seeders, and dependencies required for the full migration-planning audit.
2. **Decide on the 3 Scope Parameters (Category A):**
   - **Portal Scope:** Customer Storefront only (recommended Phase 1) vs. Customer Storefront + Admin Backoffice.
   - **Ionic Stack:** Ionic Angular, Ionic React, or Ionic Vue.
   - **Deployment Target:** Capacitor iOS/Android mobile app, responsive Web/PWA, or both.
3. **Proceed Directly to the Full Migration-Planning Master Prompt:**
   - Pass those 3 scope choices along with your master prompt, and instruct the planner to incorporate the findings in this report—specifically:
     - Introducing **Laravel Sanctum** token authentication and a dedicated guest cart token header (`X-Guest-Session-Id`) to decouple `/api/*` from browser session cookies.
     - Deduplicating the `/api/*` routes between `routes/web.php` and `routes/api.php`.
     - Fixing the 8 schema-vs-code discrepancies documented in Section 5.2 and removing `davids-wood-furniture` legacy artifacts.
     - Preserving the **Éclore** luxury design tokens (`Playfair Display`, `Azeret Mono`, `#F3EFE7` warm cream, `#B6965D` gold, `#1A1A1A` ink) in the target Ionic theme variables (`theme/variables.css`).
