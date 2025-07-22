<?php

namespace App\Services\Permission;

use App\Services\Core\BaseModelService;
use App\Services\Core\HelperService;
use Spatie\Permission\Models\Permission;

class PermissionService extends BaseModelService
{
    public function model(): string
    {
        return Permission::class;
    }

    public function getPermissions()
    {
        $permissions = $this->model()::get()->groupBy('group_name');
        return $permissions;
    }

    public function syncData($request)
    {
        try{
            $permissions = $request->all();

            if(isset($permissions['id'])){
                $permissions = [$permissions];
            }

            foreach($permissions as $permission){
                $permission['central_id'] = $permission['id'];
                $this->model()::updateOrCreate([
                    'central_id' => $permission['id']
                ], $permission);
            }
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }

    public function syncPermissionDelete($data)
    {
        try{
            $permission = $this->model()::where('central_id', $data['id'])->first();
            $permission->name = $permission->name.'-'.'deleted'.'-'.$permission->id;
            $permission->deleted_by = $data['deleted_by'];
            $permission->save();
            $permission->delete();
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }
}
