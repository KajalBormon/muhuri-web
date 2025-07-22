<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\UpdateOtherSaleItemRequest;
use App\Http\Requests\Sale\UpdateSaleItemRequest;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleItem;
use App\Services\Core\HelperService;
use App\Services\Sale\SaleItemService;
use App\Services\Sale\SaleService;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    private SaleItemService $saleItemService;
    private SaleService $saleService;

    public function __construct(SaleItemService $saleItemService, SaleService $saleService)
    {
        $this->saleItemService = $saleItemService;
        $this->saleService = $saleService;
    }

    public function index(Sale $sale)
    {
        return response()->json($sale->saleItems);
    }

    public function update(UpdateSaleItemRequest $request, Sale $sale, SaleItem $saleItem)
    {
        HelperService::checkSaleItemAccess($sale, $saleItem);
        $validatedData = $request->validated();
        $updated = $this->saleItemService->updateSaleItem($sale, $saleItem, $validatedData);
        if ($updated) {
            return response()->json($sale->saleItems);
        }
        return response()->json(['error' => 'Sale Item could not be updated'], 400);
    }

    public function updateOtherSaleItem(UpdateOtherSaleItemRequest $request, Sale $sale, SaleItem $saleItem)
    {
        HelperService::checkSaleItemAccess($sale, $saleItem);
        $validatedData = $request->validated();
        $updated = $this->saleItemService->updateSaleItem($sale, $saleItem, $validatedData);
        if ($updated) {
            return response()->json($sale->saleItems);
        }
        return response()->json(['error' => 'Sale Item could not be updated'], 400);
    }

    public function destroy(Sale $sale, SaleItem $saleItem)
    {
        $isFullSaleDeleted = $this->saleService->deleteSaleItem($sale, $saleItem);
        if ($isFullSaleDeleted) {
            return redirect()->route('sales.index')->with('success', __('message.custom.sale.destroy.success'));
        }
        return redirect()->back()->with('success', __('message.custom.saleItem.destroy.success'));
    }
}
