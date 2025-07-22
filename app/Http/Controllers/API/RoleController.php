<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Permission\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function syncTenantRoles(Request $request)
    {
        $this->roleService->syncData($request);
        return response()->json(['message' => 'Roles synced successfully.']);
    }

    public function syncTenantRoleDelete(Request $request)
    {
        $role = $request->json()->all();
        $this->roleService->syncRoleDelete($role);
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
