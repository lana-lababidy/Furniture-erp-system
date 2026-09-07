<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_settings', function (Blueprint $table) {
            $table->id();

            $table->enum('methodology', ['Quantitative', 'Qualitative']);
            $table->string('task_name');
            $table->enum('allocation', ['Office', 'Field', 'Office & Field']);
            $table->string('days')->nullable();
            $table->string('note')->nullable();
            $table->string('related')->nullable();
            $table->unsignedInteger('sequence');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_settings');
    }
};