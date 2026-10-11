# Master Prompt: Éclore Laravel 12 → Ionic Migration Planning and Codebase Audit

## Role

Act as a **senior software architect, Laravel 12 backend engineer, Ionic application architect, UI/UX migration specialist, QA lead, and application-security reviewer**. Your task is to thoroughly inspect this repository and produce an evidence-based, implementation-ready plan for migrating the Éclore Jewellery Store customer experience to Ionic while preserving its existing features, brand identity, data integrity, and business rules.

This is a **planning and audit task only**. Do not implement the migration in this run.

## Repository-specific context

The preliminary discovery report describes this project as a Laravel 12 e-commerce application, not a simple procedural PHP website. Verify that characterization against the actual files before relying on it.

Known findings from the preliminary report include:

- Laravel 12 and PHP `^8.2` backend.
- Blade-rendered customer storefront and a separate admin backoffice.
- Existing `routes/web.php` and `routes/api.php` routes, including reported overlapping/duplicated API endpoints.
- Existing frontend JavaScript/CSS, Vite build setup, custom styling, product media, and a luxury editorial visual identity.
- SQLite currently selected in the local `.env` according to the report, while `.env.example` documents MySQL/XAMPP setup. A populated SQLite file, Laravel migrations, and seeders are reported to exist.
- Existing customer features include catalogue discovery, product details, cart, wishlist, authentication, checkout, payment processing, Philippine address lookup, order tracking, receipts/invoices, reviews, returns/repairs, and appointments.
- Existing admin features include analytics, catalogue/inventory management, order fulfillment, returns/repairs, CMS/newsroom, review moderation, staff permissions, shipping/payment settings, inquiries, and audit logging.
- The report flags cookie/session-coupled API behavior, a possible lack of mobile-friendly token authentication, eight model/schema or query discrepancies, legacy furniture-platform references, and third-party integrations whose live credentials may not be configured.
- Reported visual identity: Playfair Display and Azeret Mono typography, warm cream `#F3EFE7`, gold `#B6965D`, dark ink `#1A1A1A`, generous whitespace, thin borders, and editorial jewellery imagery.

These are **leads, not unquestionable facts**. Inspect the repository and validate every important finding, file path, route, framework version, and discrepancy. Record any differences between the report and the current codebase. Do not copy secrets into reports.

## Main objective

Produce a detailed migration blueprint for an Ionic frontend that consumes the existing Laravel backend through a clearly defined HTTP API. Keep the current backend and database architecture wherever practical. Identify only the backend changes genuinely needed to support Ionic; do not assume that the whole Laravel application must be rewritten.

The plan must preserve, as applicable:

1. The current feature set and business rules.
2. The existing Éclore brand, information architecture, screen layouts, visual hierarchy, typography, colors, assets, states, and interaction behavior as closely as possible.
3. Authentication, authorization, customer/admin separation, inventory consistency, order lifecycle, payment integrity, and existing data.
4. Current local development options, including XAMPP/Apache and MySQL/MariaDB where required, while accounting for the SQLite configuration reported in the discovery report.
5. A maintainable separation between Ionic UI, API/data services, and Laravel domain/business logic.

Do not promise pixel-identical rendering across different platforms. Define visual-parity targets and document any adaptation needed for mobile navigation, safe areas, accessibility, and platform conventions.

## Scope and unresolved decisions

Do not halt the audit just because the product owner has not yet specified every target detail. Analyze the full application, make explicit assumptions, compare reasonable options, and recommend a default with rationale.

### Portal scope

- Inventory **both** the Customer Storefront and Admin Backoffice.
- As the initial planning default, prioritize migrating the **Customer Storefront** to Ionic.
- Assess whether the Admin Backoffice should remain as the existing Laravel Blade web portal, receive a responsive treatment, or be migrated to Ionic in a separate phase. Do not automatically rebuild all admin screens in Ionic unless a documented project requirement demands it.
- Clearly label which customer/admin features are in Phase 1, later phases, and out of scope. Explain what requirement or evidence would change this recommendation.

### Ionic framework choice

