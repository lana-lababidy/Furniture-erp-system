<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * إنشاء طلب جديد + توليد المهام تلقائياً حسب تسلسل الفئة.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id'  => 'required|exists:contacts,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category = Category::findOrFail($request->category_id);

        $flow = config('workflow.category_task_flow.' . $category->name);

        if (!$flow) {
            return response()->json([
                'message' => 'لا يوجد تسلسل مهام معرّف لهذه الفئة',
            ], 422);
        }

        $order = DB::transaction(function () use ($request, $category, $flow) {
            $order = Order::create([
                'contact_id'  => $request->contact_id,
                'category_id' => $category->id,
                'status'      => 'pending',
            ]);

            foreach ($flow as $index => $roleName) {
                $role = Role::where('name', $roleName)->first();

                if (!$role) {
                    throw new \RuntimeException("الدور '{$roleName}' غير موجود بجدول roles");
                }

                $order->tasks()->create([
                    'role_id'  => $role->id,
                    'sequence' => $index + 1,
                    'status'   => 'pending',
                ]);
            }

            return $order;
        });

        return response()->json([
            'message' => 'تم إنشاء الطلب وتوليد المهام بنجاح',
            'order'   => $order->load('tasks.role', 'category', 'contact'),
        ], 201);
    }
    /**
     * عرض تفاصيل طلب واحد مع مهامه ومواده.
     */
    public function show(Order $order)
    {
        $order->load(['category', 'tasks.role', 'materials']);

        return response()->json($order);
    }

    /**
     * عرض كل الطلبات (مع فلترة اختيارية بالحالة).
     */
    public function index(Request $request)
    {
        $query = Order::with(['category', 'tasks.role']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * التقرير النهائي للطلب: المهام، حالتها، المدة الزمنية، والمواد المستخدمة وتكلفتها.
     */
    public function report(Order $order)
    {
        $order->load(['category', 'tasks.role', 'materials']);

        $tasksReport = $order->tasks->map(function ($task) {
            $durationMinutes = null;

            if ($task->started_at && $task->finished_at) {
                $durationMinutes = $task->started_at->diffInMinutes($task->finished_at);
            }

            return [
                'id'               => $task->id,
                'role'             => $task->role->name,
                'sequence'         => $task->sequence,
                'status'           => $task->status,
                'started_at'       => $task->started_at,
                'finished_at'      => $task->finished_at,
                'duration_minutes' => $durationMinutes,
                'notes'            => $task->notes,
            ];
        });

        $totalMaterialsCost = $order->materials->sum(function ($material) {
            return $material->quantity * $material->unit_cost;
        });

        return response()->json([
            'order' => [
                'id'             => $order->id,
                'category'       => $order->category->name,
                'status'         => $order->status,
                'created_at'     => $order->created_at,
            ],
            'tasks'                 => $tasksReport,
            'materials'             => $order->materials,
            'total_materials_cost'  => round($totalMaterialsCost, 2),
        ]);
    }
}
