<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalContributionController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Entries (history, export, trash)
    Route::get('/entries', [EntryController::class, 'index'])->name('entries.index');
    Route::get('/entries/export', [EntryController::class, 'export'])->name('entries.export');
    Route::get('/entries/trash', [TrashController::class, 'index'])->name('trash.index');

    // Expenses
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');

    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::patch('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::post('/entries/trash/expenses/{expense}/restore', [TrashController::class, 'restoreExpense'])
            ->withTrashed()->name('trash.expenses.restore');
        Route::delete('/entries/trash/expenses/{expense}', [TrashController::class, 'destroyExpense'])
            ->withTrashed()->name('trash.expenses.destroy');
    });

    // Income
    Route::get('/income/{income}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/income', [IncomeController::class, 'store'])->name('incomes.store');
        Route::patch('/income/{income}', [IncomeController::class, 'update'])->name('incomes.update');
        Route::delete('/income/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');
        Route::post('/entries/trash/income/{income}/restore', [TrashController::class, 'restoreIncome'])
            ->withTrashed()->name('trash.incomes.restore');
        Route::delete('/entries/trash/income/{income}', [TrashController::class, 'destroyIncome'])
            ->withTrashed()->name('trash.incomes.destroy');
    });

    // Recurring
    Route::get('/recurring', [RecurringEntryController::class, 'index'])->name('recurring.index');
    Route::get('/recurring/{recurring}/edit', [RecurringEntryController::class, 'edit'])->name('recurring.edit');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/recurring', [RecurringEntryController::class, 'store'])->name('recurring.store');
        Route::patch('/recurring/{recurring}', [RecurringEntryController::class, 'update'])->name('recurring.update');
        Route::post('/recurring/{recurring}/pause', [RecurringEntryController::class, 'pause'])->name('recurring.pause');
        Route::delete('/recurring/{recurring}', [RecurringEntryController::class, 'destroy'])->name('recurring.destroy');
    });

    // Goals
    Route::get('/goals', [SavingsGoalController::class, 'index'])->name('goals.index');
    Route::get('/goals/{goal}/edit', [SavingsGoalController::class, 'edit'])->name('goals.edit');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/goals', [SavingsGoalController::class, 'store'])->name('goals.store');
        Route::patch('/goals/{goal}', [SavingsGoalController::class, 'update'])->name('goals.update');
        Route::post('/goals/{goal}/complete', [SavingsGoalController::class, 'complete'])->name('goals.complete');
        Route::delete('/goals/{goal}', [SavingsGoalController::class, 'destroy'])->name('goals.destroy');
        Route::post('/goals/{goal}/contributions', [GoalContributionController::class, 'store'])->name('goals.contributions.store');
        Route::delete('/goal-contributions/{contribution}', [GoalContributionController::class, 'destroy'])->name('goals.contributions.destroy');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/{category}/archive', [CategoryController::class, 'archive'])->name('categories.archive');
        Route::post('/categories/{category}/move', [CategoryController::class, 'move'])->name('categories.move');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
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