- Evaluate Ionic Angular, Ionic React, and Ionic Vue against the repository, the project's maintainability needs, ecosystem fit, likely implementation effort, team skill assumptions, and any explicit school/project constraints found in repository documentation.
- Do not assume that the existing use of vanilla JavaScript, jQuery, Alpine.js, or Blade proves that one Ionic binding is required.
- Recommend one target stack, but document alternatives and trade-offs. If no binding is specified, produce the audit and an actionable framework-neutral architecture while marking binding-specific details as recommendations.

### Deployment target

- Assess responsive web/PWA, Android/iOS via Capacitor, or both.
- Recommend a practical default based on repository evidence and the migration objective. Distinguish requirements common to all targets from work required only for Capacitor/native builds.
- If the final target is uncertain, plan so the decision can be made without blocking source-code discovery. Document platform-specific assumptions, especially authentication storage, deep links, payment return URLs, file downloads, and native plugins.

## Non-negotiable operating rules

1. **Read-only in this run.** Do not create, edit, delete, rename, or format application files. Do not initialize Ionic, install/remove/update dependencies, run migrations, seed or reset a database, alter `.env`, change Apache/hosts settings, or execute destructive commands.
2. You may inspect source, manifests, lockfiles, route definitions, migrations, seeders, tests, documentation, and local configuration. Do not print or copy secret values, session data, credentials, private keys, or customer information into the report.
3. Treat `.env`, `database/database.sqlite`, uploaded media, logs, and local user data as sensitive. Do not expose their contents beyond the minimum metadata needed for the audit.
4. Never connect the Ionic app directly to MySQL/MariaDB or expose database credentials to frontend code. All data access must go through the Laravel backend/API.
5. Do not replace Laravel business logic with duplicated frontend logic. Keep pricing, permissions, inventory updates, order state transitions, payment verification, and other authoritative rules on the backend.
6. Do not claim a feature is working merely because a file exists. Separate static-code evidence, automated-test evidence, and manually/runtime-verified behavior.
7. Do not make a proposed security change as if it were already implemented. Label findings, recommendations, dependencies, and implementation tasks distinctly.
8. Do not remove or deprecate legacy models, routes, packages, fields, or assets simply because they look unused. First trace references, tests, runtime paths, data implications, and intended behavior; propose cleanup only with evidence and migration safeguards.
9. Do not assume that all reported schema discrepancies are harmless or that all are confirmed. Recheck each against current migrations, models, controllers, route bindings, tests, and query paths. Document evidence and confidence.
10. Do not overwrite an existing report or plan document without inspecting it and preserving its contents. If a target output already exists, create a clearly named versioned alternative or report the conflict.
11. Do not ask the user to supply third-party sandbox credentials for this planning task. Mark live integration behavior as unverified if credentials are not available.
12. Stop after the planning documents are produced. Do not start implementation at the end of this run.

## Investigation workflow

### Phase 1 — Establish the true repository root and validate the discovery report

1. Identify the actual Git repository root. The workspace may contain a parent folder and a nested cloned repository; do not assume the current directory is the Git root.
2. Locate the pre-migration discovery report if it is present. Read it, but validate key findings against source files instead of treating the report as authoritative.
3. Inspect at minimum: `README.md`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, Vite configuration, Laravel bootstrap/configuration, routes, controllers, models, services, middleware, Blade templates, frontend JS/CSS, migrations, seeders, factories, tests, asset folders, `.gitignore`, `.env.example`, Apache configuration, and Docker/deployment files where present.
4. Inspect Git status and ignore rules only for audit purposes. Do not expose secret values or assume files tracked in Git are safe.
5. Record actual framework/runtime versions and clearly distinguish repository configuration from the current machine's runtime state.

### Phase 2 — Map current architecture and every user-facing feature

