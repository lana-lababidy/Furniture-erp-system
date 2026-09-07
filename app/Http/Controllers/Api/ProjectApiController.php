<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectApiController extends Controller
{
    /**
     * POST /api/projects
     * إنشاء مشروع جديد + متطلباته + توليد المهام تلقائياً حسب المنهجية، ضمن معاملة واحدة.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id'   => ['required', 'exists:companies,id'],
            'contact_id'   => ['nullable', 'exists:contacts,id'],
            'name'         => ['required', 'string', 'max:255'],
            'methodology'  => ['required', 'in:Quantitative,Qualitative'],
            'status'       => ['nullable', 'in:Lead,Proposal,Contract,In Progress,Completed'],

            'requirements'                => ['required', 'array', 'min:1'],
            'requirements.*.type'         => [
                'required',
                'in:CAPI,CATI,CAWI,CLT,PAPI,Mystery shopping,FG,IDI,KII,Observation',
            ],
            'requirements.*.sample_size'  => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $taskFlow = config('workflow.project_task_flow.' . $data['methodology']);

        if (!$taskFlow) {
            return response()->json([
                'message' => 'لا يوجد تسلسل مهام معرّف لهذه المنهجية',
            ], 422);
        }

        $project = DB::transaction(function () use ($data, $taskFlow) {
            $project = Project::create([
                'company_id'  => $data['company_id'],
                'contact_id'  => $data['contact_id'] ?? null,
                'name'        => $data['name'],
                'methodology' => $data['methodology'],
                'status'      => $data['status'] ?? 'Lead',
            ]);

            foreach ($data['requirements'] as $requirement) {
                $project->requirements()->create([
                    'type'        => $requirement['type'],
                    'sample_size' => $requirement['sample_size'],
                ]);
            }

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

            return $project;
        });

        return response()->json([
            'message' => 'تم إنشاء المشروع وتوليد المهام بنجاح',
            'project' => $project->load(['company', 'contact', 'requirements', 'tasks']),
        ], 201);
    }

    /**
     * GET /api/projects
     * عرض كل المشاريع مع فلترة اختيارية بالحالة أو المنهجية.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['company:id,name', 'contact:id,name', 'requirements']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('methodology')) {
            $query->where('methodology', $request->query('methodology'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * GET /api/projects/{project}
     * عرض تفاصيل مشروع واحد مع متطلباته ومهامه بالتسلسل.
     */
    public function show(Project $project): JsonResponse
    {
        $project->load(['company', 'contact', 'requirements', 'tasks']);

        return response()->json($project);
    }
}