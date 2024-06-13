<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\StockState;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

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
     * ProductStock constructor
     *
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param StockState $stockState
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepositoryInterface,
        private readonly StockState $stockState,
        private readonly StockConfigurationInterface $stockConfiguration
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
        /**
         * @var ProductInterface $variantProduct
         * Configurable products cannot have stock, only its variants can. If the user adds a configurable product
         * using its SKU and the selected options, we need to get the variant it refers to from the quote.
         */
        $variantProduct = null;

        if ($cartItem->getProductType() === self::PRODUCT_TYPE_CONFIGURABLE) {
            if ($cartItem->getChildren()[0] !== null) {
                $variantProduct = $this->productRepositoryInterface->get($cartItem->getSku());
            }
        }
        $requestedQty = $cartItem->getQtyToAdd() ?? $cartItem->getQty();
        $previousQty = $cartItem->getPreviousQty() ?? 0;

        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            return $this->isStockAvailableBundle($cartItem, $previousQty, $requestedQty);
        }

        $requiredItemQty =  $requestedQty + $previousQty;
        if ($variantProduct !== null) {
            return $this->isStockQtyAvailable($variantProduct, $requestedQty, $requiredItemQty, $previousQty);
        }
        return $this->isStockQtyAvailable($cartItem->getProduct(), $requestedQty, $requiredItemQty, $previousQty);
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
            $requiredItemQty = $qtyOption->getValue();
            if ($totalRequestedQty) {
                $requiredItemQty = $requiredItemQty * $totalRequestedQty;
            }
            if (!$this->isStockQtyAvailable($qtyOption->getProduct(), $requestedQty, $requiredItemQty, $previousQty)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if product is available in stock
     *
     * @param ProductInterface $product
     * @param float $itemQty
     * @param float $requiredQuantity
     * @param float $prevQty
     * @return bool
     */
    private function isStockQtyAvailable(
        ProductInterface $product,
        float $itemQty,
        float $requiredQuantity,
        float $prevQty
    ): bool {
        $stockStatus = $this->stockState->checkQuoteItemQty(
            $product->getId(),
            $itemQty,
            $requiredQuantity,
            $prevQty,
            $this->stockConfiguration->getDefaultScopeId()
        );

        return ((bool) $stockStatus->getHasError()) === false;
    }
}
