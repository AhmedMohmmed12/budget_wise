<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->softDeletes();
            $table->index(['user_id', 'date']);
        });

        DB::table('expenses')->update([
            'category_id' => DB::raw('(select categories.id from categories where categories.user_id = expenses.user_id and categories.name = expenses.category)'),
        ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->default('Others')->after('amount');
        });

        DB::table('expenses')->update([
            'category' => DB::raw("coalesce((select categories.name from categories where categories.id = expenses.category_id), 'Others')"),
        ]);

        // MySQL let the (user_id, date) index stand in for the user_id foreign key index.
        Schema::table('expenses', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
