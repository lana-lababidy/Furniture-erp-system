<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\HrApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\TaskApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات عامة (بدون تسجيل دخول)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| مسارات محمية (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |----------------------------------------------------------------------
    | الشركات وجهات الاتصال (Companies & Contacts)
    |----------------------------------------------------------------------
    */
    Route::get('/companies', [CompanyApiController::class, 'index']);
    Route::post('/companies', [CompanyApiController::class, 'store']);
    Route::get('/companies/{id}/contacts', [CompanyApiController::class, 'contacts']);

    Route::get('/contacts', [ContactApiController::class, 'index']);
    Route::post('/contacts', [ContactApiController::class, 'store']);
    Route::get('/contacts/{contact}', [ContactApiController::class, 'show']);

    /*
    |----------------------------------------------------------------------
    | المشاريع (Projects) — نظام الاستبيانات
    | مسموح لمدير المشروع والأدمن
    |----------------------------------------------------------------------
    */
    Route::middleware('role:project_manager,admin')->group(function () {
        Route::post('/projects', [ProjectApiController::class, 'store']);
    });

    // عرض المشاريع متاح لأي مستخدم مسجل دخول
    Route::get('/projects', [ProjectApiController::class, 'index']);
    Route::get('/projects/{project}', [ProjectApiController::class, 'show']);

    /*
    |----------------------------------------------------------------------
    | المهام (Tasks) — الفريق الميداني والمراقبة وإدخال البيانات
    |----------------------------------------------------------------------
    */
    Route::middleware('role:project_manager,field_team,qc,data_entry,admin')->group(function () {
        Route::get('/tasks', [TaskApiController::class, 'index']);
        Route::patch('/tasks/{task}/start', [TaskApiController::class, 'start']);
        Route::patch('/tasks/{task}/complete', [TaskApiController::class, 'complete']);
    });

    /*
    |----------------------------------------------------------------------
    | الموارد البشرية (HR Module)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:hr,admin')
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