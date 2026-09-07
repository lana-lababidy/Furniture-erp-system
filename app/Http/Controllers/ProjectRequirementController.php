<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectRequirementController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:CAPI,CATI,CAWI,CLT,PAPI,Mystery shopping,RA,FG,IDI,KII,Observation,Site Visit'],
            'sample_size' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($project, $validated) {
            $requirement = $project->projectRequirements()->create([
                'type' => $validated['type'],
                'sample_size' => $validated['sample_size'],
            ]);

            $this->linkRequirementToExistingTasks($project, $requirement);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', __('تمت إضافة المتطلب وربطه بمهام المشروع بنجاح.'));
    }

    /**
     * تربط المتطلب الجديد تلقائياً بكل المهام الموجودة حالياً للمشروع.
     */
    private function linkRequirementToExistingTasks(Project $project, ProjectRequirement $requirement): void
    {
        $taskIds = $project->tasks()->pluck('id');

        if ($taskIds->isEmpty()) {
            return; // المشروع ما فيه مهام بعد؟ نتجاوز الربط بدون خطأ
        }

        $requirement->tasks()->attach($taskIds);
    }
}