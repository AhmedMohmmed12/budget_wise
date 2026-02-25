@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <!-- Budget Summary Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold mb-0">Budget Summary</h2>
                <p class="text-muted mb-0">{{ \Carbon\Carbon::now()->format('F Y') }}</p>
            </div>
            <a href="{{ route('budgets.index') }}" class="btn btn-primary">
                <i class="fas fa-cog me-2"></i>Set Monthly Budget
            </a>
        </div>

        <div class="row g-3">
            <!-- Total Budget Card -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-dollar-sign fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Total Budget</h6>
                                <h3 class="fw-bold mb-0">${{ number_format($budgetSummary['totalBudget'], 2) }}</h3>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Spent</small>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">${{ number_format($budgetSummary['totalSpent'], 2) }}</span>
                                <span class="badge {{ $budgetSummary['spentPercentage'] > 100 ? 'bg-danger' : 'bg-primary' }}">
                                    {{ number_format($budgetSummary['spentPercentage'], 1) }}%
                                </span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $budgetSummary['spentPercentage'] > 100 ? 'bg-danger' : 'bg-primary' }}" 
                                 style="width: {{ min($budgetSummary['spentPercentage'], 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remaining Card -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-check fa-lg text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Remaining</h6>
                                <h3 class="fw-bold mb-0 {{ $budgetSummary['remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format(abs($budgetSummary['remaining']), 2) }}
                                    @if($budgetSummary['remaining'] < 0)<small class="text-danger">(Over)</small>@endif
                                </h3>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Available</small>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ number_format(max(0, 100 - $budgetSummary['spentPercentage']), 1) }}% left</span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ max(0, 100 - $budgetSummary['spentPercentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Budget Card -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-calendar-day fa-lg text-secondary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Daily Budget</h6>
                                <h3 class="fw-bold mb-0">${{ number_format($budgetSummary['dailyBudget'], 2) }}</h3>
                            </div>
                        </div>
                        <p class="small text-muted mb-2">
                            Based on your remaining budget and {{ $budgetSummary['daysRemaining'] }} days left this month.
                        </p>
                        <div class="small">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            Today's spending: <span class="fw-bold">${{ number_format($budgetSummary['todaySpending'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Alerts -->
    @php
        $overBudgetCategories = collect($budgetSummary['categorySummaries'])->filter(fn($cat) => $cat['percentage'] > 100);
        $mostExceeded = $overBudgetCategories->sortByDesc('percentage')->first();
    @endphp

    @if($budgetSummary['spentPercentage'] > 100)
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">⚠️ Total Budget Exceeded!</h5>
                <p class="mb-2">You've exceeded your total monthly budget by {{ number_format($budgetSummary['spentPercentage'] - 100, 1) }}%. Consider reducing expenses for the rest of the month to avoid financial strain.</p>
                <a href="{{ route('expense-tips') }}" class="btn btn-sm btn-outline-danger">View Money-Saving Tips</a>
            </div>
        </div>
    @elseif($mostExceeded)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">{{ $mostExceeded['category'] }} Budget Alert</h5>
                <p class="mb-2">You've spent {{ number_format($mostExceeded['percentage'], 1) }}% of your {{ $mostExceeded['category'] }} budget ({{ number_format($mostExceeded['percentage'] - 100, 1) }}% over budget). Check your spending in this category.</p>
                <a href="{{ route('expense-tips') }}" class="btn btn-sm btn-outline-warning">View Saving Tips</a>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Spending by Category -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Spending by Category</h5>
                </div>
                <div class="card-body">
                    @if(count($chartData) > 0)
                        <div style="max-width: 400px; margin: 0 auto;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        
                        <div class="mt-4">
                            @foreach($budgetSummary['categorySummaries'] as $summary)
                                @if($summary['spent'] > 0 || $summary['budget'] > 0)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="badge badge-category" style="background-color: {{ ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6b7280'][array_search($summary['category'], array_column($budgetSummary['categorySummaries'], 'category'))] }}">
                                                {{ $summary['category'] }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">${{ number_format($summary['spent'], 2) }} / ${{ number_format($summary['budget'], 2) }}</small>
                                        </div>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar {{ $summary['percentage'] > 100 ? 'bg-danger' : 'bg-primary' }}" 
                                             style="width: {{ min($summary['percentage'], 100) }}%">
                                            {{ number_format($summary['percentage'], 0) }}%
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i>
                            <p>No expenses recorded yet. Add your first expense to see the chart!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Expense Form -->
        <div class="col-lg-4">
            @include('components.expense-form')
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                @if($recentExpenses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Note</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $expense->category }}</span>
                                    </td>
                                    <td>{{ $expense->note ?? '-' }}</td>
                                    <td class="text-end fw-bold">${{ number_format($expense->amount, 2) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger delete-expense" data-id="{{ $expense->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                        <p>No expenses recorded yet</p>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Lazy load Chart.js only when needed
@if(count($chartData) > 0)
// Load Chart.js dynamically
function loadChartJS(callback) {
    if (typeof Chart !== 'undefined') {
        callback();
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
    script.onload = callback;
    document.head.appendChild(script);
}

// Initialize chart when DOM is ready and Chart.js is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadChartJS(function() {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        const chartData = @json($chartData);
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6b7280'];

        new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: chartData.map(item => item.category),
        datasets: [{
            data: chartData.map(item => item.amount),
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': $' + context.parsed.toFixed(2);
                    }
                }
            }
        }
    }
});
    });
});
@endif

// Delete expense - Wait for jQuery to load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is loaded
    function waitForJQuery(callback) {
        if (typeof $ !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForJQuery(callback), 100);
        }
    }

    waitForJQuery(function() {
        $(document).on('click', '.delete-expense', function() {
    if (!confirm('Are you sure you want to delete this expense?')) return;
    
    const expenseId = $(this).data('id');
    const row = $(this).closest('tr');
    
    $.ajax({
        url: `/expenses/${expenseId}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                row.fadeOut(300, function() {
                    $(this).remove();
                });
                location.reload(); // Reload to update budget summary
            }
        },
        error: function() {
            alert('Failed to delete expense. Please try again.');
        }
    });
        });
    });
});
</script>
@endpush
