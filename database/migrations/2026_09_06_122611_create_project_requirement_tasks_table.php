<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requirement_task', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_requirement_id')
                ->constrained('project_requirements')
                ->cascadeOnDelete();

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->timestamps();

            // يمنع تكرار نفس الربط بين نفس المهمة ونفس المتطلب
            $table->unique(['project_requirement_id', 'task_id'], 'req_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requirement_task');
    }
};