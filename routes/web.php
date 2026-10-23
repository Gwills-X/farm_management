<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AnimalTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeedingRecordController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Own profile (any signed-in user)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Animal types: everyone can view; only admins change them.
    Route::get('animal-types', [AnimalTypeController::class, 'index'])->name('animal-types.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('animal-types/create', [AnimalTypeController::class, 'create'])->name('animal-types.create');
        Route::post('animal-types', [AnimalTypeController::class, 'store'])->name('animal-types.store');
        Route::get('animal-types/{animalType}/edit', [AnimalTypeController::class, 'edit'])->name('animal-types.edit');
        Route::put('animal-types/{animalType}', [AnimalTypeController::class, 'update'])->name('animal-types.update');
    });

    // Animals: everyone can register and edit; only admins delete.
    Route::resource('animals', AnimalController::class)->except(['destroy']);
    Route::delete('animals/{animal}', [AnimalController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('animals.destroy');

    // Health records
    Route::get('health-records', [HealthRecordController::class, 'index'])->name('health-records.index');
    Route::get('animals/{animal}/health-records/create', [HealthRecordController::class, 'create'])->name('health-records.create');
    Route::post('animals/{animal}/health-records', [HealthRecordController::class, 'store'])->name('health-records.store');
    Route::get('health-records/{healthRecord}/edit', [HealthRecordController::class, 'edit'])->name('health-records.edit');
    Route::put('health-records/{healthRecord}', [HealthRecordController::class, 'update'])->name('health-records.update');

    // Feeding records
    Route::get('feeding-records', [FeedingRecordController::class, 'index'])->name('feeding-records.index');
    Route::get('animals/{animal}/feeding-records/create', [FeedingRecordController::class, 'create'])->name('feeding-records.create');
    Route::post('animals/{animal}/feeding-records', [FeedingRecordController::class, 'store'])->name('feeding-records.store');
    Route::get('feeding-records/{feedingRecord}/edit', [FeedingRecordController::class, 'edit'])->name('feeding-records.edit');
    Route::put('feeding-records/{feedingRecord}', [FeedingRecordController::class, 'update'])->name('feeding-records.update');

    // Purchases
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

    // Sales and payments
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('sales/{sale}/payments', [SaleController::class, 'recordPayment'])->name('sales.payments.store');

    // Expenses: staff can create and edit; only admins delete.
    Route::resource('expenses', ExpenseController::class)->except(['show', 'destroy']);
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('expenses.destroy');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // User management (admins only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::put('users/{user}/password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});
