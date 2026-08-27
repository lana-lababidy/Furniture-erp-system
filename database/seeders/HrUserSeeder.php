<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrUserSeeder extends Seeder
{
    public function run(): void
    {
        $hrRole = Role::where('name', 'hr')->first();

        if (!$hrRole) {
            $this->command->error('دور hr غير موجود. شغّلي RoleSeeder أولاً.');
            return;
        }

        User::firstOrCreate(
            ['phone' => '0790000001'],
            [
                'name'     => 'موظف الموارد البشرية',
                'password' => Hash::make('password123'),
                'role_id'  => $hrRole->id,
            ]
        );

        // حساب أدمن أيضاً (مفيد لاختبار role:hr,admin)
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            User::firstOrCreate(
                ['phone' => '0790000000'],
                [
                    'name'     => 'المدير العام',
                    'password' => Hash::make('password123'),
                    'role_id'  => $adminRole->id,
                ]
            );
        }
    }
}
