<?php

namespace App\Http\Controllers\HR;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\DepartureReason\CreateDepartureReasonRequest;
use App\Http\Requests\HR\DepartureReason\UpdateDepartureReasonRequest;
use App\Models\HR\DepartureReason;
use App\Services\HR\DepartureReasonService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Redirect;

class DepartureReasonController extends Controller implements HasMiddleware
{
    protected DepartureReasonService $departureReasonService;

    public function __construct(DepartureReasonService $departureReasonService)
    {
        $this->departureReasonService = $departureReasonService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-create-departure-reason', only: ['store']),
            new Middleware('permission:can-edit-departure-reason', only: ['update', 'changeStatus']),
            new Middleware('permission:can-delete-departure-reason', only: ['destroy']),
            new Middleware('permission:can-view-departure-reason', only: ['index']),
        ];
    }

    public function index()
    {
        $breadcrumbs = Breadcrumbs::generate('departureReasons');
        $departureReasons = $this->departureReasonService->getDepartureReasons();
        $responseData = [
            'departureReasons' => $departureReasons,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.departureReason.index'),
        ];
        return Inertia::render('DepartureReason/Index', $responseData);
    }

    public function store(CreateDepartureReasonRequest $request)
    {
        $validatedData = $request->validated();
        $departureReason = $this->departureReasonService->createDepartureReason($validatedData);
        $status = $departureReason ? Constants::SUCCESS : Constants::ERROR;
        $message = $departureReason ? __('message.custom.departureReason.store.success') : __('message.custom.departureReason.store.error');
        return Redirect::route('departure-reasons.index')->with($status, $message);
    }

    public function update(UpdateDepartureReasonRequest $request, DepartureReason $departureReason)
    {
        $validatedData = $request->validated();
        $isUpdated = $this->departureReasonService->update($departureReason, $validatedData);
        $status = $isUpdated ? Constants::SUCCESS : Constants::ERROR;
        $message = $isUpdated ? __('message.custom.departureReason.update.success') : __('message.custom.departureReason.update.error');
        return Redirect::route('departure-reasons.index')->with($status, $message);
    }

    public function destroy(DepartureReason $departureReason)
    {
        $isDeleted = $this->departureReasonService->deleteDepartureReason($departureReason);
        $status = $isDeleted ? Constants::SUCCESS : Constants::ERROR;
        $message = $isDeleted ? __('message.custom.departureReason.destroy.success') : __('message.custom.departureReason.destroy.error');
        return Redirect::route('departure-reasons.index')->with($status, $message);
    }

    public function changeStatus(Request $request, DepartureReason $departureReason)
    {
        $departureReason = $this->departureReasonService->changeStatus($departureReason, $request->is_active);
        $status = $departureReason ? Constants::SUCCESS : Constants::ERROR;
        $message = $departureReason ? ($departureReason->is_active ? __('message.custom.departureReason.changeStatus.activate') : __('message.custom.departureReason.changeStatus.deactivate')) : __('message.custom.departureReason.changeStatus.error');
        return Redirect::route('departure-reasons.index')->with($status, $message);
    }
}
