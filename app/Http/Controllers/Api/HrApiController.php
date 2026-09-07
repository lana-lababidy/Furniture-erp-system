<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Loan;
use App\Models\Leave;
use App\Models\PayrollAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HrApiController extends Controller
{
    /*
    |--------------------------------------------------------------------
    | 1. الموظفون والعقود
    |--------------------------------------------------------------------
    */

    /**
     * POST /api/hr/employees
     * إضافة موظف جديد + عقده الأول في نفس العملية.
     */
    public function storeEmployee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => ['required', 'string', 'max:255'],
            'phone'                 => ['required', 'string', 'max:50', 'unique:users,phone'],
            'password'              => ['required', 'string', 'min:6'],
            'role_id'               => ['required', 'exists:roles,id'],
            'contract.base_salary'  => ['required', 'numeric', 'min:0'],
            'contract.start_date'   => ['required', 'date'],
            'contract.end_date'     => ['nullable', 'date', 'after:contract.start_date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $employee = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'phone'    => $data['phone'],
                'password' => Hash::make($data['password']),
                'role_id'  => $data['role_id'],
            ]);

            $user->contracts()->create([
                'base_salary' => $data['contract']['base_salary'],
                'start_date'  => $data['contract']['start_date'],
                'end_date'    => $data['contract']['end_date'] ?? null,
                'status'      => 'active',
            ]);

            return $user;
        });

        return response()->json([
            'message'  => 'تم إضافة الموظف والعقد بنجاح',
            'employee' => $employee->load('contract', 'role'),
        ], 201);
    }

    /**
     * GET /api/hr/contracts/expiring
     * العقود التي تقترب من الانتهاء خلال عدد أيام معيّن (افتراضي 30).
     */
    public function expiringContracts(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);

        $contracts = Contract::query()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->with('user:id,name,phone')
            ->orderBy('end_date')
            ->get();

        return response()->json([
            'threshold_days' => $days,
            'count'          => $contracts->count(),
            'contracts'      => $contracts,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 2. الرواتب: مكافآت / خصومات / حساب صافي الراتب
    |--------------------------------------------------------------------
    */

    /**
     * POST /api/hr/payroll-adjustments
     * إضافة مكافأة أو خصم لموظف معيّن.
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'type'    => ['required', 'in:bonus,deduction'],
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'reason'  => ['nullable', 'string', 'max:500'],
            'date'    => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $adjustment = PayrollAdjustment::create($validator->validated());

        return response()->json([
            'message'    => 'تمت إضافة التعديل المالي بنجاح',
            'adjustment' => $adjustment->load('user:id,name'),
        ], 201);
    }

    /**
     * GET /api/hr/payroll/{user}/calculate?year=2026&month=8
     * حساب صافي راتب موظف عن شهر محدد:
     * الراتب الأساسي + المكافآت - الخصومات - قسط السلفة الشهري.
     */
    public function calculateMonthlySalary(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'year'  => ['required', 'integer', 'digits:4'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $year  = (int) $request->query('year');
        $month = (int) $request->query('month');

        $contract = $user->contracts()
            ->where('status', 'active')
            ->latest('start_date')
            ->first();

        if (!$contract) {
            return response()->json([
                'message' => 'لا يوجد عقد نشط لهذا الموظف',
            ], 422);
        }

        $baseSalary = (float) $contract->base_salary;

        $bonuses = (float) $user->payrollAdjustments()
            ->bonuses()
            ->forMonth($year, $month)
            ->sum('amount');

        $deductions = (float) $user->payrollAdjustments()
            ->deductions()
            ->forMonth($year, $month)
            ->sum('amount');

        $activeLoans = $user->loans()->active()->get();

        $loanInstallmentsTotal = $activeLoans->sum(fn(Loan $loan) => $loan->effectiveInstallment());

        $netSalary = $baseSalary + $bonuses - $deductions - $loanInstallmentsTotal;

        return response()->json([
            'user' => [
                'id'   => $user->id,
                'name' => $user->name,
            ],
            'period' => [
                'year'  => $year,
                'month' => $month,
            ],
            'base_salary'             => round($baseSalary, 2),
            'total_bonuses'           => round($bonuses, 2),
            'total_deductions'        => round($deductions, 2),
            'total_loan_installments' => round($loanInstallmentsTotal, 2),
            'net_salary'              => round($netSalary, 2),
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 3. السُلف المالية
    |--------------------------------------------------------------------
    */

    /**
     * POST /api/hr/loans
     * طلب سلفة جديدة لموظف.
     */
    public function storeLoan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => ['required', 'exists:users,id'],
            'total_amount'         => ['required', 'numeric', 'min:1'],
            'monthly_installment'  => ['required', 'numeric', 'min:1', 'lte:total_amount'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $loan = Loan::create([
            'user_id'             => $data['user_id'],
            'total_amount'        => $data['total_amount'],
            'monthly_installment' => $data['monthly_installment'],
            'remaining_amount'    => $data['total_amount'],
            'status'              => 'active',
        ]);

        return response()->json([
            'message' => 'تم تسجيل طلب السلفة بنجاح',
            'loan'    => $loan->load('user:id,name'),
        ], 201);
    }

    /**
     * PATCH /api/hr/loans/{loan}/deduct-installment
     * اقتطاع قسط شهري من سلفة معيّنة يدوياً (أو تُستدعى تلقائياً عند تشغيل مسير الرواتب).
     */
    public function deductLoanInstallment(Loan $loan): JsonResponse
    {
        if ($loan->status !== 'active') {
            return response()->json([
                'message' => 'هذه السلفة غير نشطة، لا يمكن اقتطاع قسط منها',
            ], 422);
        }

        $installment = $loan->effectiveInstallment();

        $loan->remaining_amount -= $installment;

        if ($loan->remaining_amount <= 0) {
            $loan->remaining_amount = 0;
            $loan->status = 'completed';
        }

        $loan->save();

        return response()->json([
            'message' => 'تم اقتطاع القسط بنجاح',
            'loan'    => $loan,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 4. الإجازات
    |--------------------------------------------------------------------
    */

    /**
     * POST /api/hr/leaves
     * تقديم طلب إجازة لموظف.
     */
    public function storeLeave(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => ['required', 'exists:users,id'],
            'type'       => ['required', 'in:sick,annual,unpaid'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $leave = Leave::create([
            ...$validator->validated(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم تقديم طلب الإجازة بنجاح',
            'leave'   => $leave->load('user:id,name'),
        ], 201);
    }

    /**
     * PATCH /api/hr/leaves/{leave}/status
     * قبول أو رفض طلب إجازة من قبل الـ HR/الأدمن.
     */
    public function updateLeaveStatus(Request $request, Leave $leave): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:approved,rejected'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'تم البت في هذا الطلب مسبقاً',
            ], 422);
        }

        $leave->update([
            'status'      => $request->input('status'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة الإجازة بنجاح',
            'leave'   => $leave->load(['user:id,name', 'reviewer:id,name']),
        ]);
    }

    /**
     * GET /api/hr/leaves?status=pending&user_id=3
     * عرض طلبات الإجازات مع فلترة اختيارية.
     */
    public function indexLeaves(Request $request): JsonResponse
    {
        $query = Leave::with(['user:id,name', 'reviewer:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        return response()->json($query->latest()->paginate(20));
    }
}
