<?php

namespace App\Models;

use App\Models\Concerns\StoresPlainDates;
use App\Support\EntryWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, Prunable, SoftDeletes, StoresPlainDates;

    /** @var list<string> */
    protected array $plainDates = ['date'];

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Whether the entry's month is still open for changes.
     */
    public function isOpen(): bool
    {
        return EntryWindow::isOpen($this->date, $this->user);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Deleted entries are kept for a while in "Recently deleted".
     */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subDays(config('budgetwise.trash_days')));
    }
}
