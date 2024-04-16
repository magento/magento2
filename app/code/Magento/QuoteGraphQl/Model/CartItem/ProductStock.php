<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

/**
 * Product Stock class to check availability of product
 */
class ProductStock
{
    /**
     * Bundle product type code
     */
    private const PRODUCT_TYPE_BUNDLE = "bundle";

    /**
     * Configurable product type code
     */
    private const PRODUCT_TYPE_CONFIGURABLE = "configurable";

    /**
     * Simple product type code
     */
    private const PRODUCT_TYPE_SIMPLE = "simple";

    /**
     * ProductStock constructor
     *
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        private readonly StockStatusRepositoryInterface $stockStatusRepository,
        private readonly ProductRepositoryInterface $productRepositoryInterface
    ) {
    }

    /**
     * Check item status available or unavailable
     *
     * @param Item $cartItem
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isProductAvailable(Item $cartItem): bool
    {
        $requestedQty = 0;
        $previousQty = 0;
        /**
         * @var  ProductInterface $variantProduct
         * Configurable products cannot have stock, only its variants can. If the user adds a configurable product
         * using its SKU and the selected options, we need to get the variant it refers to from the quote.
         */
        $variantProduct = null;

        foreach ($cartItem->getQuote()->getItems() as $item) {
            if ($item->getItemId() !== $cartItem->getItemId()) {
                continue;
            }
            if ($cartItem->getProductType() === self::PRODUCT_TYPE_CONFIGURABLE) {
                if ($cartItem->getChildren()[0] !== null) {
                    $variantProduct = $this->productRepositoryInterface->get($item->getSku());
                }
            }
            $requestedQty = $item->getQtyToAdd() ?? $item->getQty();
            $previousQty = $item->getPreviousQty() ?? 0;
        }

        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            return $this->isStockAvailableBundle($cartItem, $previousQty, $requestedQty);
        }

        $requiredItemQty =  $requestedQty + $previousQty;
        $productId = (int) $cartItem->getProduct()->getId();
        if ($variantProduct !== null) {
            $productId = (int)$variantProduct->getId();
        }
        return $this->isStockAvailable($productId, $requiredItemQty);
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

    /**
     * Calculate available stock of a bundle
     *
     * @param Item $cartItem
     * @param int $previousQty
     * @param int|float $requestedQty
     * @return bool
     */
    public function isStockAvailableBundle(Item $cartItem, int $previousQty, $requestedQty): bool
    {
        $qtyOptions = $cartItem->getQtyOptions();
        $totalRequestedQty = $previousQty + $requestedQty;
        foreach ($qtyOptions as $qtyOption) {
            $productId = (int)$qtyOption->getProductId();
            $requiredItemQty = $qtyOption->getValue();
            if ($totalRequestedQty) {
                $requiredItemQty = $requiredItemQty * $totalRequestedQty;
            }
            if (!$this->isStockAvailable($productId, $requiredItemQty)) {
                return false;
            }
        }
        return true;
    }
}
