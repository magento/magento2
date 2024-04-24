<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Product Stock class to check availability of product
 */
class ProductStock
{
    /**
     * Product type code
     */
    private const PRODUCT_TYPE_BUNDLE = "bundle";

    /**
     * ProductStock constructor
     *
     * @param StockStatusRepositoryInterface $stockStatusRepository
     */
    public function __construct(
        private StockStatusRepositoryInterface $stockStatusRepository
    ) {
    }

    /**
     * Check item status available or unavailable
     *
     * @param Item $cartItem
     * @return bool
     */
    public function isProductAvailable($cartItem): bool
    {
        $requestedQty = 0;
        $previousQty = 0;

        foreach ($cartItem->getQuote()->getItems() as $item) {
            if ($item->getItemId() === $cartItem->getItemId()) {
                $requestedQty = $item->getQtyToAdd() ?? $item->getQty();
                $previousQty = $item->getPreviousQty() ?? 0;
            }
        }

        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            $qtyOptions = $cartItem->getQtyOptions();
            $totalRequestedQty = $previousQty + $requestedQty;
            foreach ($qtyOptions as $qtyOption) {
                $productId = (int) $qtyOption->getProductId();
                $requiredItemQty = (float) $qtyOption->getValue();
                if ($totalRequestedQty) {
                    $requiredItemQty = $requiredItemQty * $totalRequestedQty;
                }
                if (!$this->isStockAvailable($productId, $requiredItemQty)) {
                    return false;
                }
            }
        } else {
            $requiredItemQty =  $requestedQty + $previousQty;
            $productId = (int) $cartItem->getProduct()->getId();
            return $this->isStockAvailable($productId, $requiredItemQty);
        }
        return true;
    }

    /**
     * Check if is required product available in stock
     *
     * @param int $productId
     * @param float $requiredQuantity
     * @return bool
     */
    private function isStockAvailable(int $productId, float $requiredQuantity): bool
    {
        $stock = $this->stockStatusRepository->get($productId);
        return $stock->getQty() >= $requiredQuantity;
    }
}
