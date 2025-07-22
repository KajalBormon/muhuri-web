<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\Buyer\BuyerPaymentService;
use App\Services\Buyer\BuyerService;
use App\Services\ConfigurationService;
use App\Services\Purchase\PurchaseService;
use App\Services\Expense\ExpenseService;
use App\Services\Sale\SaleItemService;
use App\Services\Sale\SaleService;
use App\Services\SummaryService;
use App\Services\Supplier\SupplierPaymentService;
use App\Services\Supplier\SupplierService;
use Carbon\Carbon;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class ReportController extends Controller implements HasMiddleware
{
    private SummaryService $summaryService;
    private BuyerService $buyerService;
    private BuyerPaymentService $buyerPaymentService;
    private SupplierService $supplierService;
    private SupplierPaymentService $supplierPaymentService;
    private SaleService $saleService;
    private ExpenseService $expenseService;
    private PurchaseService $purchaseService;
    private ConfigurationService $configurationService;
    private BrandService $brandService;
    private SaleItemService $saleItemService;

    public function __construct(
        SummaryService $summaryService,
        BuyerService $buyerService,
        BuyerPaymentService $buyerPaymentService,
        SupplierService $supplierService,
        SupplierPaymentService $supplierPaymentService,
        SaleService $saleService,
        ExpenseService $expenseService,
        PurchaseService $purchaseService,
        ConfigurationService $configurationService,
        BrandService $brandService,
        SaleItemService $saleItemService)
    {
        $this->summaryService = $summaryService;
        $this->buyerService = $buyerService;
        $this->buyerPaymentService = $buyerPaymentService;
        $this->supplierService = $supplierService;
        $this->supplierPaymentService = $supplierPaymentService;
        $this->saleService = $saleService;
        $this->expenseService = $expenseService;
        $this->purchaseService = $purchaseService;
        $this->configurationService = $configurationService;
        $this->brandService = $brandService;
        $this->saleItemService = $saleItemService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-atAGlance', only: ['getSummariesReport']),
            new Middleware('permission:can-view-buyer-due', only: ['getBuyerDue']),
            new Middleware('permission:can-view-buyer-payment', only: ['getBuyerPayment']),
            new Middleware('permission:can-view-supplier-due', only: ['getSupplierDue']),
            new Middleware('permission:can-view-supplier-payment', only: ['getSupplierPayment']),
            new Middleware('permission:can-view-sale-report', only: ['getSaleReport']),
            new Middleware('permission:can-view-purchase-report', only: ['getPurchaseReport']),
            new Middleware('permission:can-view-expense-report', only: ['getExpenseReport']),
            new Middleware('permission:can-view-sale-by-brand-report', only: ['getSalesByBrandReport']),
        ];
    }

    public function getSummariesReport(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('atAGlance');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $summaries = $this->summaryService->getSummaries($fromDate, $toDate);

        $query = $request->only(['from_date', 'to_date']);

        $responseData = [
            'configuration' => $configuration,
            'summaries'=> $summaries,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.atAGlance')
        ];

        return Inertia::render('Report/AtAGlance', $responseData);
    }

    public function getBuyerDue()
    {
        $breadcrumbs = Breadcrumbs::generate('buyerDue');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $buyerDueData = $this->buyerService->getBuyerDue();
        $buyerDues = $buyerDueData['buyer_due'];
        $totalDue = $buyerDueData['total_due'];

        $responseData = [
            'configuration' => $configuration,
            'buyerDues' => $buyerDues,
            'totalDue' => $totalDue,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.buyerDue')
        ];

        return Inertia::render('Report/BuyerDue', $responseData);
    }

    public function getBuyerPayment(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('buyerPayment');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $fromDate = $request->query('from_date', Carbon::today()->toDateString());
        $toDate = $request->query('to_date', Carbon::today()->toDateString());
        $buyerPayments = $this->buyerPaymentService->getPayments(null, $fromDate, $toDate);
        $totalPayment = collect($buyerPayments)->sum('amount');
        $query = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $responseData = [
            'configuration' => $configuration,
            'buyerPayments' => $buyerPayments,
            'totalPayment' => $totalPayment,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.buyerPayment')
        ];

        return Inertia::render('Report/BuyerPayment', $responseData);
    }

    public function getSupplierDue()
    {
        $breadcrumbs = Breadcrumbs::generate('supplierDue');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $supplierDueData = $this->supplierService->getSupplierDue();
        $supplierDues = $supplierDueData['supplier_due'];
        $totalDue = $supplierDueData['total_due'];

        $responseData = [
            'configuration' => $configuration,
            'supplierDues' => $supplierDues,
            'totalDue' => $totalDue,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.supplierDue')
        ];

        return Inertia::render('Report/SupplierDue', $responseData);
    }

    public function getSupplierPayment(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('supplierPayment');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $fromDate = $request->query('from_date', Carbon::today()->toDateString());
        $toDate = $request->query('to_date', Carbon::today()->toDateString());
        $supplierPayments = $this->supplierPaymentService->getPayments(null, $fromDate, $toDate);
        $totalPayment = collect($supplierPayments)->sum('amount');
        $query = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $responseData = [
            'configuration' => $configuration,
            'supplierPayments' => $supplierPayments,
            'totalPayment' => $totalPayment,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.supplierPayment')
        ];

        return Inertia::render('Report/SupplierPayment', $responseData);
    }

    public function getSaleReport(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('sale');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $salesData = $this->saleService->getSalesDateAndTypeWise($request);
        $sales = $salesData['sales'];
        $totalAmount = $salesData['total_amount'];
        $saleTypes = $this->saleService->getSaleTypesWithName();
        $query = $request->only(['from_date', 'to_date']);

        $responseData = [
            'configuration' => $configuration,
            'sales'=> $sales,
            'totalAmount' => $totalAmount,
            'saleTypes' => $saleTypes,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.sale')
        ];

        return Inertia::render('Report/Sale', $responseData);
    }

    public function getExpenseReport(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('expense');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $expensesData = $this->expenseService->getExpensesDateAndTypeWise($request);
        $expenses = $expensesData['expenses'];
        $totalAmount = $expensesData['total_amount'];
        $query = $request->only(['from_date', 'to_date']);

        $responseData = [
            'configuration' => $configuration,
            'expenses'=> $expenses,
            'totalAmount' => $totalAmount,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.expense')
        ];

        return Inertia::render('Report/Expense', $responseData);
    }

    public function getPurchaseReport(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('purchase');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $purchasesData = $this->purchaseService->getPurchasesDateAndTypeWise($request);
        $purchases = $purchasesData['purchases'];
        $totalAmount = $purchasesData['total_amount'];
        $purchaseTypes = $this->purchaseService->getPurchaseTypesWithName();
        $query = $request->only(['from_date', 'to_date']);

        $responseData = [
            'configuration' => $configuration,
            'purchases'=> $purchases,
            'totalAmount' => $totalAmount,
            'purchaseTypes' => $purchaseTypes,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.purchase')
        ];

        return Inertia::render('Report/Purchase', $responseData);
    }

    public function getSalesByBrandReport(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('saleByBrand');
        $configuration = $this->configurationService->getCompanyPublicConfiguration();
        $salesByBrandData = $this->saleItemService->getSalesByBrand($request);
        $salesByBrand = $salesByBrandData['salesByBrand'];
        $totalAmount = $salesByBrandData['total_amount'];
        $brands = $this->brandService->getBrands();
        $query = $request->only(['from_date', 'to_date']);

        $responseData = [
            'configuration' => $configuration,
            'salesByBrand'=> $salesByBrand,
            'totalAmount' => $totalAmount,
            'brands' => $brands,
            'query' => $query,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => __('pageTitle.custom.report.sale')
        ];
        
        return Inertia::render('Report/SaleByBrand', $responseData);
    }
}
