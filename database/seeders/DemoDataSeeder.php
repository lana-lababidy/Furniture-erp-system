<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use App\Models\WorkflowSetting;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // لازم يكون في PM موجود قبل هيك (من UserSeeder)
        $pm = User::whereHas('role', fn ($q) => $q->where('name', 'project_manager'))->first();

        // 1) Company
        $company = Company::create([
            'name' => 'شركة تجريبية للأبحاث',
            'address' => 'عمان - الأردن',
        ]);

        // 2) Contact مرتبط بالشركة
        $contact = Contact::create([
            'company_id' => $company->id,
            'name' => 'أحمد المندوب',
            'phone' => '0791234567',
            'notes' => 'جهة الاتصال الرئيسية للمشروع',
        ]);

        // 3) Project (Quantitative مثلاً)
        $project = Project::create([
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'assigned_pm_id' => $pm?->id,
            'name' => 'مشروع مسح رضا العملاء 2026',
            'methodology' => 'Quantitative',
            'status' => 'Lead',
        ]);

        // 4) Project Requirement
        $requirement = ProjectRequirement::create([
            'project_id' => $project->id,
            'type' => 'CAPI',
            'sample_size' => 500,
        ]);

        // 5) ⚡ توليد المهام تلقائيًا من workflow_settings (نفس منطق الـ auto-trigger)
        $templates = WorkflowSetting::where('methodology', 'Quantitative')
            ->orderBy('sequence')
            ->get();

        foreach ($templates as $template) {
            $task = Task::create([
                'project_id' => $project->id,
                'title' => $template->task_name,
                'allocation' => $template->allocation,
                'days' => $template->days,
                'note' => $template->note,
                'related' => $template->related,
                'sequence' => $template->sequence,
                'status' => 'pending',
                'required_role' => $template->required_role ?? null,
            ]);

            // ربط المهمة بالـ requirement (pivot)
            $requirement->tasks()->attach($task->id);
        }
    }
}