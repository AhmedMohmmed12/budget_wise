# BudgetWise

A small notebook for where your money goes. Write down what you spend, set a limit for the month (or week), and see what's left.

Live at [budget.habani.site](https://budget.habani.site).

## What it does

- **This month / this week.** How much is left, a daily allowance, spending by category against its limit, and what's coming up. Past periods can be browsed read-only.
- **Entries.** Every expense and income entry, filterable by month, category, text and amount, with CSV export. Deleted entries wait 30 days in "Recently deleted".
- **Closed months.** Entries can be dated in the current month only (never the future). During the first 3 days of a new month last month can still be fixed; after that it's closed for good. The user's timezone decides when a day or month rolls over.
- **Budgets.** A total plus optional per-category limits, monthly or weekly (week starting Saturday, Sunday or Monday). Each period keeps its own budget; a new period starts with the previous one's numbers.
- **Categories.** Add, rename, recolour, reorder and archive your own.
- **Income.** Salary and anything else coming in, so each month shows in, out, set aside and kept.
- **Recurring.** Rent, subscriptions and salary are written down automatically on their day each month.
- **Goals.** Save toward something, see progress, the pace you're on and what's needed per month to hit a date.
- **Reports.** A 12-month ledger, this month vs last month vs your 3-month average per category, and the last 12 weeks for weekly budgets.
- **Email alerts.** One email when the total or a category reaches 80% and 100% of its budget (can be turned off).
- **Currency** per account (USD, EUR, GBP, EGP, SAR, AED and more).

## Stack

Laravel 12 (PHP 8.3), MySQL, Blade, Tailwind CSS 3 and Alpine.js, built with Vite. Tests use Pest on SQLite.

## Running it locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

The seeder creates a demo account with three months of history: username `demo`, password `password`.

Run the tests with `php artisan test`.

## Deploying

The Dockerfile builds the assets and serves the app with Apache. After deploying, run `php artisan migrate --force`. Back up the database first: the migrations reshape `expenses` and `budgets` and carry existing data over.

Set these in the production `.env`:

- `APP_NAME=BudgetWise` (it appears in emails)
- `MAIL_*` with a real SMTP provider, so budget alerts can be sent

Nothing else needs to run in the background. Recurring entries are written down when the user next opens the app. If you can add a cron entry, this makes them post on the day even when nobody visits, and prunes old deleted entries:

```
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```
