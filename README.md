# BudgetWise - Laravel Budget Tracking Application

A comprehensive budget tracking application built with Laravel 11, Blade templates, Bootstrap 5, and Font Awesome icons.

## Features

### 🎯 Core Functionality

- **User Authentication** - Secure login/registration with username or email support
- **Budget Management** - Set monthly or weekly budgets for different spending categories
- **Expense Tracking** - Add and track expenses across multiple categories
- **Visual Analytics** - Interactive donut charts showing spending by category
- **Budget Alerts** - Real-time notifications when budgets are exceeded
- **Expense Tips** - Helpful money-saving strategies and tips
- **Profile Management** - Update personal information and change password

### 📊 Dashboard Features

- **Budget Summary Cards** - Total budget, remaining balance, and daily budget
- **Category Breakdown** - Detailed spending analysis by category
- **Recent Transactions** - View and delete recent expenses
- **Progress Indicators** - Visual progress bars for each category
- **Smart Alerts** - Warnings when approaching or exceeding budget limits

### 💰 Budget Categories

- Restaurants
- Housing
- Transportation
- Shopping
- Entertainment
- Others

## Technology Stack

### Backend

- **Framework**: Laravel 11
- **Database**: MySQL
- **Authentication**: Laravel Breeze
- **ORM**: Eloquent

### Frontend

- **Templates**: Blade
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6
- **Charts**: Chart.js 4
- **JavaScript**: jQuery for AJAX

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL
- Node.js & npm (optional, for asset compilation)

### Setup Steps

1. **Clone or navigate to the project directory**

    ```bash
    cd budget_wise
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Create and configure environment file**

    ```bash
    cp .env.example .env  # If .env doesn't exist
    php artisan key:generate
    ```

4. **Configure database in .env**

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=budget_wise
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Create database**

    ```bash
    # Using MySQL CLI or create via phpMyAdmin
    mysql -u root -e "CREATE DATABASE budget_wise;"
    ```

6. **Run migrations**

    ```bash
    php artisan migrate
    ```

7. **Seed demo data (optional)**

    ```bash
    php artisan db:seed --class=BudgetWiseSeeder
    ```

    Demo credentials:
    - Username/Email: `demo` or `demo@budgetwise.com`
    - Password: `password`

8. **Start development server**

    ```bash
    php artisan serve
    ```

9. **Access the application**
   Open your browser and visit: `http://localhost:8000`

## Usage Guide

### Getting Started

1. **Register a new account** or use demo credentials
    - Visit `/register` to create a new account
    - Or login with demo user: `demo` / `password`

2. **Set your budget**
    - Click "Set Monthly Budget" on the dashboard
    - Enter your total budget and period (monthly/weekly)
    - Optionally set category-specific budgets
    - Click "Save Budget Settings"

3. **Add expenses**
    - Use the "Add Expense" form on the dashboard
    - Enter amount, select category, choose date, and add optional note
    - Click "Add Expense" to save

4. **Track your spending**
    - View budget summary cards showing total, remaining, and daily budget
    - Check the donut chart for category breakdown
    - Monitor recent transactions table
    - Get alerts when approaching or exceeding budgets

5. **Manage your profile**
    - Click on your name in the navigation
    - Update profile information or change password
    - Logout when done

## Features Walkthrough

### Dashboard

- **Budget Summary**: Three cards showing total budget, remaining balance, and daily spending limit
- **Spending Chart**: Interactive donut chart visualizing expenses by category
- **Category Progress**: Progress bars for each spending category
- **Add Expense Form**: Quick expense entry with real-time validation
- **Recent Transactions**: List of last 10 expenses with delete functionality
- **Smart Alerts**: Automatic warnings when budgets are exceeded

### Set Budget

- Configure total budget amount
- Choose between monthly or weekly periods
- Set individual category budgets
- View helpful budgeting tips
- Pre-filled with existing budget data

### Expense Tips

- Six categories of money-saving strategies:
    - Daily Habits
    - Shopping Strategies
    - Smart Saving
    - Budget Management
    - Entertainment & Leisure
    - Long-Term Strategies
- Practical, actionable tips for reducing expenses
- Pro tips for effective saving

### Profile Settings

- **Profile Tab**: Update name, username, and email
- **Security Tab**: Change password with current password verification
- Validation for unique usernames and emails
- Secure password requirements

## Database Schema

### Users Table

- id, name, username (unique), email (unique), password, timestamps

### Expenses Table

- id, user_id (FK), amount (decimal), category, date, note, timestamps

### Budgets Table

- id, user_id (FK), category, amount (decimal), period (enum), start_date, active (boolean), timestamps

## API Endpoints

### Protected Routes (Requires Authentication)

- `GET /dashboard` - Dashboard view
- `POST /expenses` - Create expense (AJAX)
- `DELETE /expenses/{id}` - Delete expense (AJAX)
- `GET /budgets/set` - Budget settings page
- `POST /budgets` - Save budget settings
- `GET /profile` - Profile page
- `PATCH /profile` - Update profile
- `PATCH /profile/password` - Update password
- `GET /expense-tips` - Money-saving tips page

### Authentication Routes

- `GET /register` - Registration form
- `POST /register` - Create account
- `GET /login` - Login form
- `POST /login` - Authenticate user
- `POST /logout` - Logout user

## Responsive Design

The application is fully responsive and mobile-friendly:

- **Desktop**: Full sidebar navigation and multi-column layouts
- **Tablet**: Responsive grid adjusts to 2 columns
- **Mobile**: Single column layout with fixed bottom navigation bar
- All charts and tables are mobile-optimized
- Touch-friendly buttons and forms

## Service Architecture

### BudgetSummaryService

Located in `app/Services/BudgetSummaryService.php`

Provides comprehensive budget analytics:

- Total budget calculation
- Total spent in current period
- Remaining balance
- Daily budget calculation
- Category-wise spending summaries
- Percentage calculations
- Today's spending tracking

### Custom Styling

All custom CSS is included in the main layout file (`resources/views/layouts/app.blade.php`)

### JavaScript Libraries (CDN)

- Bootstrap 5.3.0
- Font Awesome 6.4.0
- Chart.js 4.4.0
- jQuery 3.7.0

## Security Features

- Password hashing using bcrypt
- CSRF protection on all forms
- Authentication middleware on protected routes
- Unique constraint on usernames and emails
- Current password verification for password changes
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade templating

### Demo Account

Use the seeded demo account to test all features:

- Username: `demo`
- Email: `demo@budgetwise.com`
- Password: `password`

## Troubleshooting

### Common Issues

**Database connection error**

- Check MySQL is running
- Verify database credentials in `.env`
- Ensure database exists

**Migration errors**

- Run `php artisan migrate:fresh` to reset database
- Check database user permissions

**Missing vendor directory**

- Run `composer install`

**App key not set**

- Run `php artisan key:generate`

**Chart not displaying**

- Ensure Chart.js CDN is accessible
- Check browser console for JavaScript errors
