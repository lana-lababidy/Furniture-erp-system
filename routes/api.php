<?php
use App\Http\Controllers\HrApiController;
use App\Http\Controllers\ContactApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderApiController;
use App\Http\Controllers\TaskApiController;



Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |----------------------------------------------------------------------
    | الطلبات (Orders)
    | مسموح فقط لموظف الاستقبال ومدير المصنع 
    |----------------------------------------------------------------------
    */
    Route::middleware('role:receptionist,manager')->group(function () {
        Route::post('/orders', [OrderApiController::class, 'store']);
    });
    Route::get('/contacts', [ContactApiController::class, 'index']);
    Route::post('/contacts', [ContactApiController::class, 'store']);
    Route::get('/contacts/{contact}', [ContactApiController::class, 'show']);

    // عرض الطلبات والتقرير - متاح لأي مستخدم مسجل دخول 
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);
    Route::get('/orders/{order}/report', [OrderApiController::class, 'report']);

    /*
    |----------------------------------------------------------------------
    */
    Route::middleware('role:carpenter,painter,upholsterer,welder,warehouse,designer,installer')->group(function () {
        Route::get('/tasks', [TaskApiController::class, 'index']);
        Route::patch('/tasks/{task}/start', [TaskApiController::class, 'start']);
        Route::patch('/tasks/{task}/complete', [TaskApiController::class, 'complete']);
    });


    Route::middleware(['auth:sanctum', 'role:hr,admin'])
        ->prefix('hr')
        ->group(function () {

            // الموظفون والعقود
            Route::post('/employees', [HrApiController::class, 'storeEmployee']);
            Route::get('/contracts/expiring', [HrApiController::class, 'expiringContracts']);

            // الرواتب
            Route::post('/payroll-adjustments', [HrApiController::class, 'storeAdjustment']);
            Route::get('/payroll/{user}/calculate', [HrApiController::class, 'calculateMonthlySalary']);

            // السُلف
            Route::post('/loans', [HrApiController::class, 'storeLoan']);
            Route::patch('/loans/{loan}/deduct-installment', [HrApiController::class, 'deductLoanInstallment']);

            // الإجازات
            Route::get('/leaves', [HrApiController::class, 'indexLeaves']);
            Route::post('/leaves', [HrApiController::class, 'storeLeave']);
            Route::patch('/leaves/{leave}/status', [HrApiController::class, 'updateLeaveStatus']);
        });
});
