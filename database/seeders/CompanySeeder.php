<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name'    => 'شركة الأمل للتجارة',
                'address' => 'عمان - شارع الجامعة',
            ],
            [
                'name'    => 'مجموعة النور الصناعية',
                'address' => 'إربد - المنطقة الصناعية',
            ],
            [
                'name'    => 'مؤسسة الفجر للمقاولات',
                'address' => 'الزرقاء - حي الأمير حسن',
            ],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(
                ['name' => $company['name']],
                $company
            );
        }
    }
}