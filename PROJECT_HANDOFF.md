# Handoff: Farm Management System (Laravel 12 + Inertia React)

Status: Parts 1 to 7 are written. Nothing has been executed in the authoring environment (no PHP, MySQL, or Composer). Run the checks in "First steps" before changing anything.

## First steps for whoever continues
1. Follow FULL_SETUP_GUIDE.md: install packages, migrate, seed, `npm run build`, `npm run dev`.
2. Create the database `farm_management_test` and run `php artisan test`. Fix failures (see PART7 guide, section 6).
3. Walk through the manual checks in FULL_SETUP_GUIDE.md and SETUP_GUIDE_PART6.md in the browser.
4. Record every failure and fix. Do not rewrite working modules.

## Stack and conventions
- Laravel 12, PHP 8.2+, MySQL, Inertia.js v2 (inertia-laravel), React, Vite, Tailwind v4 (@tailwindcss/vite).
- Session auth. users.role = admin | staff; users.is_active.
- Route protection: `role:admin` middleware (app/Http/Middleware/EnsureRole.php, alias registered in bootstrap/app.php). Policies (app/Policies) also enforce rules in controllers.
- Business logic in services: AnimalCodeGenerator, AnimalRegistrationService, SaleService, SalePaymentService, PurchaseService, ReportService, UserAccountService.
- Money: decimal columns; server arithmetic in integer kobo; display with formatNaira (en-NG, NGN).
- Animal codes: `{animal_types.code_prefix}-{5-digit sequence}`; sequence in animal_types.code_sequence, incremented under lockForUpdate.
- Inertia prop relations are camelCase (animalType, recordedBy, purchaseItem, saleItem). Columns are snake_case.
- Route parameter names must match controller variable names for implicit model binding. Animal types use {animalType}.
- Frontend URLs are hardcoded strings (no Ziggy). Keep them in sync with routes/web.php.
- Shared frontend pieces: Layouts/AppLayout.jsx (nav, admin-only Users link), Components/Field.jsx (input and button class constants), Components/Pagination.jsx, lib/format.js.

## File map by part
- Part 1: migrations 000001–000009; models; AnimalCodeGenerator; AnimalRegistrationService; SaleService; seeders.
- Part 2: AuthController; AnimalTypeController; AnimalController; requests; policies; layout; Login, AnimalTypes, Animals pages; HandleInertiaRequests; bootstrap/app.php; vite.config.js.
- Part 3: HealthRecordController; FeedingRecordController; requests; policies; pages.
- Part 4: migration 000010 sale_payments; SalePayment; SalePaymentService; PurchaseService; PurchaseController; SaleController; ExpenseController; requests; policies; pages.
- Part 5: DashboardController; ReportController; ReportService; Dashboard and Reports pages.
- Part 6: EnsureRole middleware; UserController; ProfileController; UserAccountService; user requests; Users and Profile pages; route protection; animal type route fix.
- Part 7: phpunit.xml with test DB; tests/TestCase.php safety guard; factories; 13 feature test files.

## Routes
Public: /login (guest only).
Signed in: / (dashboard) · /profile · /profile/password (PUT) · /animal-types (index) · /animals (resource, no delete) · /health-records · /animals/{animal}/health-records/create · /feeding-records · /animals/{animal}/feeding-records/create · /purchases (index, create, store, show) · /sales (index, create, store, show) · /sales/{sale}/payments · /expenses (no show, no delete) · /reports.
Admin only (role:admin): animal-types create/store/edit/update · DELETE /animals/{animal} · DELETE /expenses/{expense} · /users (resource) · PATCH /users/{user}/active · PUT /users/{user}/password.

## Business rules enforced in code
- Animal type must be active to register an animal or purchase under it.
- Animal code and animal type cannot change after registration. Prefix cannot change once animals exist.
- Status transitions: active → deceased/archived; archived → active; sold and deceased are final.
- Only active animals can be sold. A sale marks them sold in one transaction. A sold animal cannot be sold again (row lock and status check).
- Payments cannot exceed the outstanding balance; the sale row is locked during payment.
- Feeding cannot be recorded for sold or deceased animals.
- Animals with any record cannot be deleted; admins can delete empty ones.
- Health and feeding records: staff edit their own; admins edit any.
- Expenses: staff edit their own; only admins delete.
- Users: an admin cannot deactivate or demote themselves; the last active admin cannot be removed; deactivated users cannot sign in; deactivation does not alter records.
- Dashboard and reports show sales value, received, outstanding, purchases, and expenses separately. They do NOT calculate profit.

## Known gaps and risks
- Nothing has been run. Expect small errors on first run.
- Expense category management screens are not built (categories come from the seeder).
- The sales animal dropdown is capped at 500 active animals.
- Payment method is optional even when an amount is paid.
- Dashboard "needing attention" does not check for a later healthy record.
- Reports render all matching rows on screen; very large exports may be slow.
- Frontend page props and routes are not covered by PHP tests; check them in the browser.
- Animal Show uses a hardcoded delete confirmation dialog (window.confirm).
