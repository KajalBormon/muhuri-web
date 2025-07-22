<?php

namespace App\Services\HR;

use Illuminate\Support\Str;
use App\Models\HR\DepartureReason;
use Illuminate\Support\Facades\DB;
use App\Services\Core\HelperService;
use App\Services\Core\BaseModelService;

class DepartureReasonService extends BaseModelService
{
    public function model(): string
    {
        return DepartureReason::class;
    }

    public function getDepartureReasons()
    {
        return $this->model()::all();
    }

    public function createDepartureReason($validatedData)
    {
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $departureReason = $this->create($validatedData);
        return $departureReason;
    }

    public function changeStatus(DepartureReason $departureReason, $isActive)
    {
        $isActive = ($isActive == true) ? false : true;
        $departureReason->is_active = $isActive;
        $departureReason->save();
        return $departureReason;
    }

    public function deleteDepartureReason(DepartureReason $departureReason)
    {
        return DB::transaction(function () use ($departureReason) {
            $departureReason->name = $departureReason->name . '-deleted' .'-' . $departureReason->id;
            $departureReason->slug = $departureReason->slug . '-deleted' .'-' . $departureReason->id;
            $departureReason->deleted_by = auth()->user()->id;
            $departureReason->save();
            $departureReason->delete();
            return true;
        });
    }

    public function syncData($request)
    {
        try{
            $departureReasons = $request->all();

            if(isset($departureReasons['id'])){
                $departureReasons = [$departureReasons];
            }

            foreach($departureReasons as $departureReason){
                $departureReason['central_id'] = $departureReason['id'];
                $this->model()::updateOrCreate([
                    'central_id' => $departureReason['id']
                ], $departureReason);
            }
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }

    public function departureReasonDelete($data)
    {
        try{
            $departureReason = $this->model()::where('central_id', $data['id'])->first();
            $departureReason->delete();
        }catch(\Exception $e){
            HelperService::captureException($e);
        }
    }
}
