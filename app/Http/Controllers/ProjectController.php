<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\WorkflowSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'methodology' => ['required', 'in:' . implode(',', WorkflowSetting::METHODOLOGIES)],
        ]);

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create([
                'company_id' => $validated['company_id'],
                'contact_id' => $validated['contact_id'] ?? null,
                'name' => $validated['name'],
                'methodology' => $validated['methodology'],
                'status' => 'Lead',
            ]);

            $this->generateTasksFromWorkflowTemplate($project);

            return $project;
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', __('تم إنشاء المشروع وتوليد المهام بنجاح.'));
    }

    private function generateTasksFromWorkflowTemplate(Project $project): void
    {
        $template = WorkflowSetting::query()
            ->forMethodology($project->methodology)
            ->get();

        if ($template->isEmpty()) {
            throw new \RuntimeException(
                "لا يوجد قالب مهام مُعرَّف لمنهجية: {$project->methodology}"
            );
        }

        $tasks = $template->map(fn (WorkflowSetting $setting) => [
            'project_id' => $project->id,
            'title' => $setting->task_name,
            'allocation' => $setting->allocation,
            'days' => $setting->days,
            'note' => $setting->note,
            'related' => $setting->related,
            'sequence' => $setting->sequence,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        Task::insert($tasks);
    }
}