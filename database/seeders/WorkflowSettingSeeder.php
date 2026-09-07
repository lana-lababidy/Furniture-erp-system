<?php

namespace Database\Seeders;

use App\Models\WorkflowSetting;
use Illuminate\Database\Seeder;

class WorkflowSettingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedQuantitativeTasks();
        $this->seedQualitativeTasks();
    }

    private function seedQuantitativeTasks(): void
    {
        $tasks = [
            // [task_name, allocation, days, note, related]
            ['Samplig', 'Office', '1', null, null],
            ['Questionnaire design', 'Office', '2', null, null],
            ['Questionnaire Translation', 'Office', '2', null, null],
            ['Screpting', 'Office', '2', null, null],
            ['Script test', 'Office & Field', '1', '3 signatures (Project manager, Field Manager, QC)', null],
            ['Assign Task and cost (Agreements)', 'Office & Field', '1', null, 'Accounting & data'],
            // ⚠️ القيمة الأصلية بالإكسل كانت تاريخ (2026-02-03) بدل رقم - تحققي من الصحيح
            ['Briefing', 'Field', '3', null, null],
            ['Pilot', 'Field', '3', null, null],
            ['Build the QC and analysis based on Dummy data', 'Office', '1', null, null],
            ['Field work', 'Field', 'Based on the sample', null, null],
            ['QC Audio', 'Office & Field', 'Based on the sample', null, null],
            ['QC GPS', 'Office & Field', 'Based on the sample', null, null],
            ['GC Logic and analysis', 'Office & Field', 'Based on the sample', null, null],
            ['Data cleaning & Codding', 'Office', 'Based on the sample', null, 'Accounting & data'],
        ];

        $this->insertTasks(WorkflowSetting::METHODOLOGY_QUANTITATIVE, $tasks);
    }

    private function seedQualitativeTasks(): void
    {
        $tasks = [
            // [task_name, allocation, days, note, related]
            ['Target Criteria', 'Office', '1', null, null],
            ['Screening questionnaire design', 'Office', '1', null, null],
            ['Screening questionnaire Translation', 'Office', '1', null, null],
            ['Screening data sheet', 'Office', '1', null, 'Cumulative'],
            ['Assign Task and cost (Agreements) to recruiters', 'Office & Field', '1', null, 'Accounting & data'],
            ['Recruiting', 'Field', 'Based on target Number', null, null],
            ['Discussion guide design', 'Office', '2', null, null],
            ['Discussion guide Translation', 'Office', '2', null, null],
            ['Briefing', 'Field', '1', null, null],
            ['Assign Task and cost (Agreements) to moderator', 'Office & Field', '2', null, 'Accounting & data'],
            ['Conducting interviews', 'Field', 'Based on target Number', null, null],
            ['Audio upload', 'Field', 'Based on target Number', null, null],
            ['QC', 'Office', 'Based on target Number', null, null],
            ['Assign Task and cost (Agreements) to Transcriber', 'Office', '1', null, 'Accounting & data'],
            ['Local Language Transcript', 'Office', 'Based on target Number', null, null],
            ['Assign Task and cost (Agreements) to Translator', 'Office', '1', null, 'Accounting & data'],
            ['Translate Transcript to English', 'Office', 'Based on target Number', null, null],
        ];

        $this->insertTasks(WorkflowSetting::METHODOLOGY_QUALITATIVE, $tasks);
    }

    /**
     * @param  array<int, array{0:string,1:string,2:?string,3:?string,4:?string}>  $tasks
     */
    private function insertTasks(string $methodology, array $tasks): void
    {
        foreach ($tasks as $index => [$taskName, $allocation, $days, $note, $related]) {
            WorkflowSetting::updateOrCreate(
                [
                    'methodology' => $methodology,
                    'sequence' => $index + 1,
                ],
                [
                    'task_name' => $taskName,
                    'allocation' => $allocation,
                    'days' => $days,
                    'note' => $note,
                    'related' => $related,
                ]
            );
        }
    }
}