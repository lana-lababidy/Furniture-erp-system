<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $companyAmal = Company::where('name', 'شركة الأمل للتجارة')->first();
        $ahmad       = Contact::where('phone', '0791234567')->first();

        $companyNoor = Company::where('name', 'مجموعة النور الصناعية')->first();
        $sara        = Contact::where('phone', '0797654321')->first();

        if (!$companyAmal || !$companyNoor) {
            $this->command->error('الشركات غير موجودة. شغّلي CompanySeeder و ContactSeeder أولاً.');
            return;
        }

        $projects = [
            [
                'company_id'  => $companyAmal->id,
                'contact_id'  => $ahmad?->id,
                'name'        => 'دراسة رضا العملاء - قطاع التجزئة',
                'methodology' => 'Quantitative',
                'status'      => 'In Progress',
                'requirements' => [
                    ['type' => 'CAPI', 'sample_size' => 500],
                ],
            ],
            [
                'company_id'  => $companyNoor->id,
                'contact_id'  => $sara?->id,
                'name'        => 'مجموعات نقاش حول تجربة المستخدم',
                'methodology' => 'Qualitative',
                'status'      => 'Lead',
                'requirements' => [
                    ['type' => 'FG', 'sample_size' => 40],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $requirements = $projectData['requirements'];
            unset($projectData['requirements']);

            $project = Project::firstOrCreate(
                ['name' => $projectData['name']],
                $projectData
            );

            if ($project->wasRecentlyCreated) {
                foreach ($requirements as $requirement) {
                    $project->requirements()->create($requirement);
                }

                $taskFlow = config('workflow.project_task_flow.' . $project->methodology);

                foreach ($taskFlow as $index => $taskDefinition) {
                    $project->tasks()->create([
                        'title'      => $taskDefinition['title'],
                        'allocation' => $taskDefinition['allocation'],
                        'days'       => $taskDefinition['days'] ?? null,
                        'note'       => $taskDefinition['note'] ?? null,
                        'related'    => $taskDefinition['related'] ?? null,
                        'sequence'   => $index + 1,
                        'status'     => 'pending',
                    ]);
                }
            }
        }
    }
}