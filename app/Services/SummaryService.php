<?php

namespace App\Services;

use App\Models\Summary;
use App\Services\Core\BaseModelService;
use App\Services\FiscalYear\FiscalYearService;

class SummaryService extends BaseModelService
{
    private FiscalYearService $fiscalYearService;

    public function __construct(FiscalYearService $fiscalYearService)
    {
        $this->fiscalYearService = $fiscalYearService;
    }

    public function model(): string
    {
        return Summary::class;
    }

    public function firstOrCreateSummary($date)
    {
        $fiscalYear = $this->fiscalYearService->getFiscalYearByDate($date);

        $data['summary_date'] = $date;
        $data['fiscal_year_id'] = $fiscalYear->id;
        $data['fiscal_year'] = $fiscalYear->fiscal_year;

        return $this->model()::firstOrCreate([
            'summary_date' => $date,
            'fiscal_year_id' => $fiscalYear->id
        ],$data);

    }

    public function getSummaries($fromDate = null, $toDate = null, $year = null, $month = null,  $orderBy = 'summary_date', $direction = 'desc')
    {
        if(!$year){
            $fiscalYear = $this->fiscalYearService->getCurrentFiscalYear();
            $year = $fiscalYear->fiscal_year;
        }

        $query = $this->model()::where('fiscal_year', $year);

        if($month !== null){
            $query->where('month', $month);
        }

        if($fromDate !== null){
            $query->whereDate('summary_date', '>=', $fromDate);
        }

        if($toDate !== null){
            $query->whereDate('summary_date', '<=', $toDate);
        }

        $summaries = $query->orderBy($orderBy, $direction)->orderBy('id', $direction)->get();

        return $summaries;
    }

    public function increaseTotalSale($sale)
    {
        $summary = $this->firstOrCreateSummary($sale->sale_date);
        $summary->total_sale += $sale->grand_total;
        return $summary->save();
    }

    public function decreaseTotalSale($sale)
    {
        $summary = $this->firstOrCreateSummary($sale->sale_date);
        $summary->total_sale -= $sale->grand_total;
        return $summary->save();
    }

    public function decreaseTotalSaleForItem($saleItem)
    {
        $summary = $this->firstOrCreateSummary($saleItem->sale->sale_date);
        $summary->total_sale -= $saleItem->total_price;
        return $summary->save();
    }

    //Total purchase Increase and Decrease
    public function increaseTotalPurchase($purchase)
    {
        $summary = $this->firstOrCreateSummary($purchase->purchase_date);
        $summary->total_purchase += $purchase->grand_total;
        return $summary->save();
    }

    public function decreaseTotalPurchase($purchase)
    {
        $summary = $this->firstOrCreateSummary($purchase->purchase_date);
        $summary->total_purchase -= $purchase->grand_total;
        return $summary->save();
    }

    public function decreaseTotalPurchaseForItem($purchaseItem)
    {
        $summary = $this->firstOrCreateSummary($purchaseItem->purchase->purchase_date);
        $summary->total_sale -= $purchaseItem->total_price;
        return $summary->save();
    }

    public function addBuyerPaymentToSummary($payment)
    {
        $summary = $this->firstOrCreateSummary($payment->payment_date);
        $summary->total_buyer_payment += $payment->amount;
        return $summary->save();
    }

    public function removeBuyerPaymentFromSummary($payment)
    {
        $summary = $this->firstOrCreateSummary($payment->payment_date);
        $summary->total_buyer_payment -= $payment->amount;
        return $summary->save();
    }

    public function addSupplierPaymentToSummary($payment)
    {
        $summary = $this->firstOrCreateSummary($payment->payment_date);
        $summary->total_supplier_payment += $payment->amount;
        return $summary->save();
    }

    public function removeSupplierPaymentFromSummary($payment)
    {
        $summary = $this->firstOrCreateSummary($payment->payment_date);
        $summary->total_supplier_payment -= $payment->amount;
        return $summary->save();
    }

    public function addExpenseToSummary($expense)
    {
        $summary = $this->firstOrCreateSummary($expense->expense_date);
        $summary->total_expense += $expense->amount;
        return $summary->save();
    }

    public function removeExpenseFromSummary($expense)
    {
        $summary = $this->firstOrCreateSummary($expense->expense_date);
        $summary->total_expense -= $expense->amount;
        return $summary->save();
    }
}
