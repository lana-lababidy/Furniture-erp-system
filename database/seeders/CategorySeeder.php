<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'bedroom',      // غرفة نوم
            'sofa',         // طقم كنب
            'kitchen',      // مطبخ مفصّل
            'metal_table',  // طاولة معدنية
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category]);
        }
    }
}
