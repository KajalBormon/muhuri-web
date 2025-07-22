<?php

namespace App\Services;

use App\Models\Brand;
use App\Services\Core\HelperService;
use App\Services\Core\BaseModelService;
use Illuminate\Support\Facades\DB;

class BrandService extends BaseModelService
{
    public function model(): string
    {
        return Brand::class;
    }

    public function getBrands()
    {
        return $this->model()::orderBy('id','desc')->get();
    }

    public function getBrandById($brandId)
    {
        return $this->model()::select('name')->where('id', $brandId)->first();
    }

    public function changeStatus(Brand $brand, $isActive)
    {
        $isActive = ($isActive == true) ? false : true;
        $brand->is_active = $isActive;
        $brand->save();
        return $brand;
    }

    public function syncData($request)
    {
        try{
            $brands = $request->all();

            if(isset($brands['id'])){
                $brands = [$brands];
            }

            foreach($brands as $brand){
                $brand['central_id'] = $brand['id'];
                $this->model()::updateOrCreate([
                    'central_id' => $brand['id']
                ], $brand);
            }
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }

    public function syncBrandDelete($data)
    {
        try{
            $brand = $this->model()::where('central_id', $data['id'])->first();
            $brand->delete();
            $brand->deleted_by = $data['deleted_by'];
            $brand->save();
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }

    public function deleteBrand(Brand $brand)
    {
        return DB::transaction(function () use ($brand) {
            $brand->delete();
            $brand->deleted_by = auth()->user()->id;
            $brand->save();
            return true;
        });
    }
}
