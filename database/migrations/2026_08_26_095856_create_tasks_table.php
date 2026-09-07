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
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            $table->string('title');
            $table->enum('allocation', ['Office', 'Field', 'Office & Field']);
            $table->string('days')->nullable(); // نص وليس رقم دائماً

            $table->text('note')->nullable();
            $table->string('related')->nullable();

            $table->unsignedInteger('sequence');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};