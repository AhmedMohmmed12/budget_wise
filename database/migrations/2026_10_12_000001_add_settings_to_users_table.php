<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('currency', 3)->default('USD')->after('password');
            $table->string('timezone', 64)->default('UTC')->after('currency');
            $table->boolean('email_alerts')->default(true)->after('timezone');
            $table->string('budget_period', 10)->default('monthly')->after('email_alerts');
            $table->unsignedTinyInteger('week_starts_on')->default(6)->after('budget_period');
        });

        // Keep whatever period each user already budgets in.
        $periods = DB::table('budgets')->where('category', 'Total')->pluck('period', 'user_id');
        foreach ($periods as $userId => $period) {
            DB::table('users')->where('id', $userId)->update(['budget_period' => $period ?: 'monthly']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'timezone', 'email_alerts', 'budget_period', 'week_starts_on']);
        });
    }
};
