<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Permission\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function syncTenantPermissions(Request $request)
    {
        $this->permissionService->syncData($request);
        return response()->json(['message' => 'Permissions synced successfully.']);
    }

    public function syncTenantPermissionDelete(Request $request)
    {
        $permission = $request->json()->all();
        $this->permissionService->syncPermissionDelete($permission);
        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
