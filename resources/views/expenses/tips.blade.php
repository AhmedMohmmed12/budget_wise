@extends('layouts.app')

@section('title', 'Money Saving Tips')

@section('content')
<div class="container">
    <h1 class="fw-bold mb-3">Tips for Reducing Expenses</h1>
    <p class="text-muted mb-5 lead">
        Discover practical strategies and tips to help you save money and reduce your expenses. 
        Implementing even a few of these suggestions can make a significant difference in your financial situation over time.
    </p>

    <div class="row g-4">
        <!-- Daily Habits -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                            <i class="fas fa-lightbulb fa-lg text-warning"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Daily Habits</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Make coffee at home instead of buying at cafés</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Bring lunch to work instead of eating out</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use reusable water bottles instead of buying bottled water</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Plan meals for the week to reduce food waste</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use public transportation or carpool when possible</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Shopping Strategies -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                            <i class="fas fa-arrow-trend-down fa-lg text-primary"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Shopping Strategies</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use shopping lists to avoid impulse purchases</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Compare prices across different stores</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Look for sales, discounts, and coupons</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Buy non-perishable items in bulk when on sale</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Consider second-hand options for clothing and furniture</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Smart Saving -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                            <i class="fas fa-piggy-bank fa-lg text-success"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Smart Saving</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Set up automatic transfers to savings on payday</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Challenge yourself to no-spend days or weekends</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use the 24-hour rule for non-essential purchases</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Cancel unused subscriptions and memberships</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Negotiate bills like internet, phone, and insurance</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Budget Management -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                            <i class="fas fa-chart-line fa-lg text-info"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Budget Management</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Track all expenses to identify spending patterns</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Allocate specific amounts for different spending categories</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Review your budget regularly and adjust as needed</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use the 50/30/20 rule: 50% for needs, 30% for wants, 20% for savings</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Set realistic financial goals and track your progress</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Entertainment & Leisure -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3">
                            <i class="fas fa-ticket fa-lg text-danger"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Entertainment & Leisure</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Look for free community events and activities</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Use the library for books, movies, and music</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Host potluck dinners instead of eating out</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Take advantage of free trials, student discounts, and happy hours</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Plan vacations during off-peak seasons for better rates</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Long-Term Strategies -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-2 me-3">
                            <i class="fas fa-calendar-days fa-lg text-secondary"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Long-Term Strategies</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Pay off high-interest debt first</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Invest in energy-efficient appliances to reduce utility bills</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Consider refinancing loans for better interest rates</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Build an emergency fund to avoid using credit in emergencies</span>
                        </li>
                        <li class="mb-0 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Learn basic repair skills to fix things yourself</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Savings Plan -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Create Your Personal Savings Plan</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Choose 2-3 tips from each category to implement in your daily life. Start with small changes and gradually expand your money-saving habits. 
                Track your progress in the expense tracker to see the impact of your new habits on your spending.
            </p>
            <div class="alert alert-info mb-0">
                <div class="d-flex">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-2">Pro Tip:</h6>
                        <p class="mb-0">The most successful savers don't just cut expenses - they also replace costly habits with enjoyable alternatives. 
                        Think about what truly brings you joy and focus your spending there while reducing in other areas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

