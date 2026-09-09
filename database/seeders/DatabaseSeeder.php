<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,           // لازم يجي أول شي (users بتعتمد عليه)
            WorkflowSettingSeeder::class, // قالب ثابت، ما بيعتمد عليه أي شي
            UserSeeder::class,
            DemoDataSeeder::class,      // بيعتمد على RoleSeeder
        ]);
    }
}
