<?php

namespace App\Http\Requests;

use App\Models\RecurringEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecurringEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership lives in RecurringEntryPolicy
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $currentCategory = $this->route('recurring')?->category_id;

        return [
            'kind' => ['required', Rule::in([RecurringEntry::EXPENSE, RecurringEntry::INCOME])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'required_if:kind,'.RecurringEntry::EXPENSE,
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where(fn ($q) => $q->whereNull('archived_at')->orWhere('id', $currentCategory))),
            ],
            'day_of_month' => ['required', 'integer', 'between:1,31'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ];
    }

    /**
     * Income doesn't have a category.
     *
     * @return array<string, mixed>
     */
    public function entryData(): array
    {
        $data = $this->validated();

        if ($data['kind'] === RecurringEntry::INCOME) {
            $data['category_id'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Give it a name, like "Rent" or "Salary".',
            'category_id.required_if' => 'Pick a category for this expense.',
            'day_of_month.between' => 'Pick a day between 1 and 31.',
            'ends_on.after_or_equal' => "It can't end before it starts.",
        ];
    }
}
