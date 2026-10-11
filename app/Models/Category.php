<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'position',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'position' => 'integer',
    ];

    public static function createDefaultsFor(User $user): void
    {
        $position = 0;

        foreach (config('budgetwise.default_categories') as $name => $color) {
            $user->categories()->firstOrCreate(['name' => $name], [
                'color' => $color,
                'position' => $position++,
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Used categories can be archived but not deleted (deleted entries count
     * too, since they can still be restored).
     */
    public function isInUse(): bool
    {
        return $this->expenses()->withTrashed()->exists() || $this->budgets()->exists();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
