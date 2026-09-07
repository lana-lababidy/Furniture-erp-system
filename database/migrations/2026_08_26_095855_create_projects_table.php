<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('assigned_pm_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->enum('methodology', ['Quantitative', 'Qualitative', 'Hybrid']);
            $table->enum('status', ['Lead', 'Proposal', 'Contract', 'In Progress', 'Completed'])->default('Lead');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};