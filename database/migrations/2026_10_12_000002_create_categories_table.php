<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The categories that were hard-coded before this migration. */
    private const LEGACY = [
        'Restaurants' => '#c0623b',
        'Housing' => '#2f5d50',
        'Transportation' => '#3d6b8c',
        'Shopping' => '#8a5a8c',
        'Entertainment' => '#b8892e',
        'Others' => '#6b6457',
    ];

    private const SPARE_COLORS = ['#5f7a3a', '#a33f5c', '#2f5d50', '#3d6b8c'];

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->char('color', 7);
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            $names = collect(array_keys(self::LEGACY))
                ->merge(DB::table('expenses')->where('user_id', $userId)->distinct()->pluck('category'))
                ->merge(DB::table('budgets')->where('user_id', $userId)->where('category', '!=', 'Total')->distinct()->pluck('category'))
                ->filter()
                ->unique(fn ($name) => mb_strtolower($name))
                ->values();

            $rows = $names->map(fn ($name, $i) => [
                'user_id' => $userId,
                'name' => $name,
                'color' => self::LEGACY[$name] ?? self::SPARE_COLORS[$i % count(self::SPARE_COLORS)],
                'position' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('categories')->insert($rows->all());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
