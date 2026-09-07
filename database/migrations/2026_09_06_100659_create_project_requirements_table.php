
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('type', [
                'CAPI', 'CATI', 'CAWI', 'CLT', 'PAPI', 
                'Mystery shopping', 'RA', 'FG', 'IDI', 'KII', 'Observation', 'Site Visit'
            ]);
            $table->integer('sample_size')->default(0); // حجم العينة المطلوب (مثال: 500 أو 18)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};