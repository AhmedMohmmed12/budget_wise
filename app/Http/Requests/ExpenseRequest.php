<?php

namespace App\Http\Requests;

use App\Rules\OpenDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership and the closed-month check live in ExpensePolicy
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $currentCategory = $this->route('expense')?->category_id;

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where(fn ($q) => $q->whereNull('archived_at')->orWhere('id', $currentCategory))),
            ],
            'date' => ['required', 'date', new OpenDate($user)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'How much was it?',
            'amount.min' => 'The amount has to be more than zero.',
            'category_id.required' => 'Pick a category.',
            'category_id.exists' => 'Pick one of your categories.',
        ];
    }
}
