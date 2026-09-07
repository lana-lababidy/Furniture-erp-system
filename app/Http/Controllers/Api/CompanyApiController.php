<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyApiController extends Controller
{
    /**
     * GET /api/companies
     * جلب الشركات مع الترقيم والبحث
     */
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $companies = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($companies);
    }

    /**
     * POST /api/companies
     * إضافة شركة جديدة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $company = Company::create([
            'name'    => $request->name,
            'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'تم إنشاء الشركة بنجاح',
            'company' => $company,
        ], 201);
    }

    /**
     * GET /api/companies/{id}/contacts
     * جلب جهات الاتصال التابعة لشركة معينة
     */
    public function contacts(string $id)
    {
        $company = Company::findOrFail($id);

        $contacts = $company->contacts()
            ->latest()
            ->paginate(request()->get('per_page', 15));

        return response()->json($contacts);
    }
}
