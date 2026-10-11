<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start')->nullable()->after('period');
        });

        DB::table('budgets')->where('active', false)->delete();

        $weekStarts = DB::table('users')->pluck('week_starts_on', 'id');

        foreach (DB::table('budgets')->get() as $budget) {
            $start = Carbon::parse($budget->start_date);
            $start = $budget->period === 'weekly'
                ? $start->startOfWeek((int) ($weekStarts[$budget->user_id] ?? 6))
                : $start->startOfMonth();

            $categoryId = $budget->category === 'Total' ? null : DB::table('categories')
                ->where('user_id', $budget->user_id)
                ->where('name', $budget->category)
                ->value('id');

            DB::table('budgets')->where('id', $budget->id)->update([
                'period_start' => $start->toDateString(),
                'category_id' => $categoryId,
            ]);
        }

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn(['category', 'start_date', 'active']);
            $table->unique(['user_id', 'period', 'period_start', 'category_id']);
        });
    }

    public function down(): void
    {
        // MySQL let the unique index stand in for the user_id foreign key index.
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'period', 'period_start', 'category_id']);
            $table->string('category')->default('Total')->after('user_id');
            $table->date('start_date')->nullable();
            $table->boolean('active')->default(true);
        });

        DB::table('budgets')->update([
            'start_date' => DB::raw('period_start'),
            'category' => DB::raw("coalesce((select categories.name from categories where categories.id = budgets.category_id), 'Total')"),
        ]);

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('period_start');
        });
    }
};
