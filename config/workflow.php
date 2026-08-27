<?php

return [
    /*
     * تسلسل الأدوار المطلوبة لكل فئة منتج، بالترتيب.
     * المفتاح = اسم الفئة (categories.name)
     * القيمة = مصفوفة أسماء الأدوار بالترتيب (roles.name)
     */
    'category_task_flow' => [
        'bedroom'     => ['carpenter', 'painter', 'warehouse'],
        'sofa'        => ['carpenter', 'upholsterer', 'warehouse'],
        'kitchen'     => ['designer', 'carpenter', 'installer', 'warehouse'],
        'metal_table' => ['welder', 'painter', 'warehouse'],
    ],
];