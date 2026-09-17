<?php

use App\Models\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, increment the order of statuses that currently have order >= 4
        // This makes room for the new "Pending Approval" status
        DB::table('task_statuses')
            ->where('order', '>=', 4)
            ->increment('order');

        // Then add the "Pending Approval" status with order 4
        // This will be inserted after "In Review" (order 3) and before the shifted statuses
        DB::table('task_statuses')->insert([
            'name' => 'Pending Approval',
            'slug' => 'pending-approval',
            'color' => '#FFAB00',
            'category' => 'active',
            'order' => 4,
            'is_default' => false,
            'is_closed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, remove the "Pending Approval" status
        DB::table('task_statuses')
            ->where('slug', 'pending-approval')
            ->delete();

        // Then decrement the order of statuses that have order > 4
        // This reverses the increment we did in up()
        DB::table('task_statuses')
            ->where('order', '>', 4)
            ->decrement('order');
    }
};
