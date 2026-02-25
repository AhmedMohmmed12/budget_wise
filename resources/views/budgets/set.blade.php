@extends('layouts.app')

@section('title', 'Set Budget')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h2 class="fw-bold">Set Your Budget</h2>
                <p class="text-muted">Configure your monthly or weekly budget limits</p>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Budget Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('budgets.store') }}">
                        @csrf

                        <!-- Total Budget and Period -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="total_budget" class="form-label fw-bold">Total Budget <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('total_budget') is-invalid @enderror" 
                                           id="total_budget" 
                                           name="total_budget" 
                                           step="0.01" 
                                           min="0"
                                           value="{{ old('total_budget', $budgetData['Total']['amount'] ?? 0) }}" 
                                           required>
                                </div>
                                @error('total_budget')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Your total budget for the selected period</small>
                            </div>

                            <div class="col-md-6">
                                <label for="period" class="form-label fw-bold">Budget Period <span class="text-danger">*</span></label>
                                <select class="form-select @error('period') is-invalid @enderror" id="period" name="period" required>
                                    <option value="monthly" {{ old('period', $period) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="weekly" {{ old('period', $period) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                </select>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">How often you want to reset your budget</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Category Budgets -->
                        <h5 class="mb-3">Category Budgets</h5>
                        <p class="text-muted small mb-3">Set specific budgets for each spending category (optional)</p>

                        <div class="row g-3">
                            @php
                                $categories = [
                                    'restaurants' => ['icon' => 'utensils', 'label' => 'Restaurants'],
                                    'housing' => ['icon' => 'home', 'label' => 'Housing'],
                                    'transportation' => ['icon' => 'car', 'label' => 'Transportation'],
                                    'shopping' => ['icon' => 'shopping-bag', 'label' => 'Shopping'],
                                    'entertainment' => ['icon' => 'film', 'label' => 'Entertainment'],
                                    'others' => ['icon' => 'ellipsis-h', 'label' => 'Others']
                                ];
                            @endphp

                            @foreach($categories as $key => $cat)
                            <div class="col-md-6">
                                <label for="{{ $key }}" class="form-label">
                                    <i class="fas fa-{{ $cat['icon'] }} me-2 text-muted"></i>{{ $cat['label'] }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error($key) is-invalid @enderror" 
                                           id="{{ $key }}" 
                                           name="{{ $key }}" 
                                           step="0.01" 
                                           min="0"
                                           value="{{ old($key, $budgetData[ucfirst($cat['label'])]['amount'] ?? 0) }}" 
                                           placeholder="0.00">
                                </div>
                                @error($key)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Budget Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Helpful Tips -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Budget Tips</h6>
                    <ul class="mb-0 small">
                        <li class="mb-2">The 50/30/20 rule suggests: 50% for needs, 30% for wants, and 20% for savings</li>
                        <li class="mb-2">Start with a realistic budget based on your past spending habits</li>
                        <li class="mb-2">Review and adjust your budget monthly based on your actual expenses</li>
                        <li>Leave some buffer room for unexpected expenses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

