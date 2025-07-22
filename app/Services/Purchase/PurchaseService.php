<?php

namespace App\Services\Purchase;

use App\Models\Purchase\PurchaseItem;
use App\Models\Supplier\Supplier;
use App\Models\Purchase\Purchase;
use App\Services\Supplier\SupplierPaymentService;
use App\Services\Supplier\SupplierSummaryService;
use App\Services\SummaryService;
use Illuminate\Support\Facades\DB;
use App\Services\Core\BaseModelService;
use App\Services\Core\HelperService;
use App\Services\FiscalYear\FiscalYearService;
use Carbon\Carbon;

class PurchaseService extends BaseModelService
{
    protected FiscalYearService $fiscalYearService;
    protected PurchaseItemService $purchaseItemService;
    protected PurchaseSummaryService $purchaseSummeryService;
    protected SupplierSummaryService $supplierSummaryService;
    protected SupplierPaymentService $supplierPaymentService;
    protected SummaryService $summaryService;

    public function __construct(FiscalYearService $fiscalYearService, PurchaseItemService $purchaseItemService, PurchaseSummaryService $purchaseSummaryService, SupplierSummaryService $supplierSummaryService, SupplierPaymentService $supplierPaymentService, SummaryService $summaryService)
    {
        $this->fiscalYearService = $fiscalYearService;
        $this->purchaseItemService = $purchaseItemService;
        $this->purchaseSummeryService = $purchaseSummaryService;
        $this->supplierSummaryService = $supplierSummaryService;
        $this->supplierPaymentService = $supplierPaymentService;
        $this->summaryService = $summaryService;
    }

    public function model(): string
    {
        return Purchase::class;
    }

    public function addPurchaseToSummaries(Purchase $purchase)
    {
        $this->supplierSummaryService->increaseTotalTransaction($purchase->supplier, $purchase);
        $this->summaryService->increaseTotalPurchase($purchase);

        return $purchase;
    }

    public function removePurchaseFromSummaries(Purchase $purchase)
    {
        $this->supplierSummaryService->decreaseTotalTransaction($purchase->supplier, $purchase);
        $this->summaryService->decreaseTotalPurchase($purchase);

        return $purchase;
    }

    public function removePurchaseItemFromSummaries(PurchaseItem $purchaseItem)
    {
        $this->supplierSummaryService->decreaseTotalTransactionForPurchaseItem($purchaseItem->purchase->supplier, $purchaseItem);
        $this->summaryService->decreaseTotalPurchaseForItem($purchaseItem);
    }

    public function createPurchase(array $data)
    {
        $fiscalYear = $this->fiscalYearService->getFiscalYearByDate($data['purchase_date']);
        $invoiceNumber = HelperService::generateInvoiceNumber('P-', $this->model());
        $subTotal = round(array_sum(array_map(fn($item) => floatval($item['total_price']), $data['items'])));
        $discount = isset($data['discount']) ? floatval($data['discount']) : 0;
        $paidAmount = isset($data['paid_amount']) ? floatval($data['paid_amount']) : 0;
        $grandTotal = max(0, $subTotal - $discount);
        $purchaseType = isset($data['purchase_type']) ? $data['purchase_type'] : 'other';
        $purchaseNote = isset($data['purchase_note']) ? $data['purchase_note'] : '';

        $purchaseData = [
            'supplier_id' => $data['supplier_id'],
            'invoice_number' => $invoiceNumber,
            'purchase_date' => $data['purchase_date'],
            'sub_total' => $subTotal,
            'discount' => $discount,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'fiscal_year' => $fiscalYear->fiscal_year,
            'fiscal_year_id' => $fiscalYear->id,
            'purchase_type' => $purchaseType,
            'note' => $purchaseNote
        ];

        return DB::transaction(function () use ($purchaseData, $data) {
            $purchase = $this->create($purchaseData);
            $this->addPurchaseToSummaries($purchase);

            $purchaseItems = $this->purchaseItemService->createPurchaseItems($data['items'], $purchase->id);
            foreach($purchaseItems as $purchaseItem){
                $this->purchaseSummeryService->addPurchaseItemToSummary($purchase, $purchaseItem);
            }

            if($purchase->paid_amount > 0) {
                $paymentData = [
                    'payment_date' => $purchase->purchase_date,
                    'amount' => $purchase->paid_amount,
                    'payment_method' => 'cash',
                    'purchase_id' => $purchase->id,
                    'is_editable' => false
                ];
                $this->supplierPaymentService->createPayment($purchase->supplier, $paymentData);
            }

            return $purchase;
        });
    }

