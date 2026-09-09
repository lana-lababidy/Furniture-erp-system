<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'phone' => '0700000001',
                'role' => 'admin',
            ],
            [
                'name' => 'Project Manager User',
                'phone' => '0700000002',
                'role' => 'project_manager',
            ],
            [
                'name' => 'Field Team User',
                'phone' => '0700000003',
                'role' => 'field_team',
            ],
            [
                'name' => 'QC User',
                'phone' => '0700000004',
                'role' => 'qc',
            ],
            [
                'name' => 'Data Entry User',
                'phone' => '0700000005',
                'role' => 'data_entry',
            ],
            [
                'name' => 'HR User',
                'phone' => '0700000006',
                'role' => 'hr',
            ],
        ];

        foreach ($users as $u) {
            $role = Role::where('name', $u['role'])->first();

            if (!$role) {
                // تأكد إنو RoleSeeder اشتغل قبل هيك
                continue;
            }
            User::firstOrCreate(
                ['phone' => $u['phone']],
                [
                    'name' => $u['name'],
                    'password' => 'password', 
                    'role_id' => $role->id,
                ]
            );
        }
    }
}
