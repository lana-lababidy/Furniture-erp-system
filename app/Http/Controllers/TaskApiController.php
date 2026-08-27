<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskApiController extends Controller
{
    /**
     * عرض مهام الموظف المسجل دخوله حالياً، حسب دوره (role_id من الـ Token).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::with(['order', 'role'])
            ->where('role_id', $user->role_id)
            ->orderBy('created_at')
            ->get();

        $tasks = $tasks->map(function ($task) {
            $task->is_unlocked = $task->isUnlocked();
            return $task;
        });

        return response()->json($tasks);
    }

    /**
     * بدء المهمة: التحقق من الاعتمادية، ثم تغيير الحالة لـ in_progress.
     */
    public function start(Request $request, Task $task)
    {
        $user = $request->user();

        if ($task->role_id !== $user->role_id) {
            return response()->json([
                'message' => 'هذه المهمة غير مخصصة لدورك الوظيفي',
            ], 403);
        }

        if ($task->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن بدء مهمة إلا إذا كانت حالتها pending',
            ], 422);
        }

        if (!$task->isUnlocked()) {
            return response()->json([
                'message' => 'لا يمكن بدء هذه المهمة قبل اكتمال المهمة السابقة لها',
            ], 422);
        }

        $task->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم بدء المهمة بنجاح',
            'task'    => $task->fresh('role', 'order'),
        ]);
    }

    /**
     * إنهاء المهمة: تسجيل finished_at، حفظ المواد المستخدمة،
     * وتحديث حالة الطلب الكلية تلقائياً إذا اكتملت كل المهام.
     */
    public function complete(Request $request, Task $task)
    {
        $user = $request->user();

        if ($task->role_id !== $user->role_id) {
            return response()->json([
                'message' => 'هذه المهمة غير مخصصة لدورك الوظيفي',
            ], 403);
        }

        if ($task->status !== 'in_progress') {
            return response()->json([
                'message' => 'لا يمكن إنهاء مهمة لم تُبدأ بعد',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes'                    => 'nullable|string',
            'materials'                => 'nullable|array',
            'materials.*.material_name' => 'required_with:materials|string|max:255',
            'materials.*.quantity'      => 'required_with:materials|numeric|min:0',
            'materials.*.unit_cost'     => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $task) {
            $task->update([
                'status'      => 'completed',
                'finished_at' => now(),
                'notes'       => $request->notes ?? $task->notes,
            ]);

            if ($request->filled('materials')) {
                foreach ($request->materials as $material) {
                    $task->materials()->create([
                        'order_id'      => $task->order_id,
                        'material_name' => $material['material_name'],
                        'quantity'      => $material['quantity'],
                        'unit_cost'     => $material['unit_cost'] ?? 0,
                    ]);
                }
            }

            $this->refreshOrderStatus($task->order);
        });

        return response()->json([
            'message' => 'تم إنهاء المهمة بنجاح',
            'task'    => $task->fresh('role', 'order', 'materials'),
        ]);
    }

    /**
     * تحديث حالة الطلب الكلية بناءً على حالة مهامه.
     */
    private function refreshOrderStatus(\App\Models\Order $order): void
    {
        $totalTasks     = $order->tasks()->count();
        $completedTasks = $order->tasks()->where('status', 'completed')->count();

        if ($completedTasks === $totalTasks) {
            $order->update(['status' => 'completed']);
        } elseif ($completedTasks > 0) {
            $order->update(['status' => 'in_progress']);
        }
    }
}