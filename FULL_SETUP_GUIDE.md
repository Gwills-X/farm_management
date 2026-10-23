# Farm Management System – Complete Setup Guide (Parts 1 to 7)

This package is the complete application: login, animal types and animals, health and feeding, purchases, sales with payments, expenses, dashboard, reports, user management with roles, and automated tests.

Important: this code has been written and checked by reading, but it has not yet been run. Follow the steps in order and send any error text you hit.

## 1. Databases (phpMyAdmin, XAMPP running)

Create two empty databases, both with collation utf8mb4_unicode_ci:
- `farm_management` (the real app)
- `farm_management_test` (used only by automated tests)

## 2. Create the Laravel project (if not done)

    composer create-project laravel/laravel farm-management
    cd farm-management

## 3. Copy this package into the project

Copy the contents of this folder into the project root, keeping folder paths. Replace files with the same name. Do NOT copy vendor/, node_modules/, .env, or config/.

Delete Laravel's unused defaults if they exist: resources/js/app.js and resources/js/bootstrap.js.

## 4. Install packages

    composer require inertiajs/inertia-laravel
    npm install @inertiajs/react react react-dom
    npm install -D @vitejs/plugin-react @tailwindcss/vite tailwindcss laravel-vite-plugin

## 5. Configure .env

    APP_NAME="Farm Management System"
    APP_TIMEZONE=Africa/Lagos
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=farm_management
    DB_USERNAME=root
    DB_PASSWORD=
    ADMIN_EMAIL=admin@farm.test
    ADMIN_PASSWORD=ChangeMe123!

Then:

    php artisan config:clear

## 6. Migrate and seed the real database

    php artisan migrate
    php artisan db:seed

Expected: Laravel default migrations plus 10 farm migrations run. The seeders create five animal types, seven expense categories, and the admin account.

Only if you want to wipe the real database: `php artisan migrate:fresh --seed`. WARNING: this deletes all data in `farm_management`.

## 7. Run the automated tests (test database only)

    php artisan test

The tests use `farm_management_test` only. If they try to use another database, they stop with an error before touching any data.

If a test fails, read the message, fix the cause, and run the single test again:

    php artisan test --filter=SaleTest

## 8. Build the frontend

    npm run build

## 9. Run the app

Two terminals in the project folder:

    php artisan serve
    npm run dev

Open http://127.0.0.1:8000 and sign in with ADMIN_EMAIL and ADMIN_PASSWORD.

## 10. Manual check (about 15 minutes)

1. Animal Types: create "Rabbits" (prefix RAB). It appears in Add Animal.
2. Animals: register a goat. The code is generated (for example GOA-00001).
3. Animal page: record health and feeding.
4. Purchases: buy two animals. Both are registered and linked.
5. Sales: sell one animal with a part payment, then pay the balance. A sold animal cannot be sold again.
6. Expenses: record one. It appears in the list and totals.
7. Dashboard: counts and money figures update when you change the date range.
8. Reports: choose a report, apply filters, Print, Export CSV.
9. Users (admin only): add a staff account, sign out, and sign in as that user. Confirm Users is hidden for staff and /users returns 403.
10. Deactivate the staff account from the Users page; confirm sign-in is refused.

## 11. Troubleshooting

- Blank page or Vite errors: keep `npm run dev` running; run `npm install` again if a package is missing.
- "Target class does not exist": `composer dump-autoload` then `php artisan optimize:clear`.
- Migration fails because tables exist: the database is not empty. Use an empty database, or `migrate:fresh --seed` on the development database only.
- Tests refuse to run: check that DB_DATABASE in phpunit.xml is `farm_management_test`.
- Login fails: confirm ADMIN_EMAIL and ADMIN_PASSWORD were set before `db:seed` ran.
- Times look wrong: confirm APP_TIMEZONE, then `php artisan config:clear`.
- Admin pages show 403 for the admin account: confirm the user row has role = admin and is_active = 1 in phpMyAdmin.
