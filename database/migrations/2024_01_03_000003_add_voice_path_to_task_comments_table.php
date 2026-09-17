<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->string('voice_path')->nullable()->after('content');
            $table->boolean('is_voice')->default(false)->after('voice_path');
        });
    }

    public function down(): void
    {
        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropColumn(['voice_path', 'is_voice']);
        });
    }
};