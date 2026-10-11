<?php

namespace App\Models;

use App\Models\Concerns\StoresPlainDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money put aside for a goal (or taken back out, when negative).
 */
class GoalContribution extends Model
{
    use StoresPlainDates;

    /** @var list<string> */
    protected array $plainDates = ['date'];

    protected $fillable = [
        'savings_goal_id',
        'user_id',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'savings_goal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