1. Build an inventory of customer screens, admin screens, layouts, shared partials, modal/drawer states, route names/URLs, form flows, and major interactive behaviors.
2. Map routes to controllers, models/services, validation rules, middleware/guards/policies, database tables, and relevant frontend modules.
3. Identify server-rendered Blade behavior that will need a true Ionic screen rather than merely wrapping the existing webpage in a WebView.
4. Inventory every major workflow end to end, including success, loading, empty, validation-error, authorization-error, failure, cancellation, and retry states.
5. Identify dynamic content and reusable UI elements: catalogue cards, filters/sorting/pagination, product gallery, search, cart/wishlist drawers, authentication forms, address cascading selector, checkout steps, payment states, receipt downloads, order tracking, reviews, returns/repairs, appointments, and relevant admin workflows.
6. Identify email, PDF, file upload/download, push/realtime notification, OAuth, payment gateway, external chatbot, and other integration touchpoints.
7. Trace duplicate JS files and API routes before recommending consolidation. Note which copy/route actually runs and how that was verified.

### Phase 3 — Backend, database, and API readiness

1. Treat Laravel as the existing system of record unless repository evidence establishes otherwise. Design a compatibility layer/API strategy rather than proposing a wholesale backend replacement.
2. Inventory existing endpoints and provide an API coverage map: endpoint, HTTP method, purpose, request validation, response structure, auth/guard, side effects, database dependencies, and target Ionic screens.
3. Compare `routes/api.php` with API-like routes under `routes/web.php`. Identify actual overlaps, middleware differences, naming conflicts, response differences, and risk before recommending deduplication. Do not consolidate blindly.
4. Inspect current session/cookie and CSRF dependencies, guest cart identity, authentication guards, email/magic-link flows, two-factor authentication, OAuth, password resets, and admin authorization.
5. Evaluate appropriate authentication approaches for each target: browser/PWA vs. Capacitor native. Consider supported Laravel options such as Sanctum only after confirming versions and compatibility. Do not treat Bearer-token authentication as automatically required for every target. Explain cookie-based vs. token-based trade-offs and the security implications of token issuance, revocation, expiry, secure storage, CSRF, CORS, and logout.
6. For native builds, do not recommend storing long-lived secrets or authentication tokens in ordinary browser `localStorage`. Plan secure storage using an appropriate supported mechanism and note any dependency or native-plugin implications.
7. Design a guest-cart strategy that preserves anonymous cart behavior and safely merges it with an authenticated user's cart. Treat any proposed header such as `X-Guest-Session-Id` as a design option requiring server-side validation, not as trusted identity by itself.
8. Check request/response conventions, pagination/filter semantics, error formats, file/image URL generation, validation behavior, rate limiting, CORS, environment-specific base URLs, and API versioning.
9. Inspect payment logic end to end. Preserve server-side verification, webhook authenticity checks, order/payment reconciliation, idempotency, redirect/return handling, and cancellation/failure states. Determine which gateway code is active and which integrations appear legacy; do not assume from installed packages alone.
10. Inspect schema migrations, seeders, model relationships, casts, fillable fields, query builders, factories, and tests. Validate every reported model/schema/query discrepancy, including but not limited to Cart, Wishlist, Coupon, `room_category`, employee fields/scopes, admin message fields/table names, user address fields, and SQLite/MySQL SQL-function compatibility.
11. Distinguish SQLite, MySQL, and XAMPP/MariaDB behavior. Retain the current database engine and data by default; do not require switching engines just to plan the Ionic frontend. If deployment/assessment requires MySQL, specify how to verify compatibility safely without running migrations or altering data in this audit.
12. Never put database credentials or privileged operations in the Ionic application. The Ionic client calls Laravel endpoints only.
13. Mark untested external services honestly. Code may be inspected even when sandbox credentials are missing; live integration behavior cannot be claimed verified.

### Phase 4 — UI/UX parity and Ionic mapping

