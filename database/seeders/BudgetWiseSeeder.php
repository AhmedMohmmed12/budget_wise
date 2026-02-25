<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BudgetWiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user
        $user = User::create([
            'name' => 'Demo User',
            'username' => 'demo',
            'email' => 'demo@budgetwise.com',
            'password' => Hash::make('password'),
        ]);

        // Create budgets for the demo user
        $budgets = [
            ['category' => 'Total', 'amount' => 2500, 'period' => 'monthly'],
            ['category' => 'Restaurants', 'amount' => 300, 'period' => 'monthly'],
            ['category' => 'Housing', 'amount' => 700, 'period' => 'monthly'],
            ['category' => 'Transportation', 'amount' => 250, 'period' => 'monthly'],
            ['category' => 'Shopping', 'amount' => 200, 'period' => 'monthly'],
            ['category' => 'Entertainment', 'amount' => 150, 'period' => 'monthly'],
            ['category' => 'Others', 'amount' => 200, 'period' => 'monthly'],
        ];

        foreach ($budgets as $budget) {
            Budget::create([
                'user_id' => $user->id,
                'category' => $budget['category'],
                'amount' => $budget['amount'],
                'period' => $budget['period'],
                'start_date' => Carbon::now()->startOfMonth(),
                'active' => true,
            ]);
        }

        // Create sample expenses for the demo user
        $expenses = [
            ['amount' => 15.75, 'category' => 'Restaurants', 'date' => Carbon::today(), 'note' => 'Lunch at Chipotle'],
            ['amount' => 22.50, 'category' => 'Transportation', 'date' => Carbon::yesterday(), 'note' => 'Uber ride'],
            ['amount' => 28.00, 'category' => 'Entertainment', 'date' => Carbon::today()->subDays(2), 'note' => 'Movie tickets'],
            ['amount' => 45.99, 'category' => 'Shopping', 'date' => Carbon::today()->subDays(3), 'note' => 'H&M purchase'],
            ['amount' => 65.30, 'category' => 'Restaurants', 'date' => Carbon::today()->subDays(5), 'note' => 'Dinner with friends'],
            ['amount' => 355.09, 'category' => 'Housing', 'date' => Carbon::today()->subDays(7), 'note' => 'Partial rent payment'],
            ['amount' => 213.05, 'category' => 'Transportation', 'date' => Carbon::today()->subDays(10), 'note' => 'Car maintenance'],
            ['amount' => 142.04, 'category' => 'Shopping', 'date' => Carbon::today()->subDays(12), 'note' => 'Online shopping'],
            ['amount' => 142.04, 'category' => 'Entertainment', 'date' => Carbon::today()->subDays(15), 'note' => 'Concert tickets'],
            ['amount' => 142.03, 'category' => 'Others', 'date' => Carbon::today()->subDays(18), 'note' => 'Miscellaneous'],
        ];

        foreach ($expenses as $expense) {
            Expense::create([
                'user_id' => $user->id,
                'amount' => $expense['amount'],
                'category' => $expense['category'],
                'date' => $expense['date'],
                'note' => $expense['note'],
            ]);
        }

        $this->command->info('Demo user created successfully!');
        $this->command->info('Email/Username: demo@budgetwise.com or demo');
        $this->command->info('Password: password');
    }
}

