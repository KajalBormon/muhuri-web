<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\UpdateOtherPurchaseItemRequest;
use App\Http\Requests\Purchase\UpdatePurchaseItemRequest;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseItem;
use App\Services\Core\HelperService;
use App\Services\Purchase\PurchaseItemService;
use App\Services\Purchase\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PurchaseItemController extends Controller implements HasMiddleware
{
    protected PurchaseItemService $purchaseItemService;
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseItemService $purchaseItemService, PurchaseService $purchaseService)
    {
        $this->purchaseItemService = $purchaseItemService;
        $this->purchaseService = $purchaseService;
    }

    public static function middleware(): array
    {
        return[
            new middleware('permission:can-edit-purchase-item', only: ['update']),
            new middleware('permission:can-edit-other-purchase-item', only: ['updateOtherPurchaseItem']),
        ];
    }

    public function index(Purchase $purchase)
    {
        return response()->json($purchase->purchaseItems);
    }

    public function update(UpdatePurchaseItemRequest $request, Purchase $purchase, PurchaseItem $purchaseItem)
    {
        HelperService::checkPurchaseItemAccess($purchase, $purchaseItem);
        $validatedData = $request->validated();
        $updated = $this->purchaseItemService->updatePurchaseItem($purchase, $purchaseItem, $validatedData);
        if ($updated) {
            return response()->json($purchase->purchaseItems);
        }
        return response()->json(['error' => 'Purchase Item could not be updated'], 400);
    }

    public function updateOtherPurchaseItem(UpdateOtherPurchaseItemRequest $request, Purchase $purchase, PurchaseItem $purchaseItem)
    {
        HelperService::checkPurchaseItemAccess($purchase, $purchaseItem);
        $validatedData = $request->validated();
        $updated = $this->purchaseItemService->updatePurchaseItem($purchase, $purchaseItem, $validatedData);
        if ($updated) {
            return response()->json($purchase->purchaseItems);
        }
        return response()->json(['error' => 'Purchase Item could not be updated'], 400);
    }

    public function destroy(Purchase $purchase, PurchaseItem $purchaseItem)
    {
        $isFullPurchaseDeleted = $this->purchaseService->deletePurchaseItem($purchase, $purchaseItem);
        if ($isFullPurchaseDeleted) {
            return redirect()->route('purchases.index')->with('success', __('message.custom.purchase.destroy.success'));
        }
        return redirect()->back()->with('success', __('message.custom.purchaseItem.destroy.success'));
    }
}
