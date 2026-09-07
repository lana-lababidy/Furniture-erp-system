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
            ['name' => 'مدير المشروع الأول', 'phone' => '0500000001', 'role' => 'project_manager'],
            ['name' => 'فريق ميداني 1',      'phone' => '0500000002', 'role' => 'field_team'],
            ['name' => 'فريق ميداني 2',      'phone' => '0500000003', 'role' => 'field_team'],
            ['name' => 'مراقب الجودة',       'phone' => '0500000004', 'role' => 'qc'],
            ['name' => 'مدخل بيانات',        'phone' => '0500000005', 'role' => 'data_entry'],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();

            if (!$role) {
                $this->command->error("الدور '{$userData['role']}' غير موجود. شغّلي RoleSeeder أولاً.");
                continue;
            }

            User::firstOrCreate(
                ['phone' => $userData['phone']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make('password'),
                    'role_id'  => $role->id,
                ]
            );
        }
    }
}