1. Document the current storefront design system from source: typography, color tokens, spacing, borders, image aspect ratios, navigation, breakpoints, motion, hover/focus states, forms, modal/drawer dimensions, badges, and responsive rules.
2. Map current screens and shared partials to Ionic pages/components. Preserve the existing information architecture and visual style, not generic default Ionic styling.
3. Recommend equivalents for complex desktop/web patterns such as two-tier navigation, mega-menus, off-canvas cart/wishlist/notifications, modals, product quick view, multi-step checkout, tables, charts, and admin sidebar. Explain any mobile-specific adaptation rather than silently discarding features.
4. Preserve actual assets and fonts where licensing and delivery permit. Avoid replacing real jewellery product imagery with generic placeholder imagery. Identify any external image URLs, font/CDN dependencies, or third-party scripts that need a reliable fallback or approved alternative.
5. Include accessibility: semantic controls, keyboard navigation where applicable, visible focus, contrast, text resizing, labels/errors, touch target sizing, reduced motion, and screen-reader names.
6. Define a visual regression method comparing equivalent existing and Ionic screens at matching viewport sizes. Include capture conditions, representative data, screenshot naming, and acceptance criteria. If existing pages cannot be run, document a source-derived baseline and the missing runtime evidence.
7. Account for safe areas, status/navigation bars, keyboard behavior, back navigation, offline/network states, and PWA/native differences when applicable.

### Phase 5 — Security, privacy, reliability, and test strategy

Audit and plan for:

- AuthN/AuthZ and separation of customer and admin identities/guards.
- Input validation, authorization checks, mass assignment, IDOR, CSRF/CORS, XSS, SQL injection risks, rate limiting, and safe error responses.
- Exposed diagnostic payloads, especially anything that could return session identifiers, cookies, tokens, or internal user IDs.
- Payment webhooks, signature/token validation, idempotency, order reconciliation, price/stock tampering, and duplicate checkout submissions.
- Secrets handling, local configuration, environment-specific URLs, TLS/HTTPS, secure storage, logging redaction, and production debug settings.
- File/image storage, uploads, downloads, external URLs, and access control.
- Regression coverage for business-critical workflows and database compatibility.

Do not perform active penetration testing, destructive testing, or production requests. Static security review and inspection of existing tests are in scope. Clearly prioritize issues by severity and evidence.

The test plan should distinguish unit, feature/integration, API-contract, UI/component, end-to-end, visual regression, accessibility, device/platform, performance, and security tests. Define which tests can be automated and which need credentials, devices, or manual review.

### Phase 6 — Migration options, sequencing, estimates, and acceptance criteria

1. Compare at least two practical migration approaches, such as incremental migration of the customer storefront while retaining Laravel Blade admin versus migrating both portals. Recommend one based on risk, effort, and requirements.
2. Separate work into milestones with dependencies, entry/exit criteria, rollback considerations, and measurable deliverables.
3. Provide estimates as ranges (for example, person-days or effort bands) with stated assumptions, not false precision. Identify tasks whose estimates depend on unresolved scope.
4. Identify parallelizable work and work that must happen in sequence.
5. Define acceptance criteria for feature parity, API behavior, authentication, payment integrity, database safety, UI/UX parity, accessibility, performance, and platform builds.
6. Include a staged cutover and rollback strategy. The current Laravel storefront should remain available until the Ionic app has passed agreed acceptance checks; avoid a big-bang replacement by default.

## Required deliverables

Create a folder named `migration-planning/` **inside the actual Git repository root**, unless an existing project convention provides a better documented location. Before creating anything, check whether files already exist and preserve them. These are planning documents only; do not change application code.

Create the following Markdown files:

### `01-codebase-audit.md`

Include repository-root confirmation, verified stack, application architecture, customer/admin route and screen inventories, source-to-feature mapping, API inventory, current integrations, dependency/asset inventory, runtime assumptions, test coverage observations, and a list of unverified findings. Cite repository paths and line numbers where practical.

### `02-ionic-architecture.md`

Include recommended Ionic binding and rationale, target platforms, application structure, routing, reusable components, state/data-fetching strategy, API client conventions, environment configuration, auth/session/token design by platform, guest cart strategy, file handling, payment return/deep-link handling, notification strategy, and how the existing Laravel backend remains responsible for business logic. Clearly label undecided assumptions and alternatives.

### `03-feature-migration-matrix.md`

