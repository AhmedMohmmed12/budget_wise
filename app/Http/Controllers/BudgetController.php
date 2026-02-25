<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all active budgets for the user
        $budgets = Budget::where('user_id', $user->id)
            ->active()
            ->get()
            ->keyBy('category');
        
        // Define all categories including Total
        $categories = ['Total', 'Restaurants', 'Housing', 'Transportation', 'Shopping', 'Entertainment', 'Others'];
        
        // Create budget data array
        $budgetData = [];
        $period = 'monthly';
        
        foreach ($categories as $category) {
            if (isset($budgets[$category])) {
                $budgetData[$category] = [
                    'amount' => $budgets[$category]->amount,
                    'period' => $budgets[$category]->period,
                ];
                if ($category === 'Total') {
                    $period = $budgets[$category]->period;
                }
            } else {
                $budgetData[$category] = [
                    'amount' => 0,
                    'period' => $period,
                ];
            }
        }
        
        return view('budgets.set', compact('budgetData', 'period'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_budget' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,weekly',
            'restaurants' => 'nullable|numeric|min:0',
            'housing' => 'nullable|numeric|min:0',
            'transportation' => 'nullable|numeric|min:0',
            'shopping' => 'nullable|numeric|min:0',
            'entertainment' => 'nullable|numeric|min:0',
            'others' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = $request->user();
        $period = $request->period;
        $startDate = Carbon::now()->startOfMonth();

        // Update or create Total budget
        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category' => 'Total'
            ],
            [
                'amount' => $request->total_budget,
                'period' => $period,
                'start_date' => $startDate,
                'active' => true,
            ]
        );

        // Update or create category budgets
        $categories = [
            'Restaurants' => $request->restaurants,
            'Housing' => $request->housing,
            'Transportation' => $request->transportation,
            'Shopping' => $request->shopping,
            'Entertainment' => $request->entertainment,
            'Others' => $request->others,
        ];

        foreach ($categories as $category => $amount) {
            if ($amount !== null && $amount > 0) {
                Budget::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'category' => $category
                    ],
                    [
                        'amount' => $amount,
                        'period' => $period,
                        'start_date' => $startDate,
                        'active' => true,
                    ]
                );
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Budget settings saved successfully!');
    }
}
