<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'hr',
            'receptionist', // موظف استقبال - ينشئ الطلبات
            'manager',      // مدير - صلاحيات إدارية عامة
            'carpenter',    // نجار
            'painter',      // بخاخ
            'upholsterer',  // منجّد
            'welder',       // حداد
            'warehouse',    // أمين مستودع
            'designer',     // مصمم
            'installer',    // مركب
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