Use a table with at least: current feature/screen/workflow, current route/controller/view/JS evidence, current backend/API dependency, target Ionic page/component, API gap or backend change, priority/phase, parity notes, risks, and verification/acceptance test. Cover customer features comprehensively and cover admin functions even if they are recommended to remain on Blade or be deferred.

### `04-migration-roadmap.md`

Provide sequenced phases, goals, dependencies, deliverables, acceptance/exit criteria, effort ranges, risks, rollback checkpoints, and a recommended implementation order. Include setup and environment verification, backend/API preparation, Ionic foundation, feature slices, quality gates, and staged release.

### `05-risk-and-testing-plan.md`

Include prioritized functional, visual, architecture, database, security, integration, platform, operational, and schedule risks. For each risk include evidence, impact, likelihood/severity, mitigation, validation method, and owner/phase where possible. Include the full test matrix and visual-parity approach.

### `06-agent-execution-guide.md`

Write a practical guide for a future coding agent to implement the plan safely in small phases. Include repository boundaries, rules for keeping Laravel and database behavior intact, files likely to be affected (as evidence indicates), dependency order, required tests before/after each phase, checkpoints for review, expected outputs, rollback strategy, and clear stop conditions. This document must not instruct an agent to make broad uncontrolled rewrites.

## Report-specific items that must be handled explicitly

In the relevant deliverables, address these findings from the preliminary discovery report and verify them against current source:

1. Whether the current `.env` selects SQLite while `.env.example` describes MySQL/XAMPP, and what that means for runtime verification. Do not modify either configuration.
2. Whether the reported model/schema/query discrepancies are real, reachable, and used. Record each individually with file/line evidence, impact, priority, and recommended remediation. Do not silently resolve or ignore them.
3. Whether API endpoints are duplicated between `routes/web.php` and `routes/api.php`; compare actual middleware and behavior before proposing consolidation.
4. Whether the current API depends on web-session cookies and CSRF, and which auth strategy is appropriate for PWA versus Capacitor.
5. Whether cart debug responses expose session/cookie/user details; recommend removal/redaction if verified and include regression tests.
6. Whether local assets, external product image URLs, fonts/CDNs, and the third-party chatbot will work reliably in the target environment; identify replacement/fallback decisions needed.
7. Whether there are inherited furniture-platform references, hard-coded machine paths, duplicate JavaScript assets, unused packages, or legacy feature references. Propose cautious verification and cleanup steps, not automatic deletion.
8. How Xendit/payment return and webhook flows, Google OAuth, S3/local media storage, Pusher/realtime notifications, PDF receipts, and any other active integrations translate to the selected target platforms.
9. How the Éclore design tokens and current interaction patterns will be preserved in Ionic theme variables and custom components.
10. Which pre-migration findings remain uncertain because the report was static/read-only, external credentials are missing, or runtime access was unavailable.

## Expected analysis quality

- Be specific to this repository, not a generic Ionic migration checklist.
- Use actual file paths, class/function/route names, schema fields, and test names wherever supported.
- Separate **verified fact**, **inference**, **recommendation**, **open question**, and **implementation task**.
- Give each major finding a confidence level (high/medium/low) where the evidence is incomplete.
- Prioritize customer-visible and business-critical workflows, especially authentication, guest/authenticated cart, checkout, payment verification, orders, inventory, and admin controls.
- Recommend measurable acceptance criteria instead of vague claims such as “works correctly” or “looks the same.”
- State explicitly when behavior could not be verified. Do not fabricate test execution, screenshots, credentials, endpoints, or framework decisions.
- Keep the documentation navigable, with summaries, tables, and links between documents where useful.

## Final response after creating the documents

When finished, report:

1. The exact repository root inspected.
2. The paths of all documents created.
3. The top five migration findings that most affect architecture, risk, or effort.
4. The assumptions/recommendations made for portal scope, Ionic binding, and target platforms.
5. Any existing files that prevented writing or were preserved.
6. A clear statement that no application code, dependency, configuration, or database was modified.

**Begin now with repository and report verification. Complete the audit and create the six planning documents. Do not begin implementation.**
