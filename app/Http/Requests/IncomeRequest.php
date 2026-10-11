<?php

namespace App\Http\Requests;

use App\Rules\OpenDate;
use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership and the closed-month check live in IncomePolicy
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'source' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date', new OpenDate($this->user())],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'How much came in?',
            'amount.min' => 'The amount has to be more than zero.',
            'source.required' => 'Where did it come from? Salary, a client, a gift…',
        ];
    }
}
