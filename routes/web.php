<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Expenses
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');

    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::patch('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Budget
    Route::get('/budget', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budgets.store');
    Route::redirect('/budgets/set', '/budget');

    // Profile and settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tips', [PageController::class, 'expenseTips'])->name('expense-tips');
    Route::redirect('/expense-tips', '/tips');
});

require __DIR__.'/auth.php';
