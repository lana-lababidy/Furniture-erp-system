<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'name'  => 'أحمد الزعبي',
                'phone' => '0791234567',
                'notes' => 'عميل دائم - يفضل التسليم آخر الأسبوع',
            ],
            [
                'name'  => 'سارة العودة',
                'phone' => '0797654321',
                'notes' => null,
            ],
            [
                'name'  => 'محمد أبو حمدة',
                'phone' => '0788112233',
                'notes' => 'صاحب معرض أثاث - طلبات بالجملة',
            ],
            [
                'name'  => 'ليلى حسن',
                'phone' => '0799887766',
                'notes' => null,
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