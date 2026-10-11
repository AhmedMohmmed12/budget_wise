<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10); // expense | income
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('description', 100);
            $table->unsignedTinyInteger('day_of_month');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->date('last_posted_month')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_posted_month']);
        });

        foreach (['expenses', 'incomes'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('recurring_entry_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['expenses', 'incomes'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('recurring_entry_id');
            });
        }

        Schema::dropIfExists('recurring_entries');
    }
};