    public function getPurchases(Supplier $supplier = null, $year = null)
    {
        if(!$year){
            $fiscalYear = $this->fiscalYearService->getCurrentFiscalYear();
            $year = $fiscalYear->fiscal_year;
        }

        $purchases = $this->model()::where('fiscal_year', $year)
            ->when($supplier, function ($query) use ($supplier){
                return $query->where('supplier_id', $supplier->id);
            })->orderBy('purchase_date', 'desc')->get();

        return $purchases;
    }

    public function updatePurchase($purchase, array $data)
    {
        return DB::transaction(function () use ($purchase, $data) {
            $this->removePurchaseFromSummaries($purchase);
            $this->decreasePurchaseGrantTotal($purchase);

            $purchaseNote = isset($data['purchase_note']) ? $data['purchase_note'] : '';

            $purchase->purchase_date = $data['purchase_date'];
            $purchase->discount = $data['discount'] ?? 0;
            $purchase->paid_amount = $data['paid_amount'] ?? 0;
            $purchase->note = $purchaseNote;
            $purchase->save();

            $this->increasePurchaseGrandTotal($purchase);
            $this->addPurchaseToSummaries($purchase);

            return $purchase;
        });
    }

    public function increasePurchaseGrandTotal($purchase)
    {
        $purchase->sub_total += $purchase->purchaseItems->sum('total_price');
        $purchase->grand_total = $purchase->sub_total - $purchase->discount;
        $purchase->save();
    }

    public function decreasePurchaseGrantTotal($purchase)
    {
        $purchase->sub_total -= $purchase->purchaseItems->sum('total_price');
        $purchase->grand_total = $purchase->sub_total - $purchase->discount;
        $purchase->save();
    }

    public function decreasePurchaseGrantTotalForItem($purchase, $purchaseItem)
    {
        $purchase->sub_total -= $purchaseItem->total_price;
        $purchase->grand_total = $purchase->sub_total - $purchase->discount;
        $purchase->save();
    }

    public function deletePurchase(Purchase $purchase)
    {
        $result = DB::transaction(function () use ($purchase) {
            $this->removePurchaseFromSummaries($purchase);
            $supplierPayment = $purchase->supplierPayment;
            if($supplierPayment){
                $this->supplierPaymentService->deletePayment($purchase->supplierPayment);
            }
            foreach ($purchase->purchaseItems as $purchaseItem) {
                $this->purchaseSummeryService->removePurchaseItemFromSummary($purchase, $purchaseItem);
            }
            $this->purchaseItemService->getPurchaseItems($purchase)->each(function ($purchaseItem) {
                $purchaseItem->deleted_by = auth()->user()->id;
                $purchaseItem->save();
                $purchaseItem->delete();
            });
            $purchase->deleted_by = auth()->user()->id;
            $purchase->save();
            return $purchase->delete();
        });
        return $result;
    }

    public function deletePurchaseItem(Purchase $purchase, PurchaseItem $purchaseItem)
    {
        $result = DB::transaction(function () use ($purchase, $purchaseItem) {
            if($purchase->purchaseItems()->count() > 1){
                $this->removePurchaseItemFromSummaries($purchaseItem);
                $this->purchaseSummeryService->removePurchaseItemFromSummary($purchase, $purchaseItem);
                $this->decreasePurchaseGrantTotalForItem($purchase, $purchaseItem);
                $purchaseItem->deleted_by = auth()->id();
                $purchaseItem->save();
                $purchaseItem->delete();
                return false;
            }
            $this->deletePurchase($purchase);
            return true;
        });
        return $result;
    }

    public function getGrandTotalById($supplierId)
    {
        return $this->model()::where('supplier_id', $supplierId)->sum('grand_total');
    }

    public function getPurchaseById($purchaseId)
    {
        return $this->model()::with('purchaseItems')->where('id', $purchaseId)->first();
    }

    public function getPurchasesDateAndTypeWise($request)
    {
        $from = $request->from_date ?? Carbon::today()->toDateString();
        $to = $request->to_date ?? Carbon::today()->toDateString();
        $purchaseType = $request->purchase_type;

        $query = $this->model()::whereBetween('purchase_date', [$from, $to]);

        if ($purchaseType) {
            $query->where('purchase_type', $purchaseType);
        }

        $purchases = $query->with('supplier')->get();

        return [
            'purchases' => $purchases,
            'total_amount' => $purchases->sum('grand_total')
        ];
    }

    public function getPurchaseTypesWithName()
    {
        return [
            [ 'value' => 'larvae', 'text' => 'পোনা' ],
            [ 'value' => 'other', 'text' => 'অন্যান্য' ]
        ];
    }
}
