<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        // لا يوجد rollback حقيقي هون لأن هاي عملية تنظيف نهائية مقصودة
    }
};