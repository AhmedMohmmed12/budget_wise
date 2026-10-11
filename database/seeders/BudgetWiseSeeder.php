<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\RecurringPoster;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * A demo account with three months of believable history.
 * Log in as "demo" / "password".
 */
class BudgetWiseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'demo')->exists()) {
            $this->command?->warn('The demo user already exists; skipping.');

            return;
        }

        $user = User::create([
            'name' => 'Demo User',
            'username' => 'demo',
            'email' => 'demo@budgetwise.com',
            'password' => Hash::make('password'),
            'email_alerts' => false,
        ]);

        // DatabaseSeeder runs without model events, so the created() hook that
        // normally adds these doesn't fire.
        Category::createDefaultsFor($user);
        $category = $user->categories()->pluck('id', 'name');
        $thisMonth = CarbonImmutable::today()->startOfMonth();

        // Budgets for the last three months (the current one rolls forward from these).
        $limits = ['Restaurants' => 300, 'Housing' => 900, 'Transportation' => 250, 'Shopping' => 200, 'Entertainment' => 150, 'Others' => 150];
        foreach ([2, 1, 0] as $monthsAgo) {
            $start = $thisMonth->subMonthsNoOverflow($monthsAgo)->toDateString();
            Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => $start, 'amount' => 2200]);
            foreach ($limits as $name => $amount) {
                Budget::create(['user_id' => $user->id, 'category_id' => $category[$name], 'period' => 'monthly', 'period_start' => $start, 'amount' => $amount]);
            }
        }

        // Recurring: rent, a subscription and the salary.
        $starts = $thisMonth->subMonthsNoOverflow(2)->toDateString();
        $user->recurringEntries()->createMany([
            ['kind' => 'expense', 'category_id' => $category['Housing'], 'amount' => 850, 'description' => 'Rent', 'day_of_month' => 1, 'starts_on' => $starts],
            ['kind' => 'expense', 'category_id' => $category['Entertainment'], 'amount' => 12.99, 'description' => 'Music subscription', 'day_of_month' => 5, 'starts_on' => $starts],
            ['kind' => 'income', 'amount' => 3200, 'description' => 'Salary', 'day_of_month' => 1, 'starts_on' => $starts],
        ]);

        // Earlier months, written down the way the recurring entries would have been.
        foreach ([2, 1] as $monthsAgo) {
            $month = $thisMonth->subMonthsNoOverflow($monthsAgo);
            foreach ($user->recurringEntries as $entry) {
                $date = $entry->dueDateIn($month);
                $entry->isExpense()
                    ? $user->expenses()->create(['recurring_entry_id' => $entry->id, 'category_id' => $entry->category_id, 'amount' => $entry->amount, 'date' => $date, 'note' => $entry->description])
                    : $user->incomes()->create(['recurring_entry_id' => $entry->id, 'amount' => $entry->amount, 'source' => $entry->description, 'date' => $date]);
            }
            $user->recurringEntries()->update(['last_posted_month' => $month->toDateString()]);
        }

        // Everyday spending: [day of month, category, amount, note].
        $everyday = [
            [2, 'Restaurants', 14.50, 'Lunch near work'], [3, 'Transportation', 40.00, 'Fuel'],
            [4, 'Shopping', 36.20, 'Groceries'], [6, 'Restaurants', 4.75, 'Coffee'],
            [8, 'Entertainment', 24.00, 'Cinema'], [9, 'Shopping', 58.90, 'Groceries'],
            [11, 'Restaurants', 62.30, 'Dinner with friends'], [12, 'Transportation', 18.00, 'Taxi home'],
            [14, 'Others', 25.00, 'Haircut'], [16, 'Shopping', 41.10, 'Groceries'],
            [18, 'Restaurants', 9.80, 'Shawarma'], [20, 'Transportation', 40.00, 'Fuel'],
            [22, 'Shopping', 79.99, 'New shoes'], [23, 'Others', 15.00, 'Pharmacy'],
            [25, 'Restaurants', 31.40, 'Takeaway'], [27, 'Shopping', 47.60, 'Groceries'],
        ];

        $today = CarbonImmutable::today();
        foreach ([2, 1, 0] as $monthsAgo) {
            $month = $thisMonth->subMonthsNoOverflow($monthsAgo);
            foreach ($everyday as $i => [$day, $name, $amount, $note]) {
                $date = $month->day(min($day, $month->daysInMonth));
                if ($date->gt($today)) {
                    continue;
                }
                // A little variation so the months don't look identical.
                $user->expenses()->create([
                    'category_id' => $category[$name],
                    'amount' => round($amount * (1 + (($i + $monthsAgo * 3) % 5 - 2) / 10), 2),
                    'date' => $date,
                    'note' => $note,
                ]);
            }
        }

        // This month's recurring entries, exactly as a visit would write them.
        RecurringPoster::catchUp($user);

        // A goal with a few months of saving.
        $goal = $user->savingsGoals()->create([
            'name' => 'New laptop',
            'target_amount' => 1200,
            'target_date' => $thisMonth->addMonthsNoOverflow(6)->endOfMonth()->toDateString(),
        ]);
        foreach ([2, 1, 0] as $monthsAgo) {
            $date = $thisMonth->subMonthsNoOverflow($monthsAgo)->day(2);
            if ($date->lte($today)) {
                $goal->contributions()->create(['user_id' => $user->id, 'amount' => 150, 'date' => $date, 'note' => 'Monthly saving']);
            }
        }

        $this->command?->info('Demo account ready: username "demo", password "password".');
    }
}
