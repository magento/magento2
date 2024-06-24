<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * ProductStock constructor
     *
     * @param ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepositoryInterface,
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
         * @var ProductInterface $variantProduct
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
        if ($variantProduct !== null) {
            return $this->isStockQtyAvailable($variantProduct, $requiredItemQty);
        }
        return $this->isStockQtyAvailable($cartItem->getProduct(), $requiredItemQty);
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
            if (!$this->isStockQtyAvailable($qtyOption->getProduct(), $requiredItemQty)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if product is available in stock using quantity from Catalog Inventory Stock Item
     *
     * @param ProductInterface $product
     * @param float $requiredQuantity
     * @throws NoSuchEntityException
     * @return bool
     */
    private function isStockQtyAvailable(ProductInterface $product, float $requiredQuantity): bool
    {
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        if ($stockItem === null) {
            return true;
        }
        if ((int) $stockItem->getProductId() !== (int) $product->getId()) {
            throw new NoSuchEntityException(__('Stock item\'s product ID does not match requested product ID'));
        }
        return $stockItem->getQty() >= $requiredQuantity;
    }
}
