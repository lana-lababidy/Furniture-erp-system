<?php

return [
    /*
     * تسلسل المهام التلقائي حسب منهجية المشروع (methodology).
     * كل مهمة: title, allocation, days (نص وليس رقم بالضرورة), note, related
     */
    'project_task_flow' => [

        'Quantitative' => [
            ['title' => 'Sampling', 'allocation' => 'Office', 'days' => '1'],
            ['title' => 'Questionnaire design', 'allocation' => 'Office', 'days' => '2'],
            ['title' => 'Questionnaire Translation', 'allocation' => 'Office', 'days' => '2'],
            ['title' => 'Scripting', 'allocation' => 'Office', 'days' => '2'],
            [
                'title' => 'Script test',
                'allocation' => 'Office & Field',
                'days' => '1',
                'note' => '3 signatures (Project manager, Field Manager, QC)',
            ],
            [
                'title' => 'Assign Task and cost (Agreements)',
                'allocation' => 'Office & Field',
                'days' => '1',
                'related' => 'Accounting & data',
            ],
            ['title' => 'Briefing', 'allocation' => 'Field', 'days' => null],
            ['title' => 'Pilot', 'allocation' => 'Field', 'days' => '3'],
            ['title' => 'Build the QC and analysis based on Dummy data', 'allocation' => 'Office', 'days' => '1'],
            ['title' => 'Field work', 'allocation' => 'Field', 'days' => 'Based on the sample'],
            ['title' => 'QC Audio', 'allocation' => 'Office & Field', 'days' => 'Based on the sample'],
            ['title' => 'QC GPS', 'allocation' => 'Office & Field', 'days' => 'Based on the sample'],
            ['title' => 'QC Logic and analysis', 'allocation' => 'Office & Field', 'days' => 'Based on the sample'],
            [
                'title' => 'Data cleaning & Coding',
                'allocation' => 'Office',
                'days' => 'Based on the sample',
                'related' => 'Accounting & data',
            ],
        ],

        'Qualitative' => [
            ['title' => 'Target Criteria', 'allocation' => 'Office', 'days' => '1'],
            ['title' => 'Screening questionnaire design', 'allocation' => 'Office', 'days' => '1'],
            ['title' => 'Screening questionnaire Translation', 'allocation' => 'Office', 'days' => '1'],
            [
                'title' => 'Screening data sheet',
                'allocation' => 'Office',
                'days' => '1',
                'related' => 'Cumulative',
            ],
            [
                'title' => 'Assign Task and cost (Agreements) to recruiters',
                'allocation' => 'Office & Field',
                'days' => '1',
                'related' => 'Accounting & data',
            ],
            ['title' => 'Recruiting', 'allocation' => 'Field', 'days' => 'Based on target Number'],
            ['title' => 'Discussion guide design', 'allocation' => 'Office', 'days' => '2'],
            ['title' => 'Discussion guide Translation', 'allocation' => 'Office', 'days' => '2'],
            ['title' => 'Briefing', 'allocation' => 'Field', 'days' => '1'],
            [
                'title' => 'Assign Task and cost (Agreements) to moderator',
                'allocation' => 'Office & Field',
                'days' => '2',
                'related' => 'Accounting & data',
            ],
            ['title' => 'Conducting interviews', 'allocation' => 'Field', 'days' => 'Based on target Number'],
            ['title' => 'Audio upload', 'allocation' => 'Field', 'days' => 'Based on target Number'],
            ['title' => 'QC', 'allocation' => 'Office', 'days' => 'Based on target Number'],
            [
                'title' => 'Assign Task and cost (Agreements) to Transcriber',
                'allocation' => 'Office',
                'days' => '1',
                'related' => 'Accounting & data',
            ],
            ['title' => 'Local Language Transcript', 'allocation' => 'Office', 'days' => 'Based on target Number'],
            [
                'title' => 'Assign Task and cost (Agreements) to Translator',
                'allocation' => 'Office',
                'days' => '1',
                'related' => 'Accounting & data',
            ],
            ['title' => 'Translate Transcript to English', 'allocation' => 'Office', 'days' => 'Based on target Number'],
        ],
    ],
];