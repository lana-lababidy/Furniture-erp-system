<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'سارة الاستقبال', 'phone' => '0500000000', 'role' => 'receptionist'],
            ['name' => 'عبدالله المدير', 'phone' => '0500000009', 'role' => 'manager'],
            ['name' => 'محمد النجار',    'phone' => '0500000001', 'role' => 'carpenter'],
            ['name' => 'خالد البخاخ',    'phone' => '0500000002', 'role' => 'painter'],
            ['name' => 'علي المنجّد',    'phone' => '0500000003', 'role' => 'upholsterer'],
            ['name' => 'سعد الحداد',     'phone' => '0500000004', 'role' => 'welder'],
            ['name' => 'فهد المستودع',   'phone' => '0500000005', 'role' => 'warehouse'],
            ['name' => 'نورة المصممة',   'phone' => '0500000006', 'role' => 'designer'],
            ['name' => 'ياسر المركب',    'phone' => '0500000007', 'role' => 'installer'],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();

            User::firstOrCreate(
                ['phone' => $userData['phone']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make('password'), // كلمة سر موحدة للتجربة فقط
                    'role_id'  => $role->id,
                ]
            );
        }
    }
}
