<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $companyAmal  = Company::where('name', 'شركة الأمل للتجارة')->first();
        $companyNoor  = Company::where('name', 'مجموعة النور الصناعية')->first();
        $companyFajr  = Company::where('name', 'مؤسسة الفجر للمقاولات')->first();

        $contacts = [
            [
                'name'       => 'أحمد الزعبي',
                'phone'      => '0791234567',
                'notes'      => 'عميل دائم - يفضل التسليم آخر الأسبوع',
                'company_id' => $companyAmal?->id,
            ],
            [
                'name'       => 'سارة العودة',
                'phone'      => '0797654321',
                'notes'      => null,
                'company_id' => $companyNoor?->id,
            ],
            [
                'name'       => 'محمد أبو حمدة',
                'phone'      => '0788112233',
                'notes'      => 'صاحب معرض أثاث - طلبات بالجملة',
                'company_id' => $companyFajr?->id,
            ],
            [
                'name'       => 'ليلى حسن',
                'phone'      => '0799887766',
                'notes'      => null,
                'company_id' => null, // جهة اتصال مستقلة، بدون شركة
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::firstOrCreate(
                ['phone' => $contact['phone']],
                $contact
            );
        }
    }
}