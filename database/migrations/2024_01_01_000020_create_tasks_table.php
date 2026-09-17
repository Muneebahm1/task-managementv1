<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_key')->unique(); // e.g. TASK-001
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('type', ['task', 'bug', 'feature', 'story', 'epic', 'improvement'])->default('task');
            $table->enum('priority', ['lowest', 'low', 'medium', 'high', 'highest'])->default('medium');
            $table->foreignId('status_id')->constrained('task_statuses')->onDelete('restrict');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date')->nullable();
            $table->integer('story_points')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('logged_hours')->default(0);
            $table->string('labels')->nullable(); // comma-separated
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
