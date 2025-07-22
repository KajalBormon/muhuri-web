<?php

namespace App\Http\Controllers\Supplier;

use App\Services\ConfigurationService;
use Inertia\Inertia;
use App\Models\Supplier\Supplier;
use App\Services\SMS\SMSService;
use App\Services\LocationService;
use App\Models\Supplier\SupplierPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Core\HelperService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use App\Services\Supplier\SupplierPaymentService;
use App\Services\Supplier\SupplierSummaryService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Requests\Supplier\CreateSupplierPaymentRequest;
use App\Http\Requests\Supplier\UpdateSupplierPaymentRequest;

class SupplierPaymentController extends Controller implements HasMiddleware
{
    protected SupplierPaymentService $supplierPaymentService;
    protected LocationService $locationService;
    protected SupplierSummaryService $supplierSummaryService;
    protected SMSService $smsService;
    protected ConfigurationService $configurationService;

    public function __construct(SupplierPaymentService $supplierPaymentService, LocationService $locationService, SupplierSummaryService $supplierSummaryService, SMSService $smsService, ConfigurationService $configurationService)
    {
        $this->supplierPaymentService = $supplierPaymentService;
        $this->locationService = $locationService;
        $this->supplierSummaryService = $supplierSummaryService;
        $this->smsService = $smsService;
        $this->configurationService = $configurationService;
    }

    public static function middleware(): array
    {
        return [
            new middleware('permission:can-view-supplier-payment', only: ['show']),
            new middleware('permission:can-create-supplier-payment', only: ['store']),
            new middleware('permission:can-edit-supplier-payment', only: ['update']),
            new middleware('permission:can-delete-supplier-payment', only: ['destroy'])
        ];
    }

    public function store(CreateSupplierPaymentRequest $request, Supplier $supplier)
    {
        $validatedData = $request->validated();
        $supplierPayment = $this->supplierPaymentService->createPayment($supplier, $validatedData);
        $status = $supplierPayment ? 'success' : 'error';
        $message = $supplierPayment ? __('message.custom.payment.store.success') : __('message.custom.payment.store.error');

        return Inertia::location(route('suppliers.payments.invoice', [
            'supplier' => $supplier->id,
            'payment' => $supplierPayment->id,
        ]));
    }

    public function update(UpdateSupplierPaymentRequest $request, Supplier $supplier, SupplierPayment $payment)
    {
        if (!$payment->is_editable) {
            return redirect()->back()->with('error', __('message.custom.payment.destroy.invalid'));
        }
        $validatedData = $request->validated();
        $supplierPaymentAccess = $this->supplierPaymentService->checkSupplierPaymentAccess($supplier, $payment);
        if($supplierPaymentAccess){
            $isUpdated = $this->supplierPaymentService->updatePayment($payment, $validatedData);
            $status = $isUpdated ? 'success' : 'error';
            $message = $isUpdated ? __('message.custom.payment.update.success') : __('message.custom.payment.update.error');
            return Inertia::location(route('suppliers.payments.invoice', [
                'supplier' => $supplier->id,
                'payment' => $payment->id,
            ]));
        }
        return redirect()->route('suppliers.show', $payment->supplier->id)->with('error','Supplier have no access to update payment');
    }

    public function show(Supplier $supplier, SupplierPayment $payment)
    {
        $breadcrumbs = Breadcrumbs::generate('supplierPaymentInvoice', $supplier, $payment);
        $address = HelperService::getAddress($supplier);
        $supplierSummary = $this->supplierSummaryService->getSupplierSummary($supplier);
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $responseData = [
            'supplier' => $supplier,
            'payment' => $payment,
            'address' => $address,
            'supplierSummary' => $supplierSummary,
            'configuration' => $configuration,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.supplier.paymentDetails')
        ];

        return Inertia::render('Supplier/Payment/Show', $responseData);
    }

    public function destroy(Supplier $supplier, SupplierPayment $payment)
    {
        if (!$payment->is_editable) {
            return redirect()->back()->with('error', __('message.custom.payment.destroy.invalid'));
        }
        $supplierPaymentAccess = $this->supplierPaymentService->checkSupplierPaymentAccess($supplier, $payment);
        if($supplierPaymentAccess){
            $isDeleted = $this->supplierPaymentService->deletePayment($payment);
            $status = $isDeleted ? 'success' : 'error';
            $message = $isDeleted ? __('message.custom.payment.destroy.success') : __('message.custom.payment.destroy.error');
            return redirect()->route('suppliers.show',$payment->supplier->id)->with($status, $message);
        }
        return redirect()->route('suppliers.show', $payment->supplier->id)->with('error','Supplier have no access to update payment');
    }
}
