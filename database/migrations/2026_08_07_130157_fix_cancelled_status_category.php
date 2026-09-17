<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix the Cancelled status - it shouldn't be in 'done' category
        // Cancelled tasks are not completed, they're cancelled
        DB::table('task_statuses')
            ->where('slug', 'cancelled')
            ->update([
                'category' => 'cancelled',
                'is_closed' => false, // Cancelled tasks are not "done"
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes
        DB::table('task_statuses')
            ->where('slug', 'cancelled')
            ->update([
                'category' => 'done',
                'is_closed' => true,
            ]);
    }
};
