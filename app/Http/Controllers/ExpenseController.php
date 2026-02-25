<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|in:Restaurants,Housing,Transportation,Shopping,Entertainment,Others',
            'date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $expense = Expense::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully!',
            'expense' => $expense
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found'
            ], 404);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully!'
        ]);
    }
}
