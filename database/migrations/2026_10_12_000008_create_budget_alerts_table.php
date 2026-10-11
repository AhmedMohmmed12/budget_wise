<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 10);
            $table->date('period_start');
            // "total" or "category:{id}"; a string so the unique index also covers the total
            $table->string('scope', 30);
            $table->unsignedTinyInteger('threshold');
            $table->timestamp('sent_at');

            $table->unique(['user_id', 'period', 'period_start', 'scope', 'threshold'], 'budget_alerts_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
    }
};
