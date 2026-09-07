<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskApiController extends Controller
{
    /**
     * GET /api/tasks?project_id=1&status=pending
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->query('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $tasks = $query->orderBy('sequence')->paginate(20);

        return response()->json($tasks);
    }

    /**
     * PATCH /api/tasks/{task}/start
     */
    public function start(Task $task): JsonResponse
    {
        if (!$task->isUnlocked()) {
            return response()->json([
                'message' => 'لا يمكن بدء هذه المهمة قبل إكمال المهام السابقة لها بالتسلسل',
            ], 422);
        }

        if ($task->status !== 'pending') {
            return response()->json([
                'message' => 'هذه المهمة ليست بحالة pending',
            ], 422);
        }

        $task->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم بدء المهمة بنجاح',
            'task'    => $task->fresh('project'),
        ]);
    }

    /**
     * PATCH /api/tasks/{task}/complete
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        if ($task->status !== 'in_progress') {
            return response()->json([
                'message' => 'هذه المهمة ليست قيد التنفيذ حالياً',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'note' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $task->update([
            'status'      => 'completed',
            'finished_at' => now(),
            'note'        => $request->input('note') ?? $task->note,
        ]);

        $this->refreshProjectStatus($task->project);

        return response()->json([
            'message' => 'تم إنهاء المهمة بنجاح',
            'task'    => $task->fresh('project'),
        ]);
    }

    /**
     * تحديث حالة المشروع تلقائياً حسب حالة مهامه.
     */
    private function refreshProjectStatus(Project $project): void
    {
        $totalTasks     = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('status', 'completed')->count();

        if ($totalTasks > 0 && $totalTasks === $completedTasks) {
            $project->update(['status' => 'Completed']);
        } elseif ($completedTasks > 0) {
            $project->update(['status' => 'In Progress']);
        }
    }
}