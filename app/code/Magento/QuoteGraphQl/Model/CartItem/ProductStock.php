<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;

/**
 * Product Stock class to check the availability of product
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
     * ProductStock Constructor
     *
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param StockState $stockState
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepositoryInterface,
        private readonly StockState $stockState,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StockRegistryInterface $stockRegistry
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
        $requestedQty = (float)($cartItem->getQtyToAdd() ?? $cartItem->getQty());
        $previousQty = (int)$cartItem->getPreviousQty() ?? 0;

        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            return $this->isStockAvailableBundle($cartItem, $previousQty, $requestedQty);
        }

        $variantProduct = $this->getVariantProduct($cartItem);
        $requiredItemQty =  $requestedQty + $previousQty;
        if ($variantProduct !== null) {
            return $this->isStockQtyAvailable(
                $cartItem,
                $variantProduct,
                $requestedQty,
                $requiredItemQty,
                $previousQty
            );
        }
        return $this->isStockQtyAvailable(
            $cartItem,
            $cartItem->getProduct(),
            $requestedQty,
            $requiredItemQty,
            $previousQty
        );
    }

    /**
     * Calculate available stock of a bundle
     *
     * @param Item $cartItem
     * @param int $previousQty
     * @param int|float $requestedQty
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isStockAvailableBundle(Item $cartItem, int $previousQty, $requestedQty): bool
    {
        $qtyOptions = $cartItem->getQtyOptions();
        $totalRequestedQty = $previousQty + $requestedQty;
        foreach ($qtyOptions as $qtyOption) {
            $requiredItemQty = (float)$qtyOption->getValue();
            if ($totalRequestedQty) {
                $requiredItemQty = $requiredItemQty * $totalRequestedQty;
            }
            if (!$this->isStockQtyAvailable(
                $cartItem,
                $qtyOption->getProduct(),
                $requestedQty,
                $requiredItemQty,
                $previousQty
            )) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the cart item's available stock value
     *
     * @param Item $cartItem
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductAvailableStock(Item $cartItem): float
    {
        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            return $this->getLowestStockValueOfBundleProduct($cartItem);
        }

        $variantProduct = $this->getVariantProduct($cartItem);
        if ($variantProduct !== null) {
            return $this->getAvailableStock($variantProduct);
        }
        return $this->getAvailableStock($cartItem->getProduct());
    }

    /**
     * Returns variant product if available
     *
     * @param Item $cartItem
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    private function getVariantProduct(Item $cartItem): ?ProductInterface
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
        return $variantProduct;
    }

    /**
     * Check if product is available in stock
     *
     * @param Item $cartItem
     * @param ProductInterface $product
     * @param float $itemQty
     * @param float $requiredQuantity
     * @param float $prevQty
     * @return bool
     */
    private function isStockQtyAvailable(
        Item $cartItem,
        ProductInterface $product,
        float $itemQty,
        float $requiredQuantity,
        float $prevQty
    ): bool {
        $this->stockState->checkQuoteItemQty(
            $product->getId(),
            $itemQty,
            $requiredQuantity,
            $prevQty,
            $cartItem->getStore()->getId()
        );

        return ($this->getProductStockStatus($product)->getStockStatus() &&
            $this->getAvailableStock($product) >= $itemQty);
    }

    /**
     * Returns the product's available stock value
     *
     * @param ProductInterface $product
     * @return float
     */
    private function getAvailableStock(ProductInterface $product): float
    {
        return (float) $this->getProductStockStatus($product)->getQty();
    }

    /**
     * Returns the lowest stock value of bundle product
     *
     * @param Item $cartItem
     * @return float
     */
    private function getLowestStockValueOfBundleProduct(Item $cartItem): float
    {
        $bundleStock = [];
        $qtyOptions = $cartItem->getQtyOptions();
        foreach ($qtyOptions as $qtyOption) {
            $bundleStock[] = $this->getAvailableStock($qtyOption->getProduct());
        }

        return min($bundleStock);
    }

    /**
     * Returns the lowest stock value of bundle product
     *
     * @param Item $cartItem
     * @param float|null $thresholdQty
     * @return float
     */
    private function getLowestSaleableQtyOfBundleProduct(Item $cartItem, ?float $thresholdQty): float
    {
        $bundleStock = [];
        foreach ($cartItem->getQtyOptions() as $qtyOption) {
            $bundleStock[] = $this->getSaleableQty($qtyOption->getProduct(), $thresholdQty);
        }
        return $bundleStock ? (float)min($bundleStock) : 0.0;
    }

    /**
     * Returns the cart item's saleable qty value
     *
     * @param Item $cartItem
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductSaleableQty(Item $cartItem): float
    {
        $thresholdQty = (float)$this->scopeConfig->getValue(
            Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            ScopeInterface::SCOPE_STORE
        );

        if ($thresholdQty === 0.0) {
            return $this->getProductAvailableStock($cartItem);
        }

        return $this->getSaleableQtyByCartItem($cartItem, $thresholdQty);
    }

    /**
     * Returns the saleable qty value by cart item
     *
     * @param Item $cartItem
     * @param float|null $thresholdQty
     * @return float
     * @throws NoSuchEntityException
     */
    public function getSaleableQtyByCartItem(Item $cartItem, ?float $thresholdQty): float
    {
        if ($cartItem->getProductType() === self::PRODUCT_TYPE_BUNDLE) {
            return $this->getLowestSaleableQtyOfBundleProduct($cartItem, $thresholdQty);
        }

        $variantProduct = $this->getVariantProduct($cartItem);
        if ($variantProduct !== null) {
            return $this->getSaleableQty($variantProduct, $thresholdQty);
        }

        return $this->getSaleableQty($cartItem->getProduct(), $thresholdQty);
    }

    /**
     * Get product saleable qty when "Catalog > Inventory > Stock Options > Only X left Threshold" is greater than 0
     *
     * @param ProductInterface $product
     * @param float|null $thresholdQty
     * @return float
     */
    public function getSaleableQty(ProductInterface $product, ?float $thresholdQty): float
    {
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());
        $stockQty = (float)$stockStatus->getQty();
        if ($thresholdQty === null) {
            return $stockQty;
        }
        $stockLeft = $stockQty - $this->stockRegistry->getStockItem($product->getId())->getMinQty();

        return ($stockQty >= 0 && $stockLeft <= $thresholdQty) ? $stockQty : 0.0;
    }

    /**
     * Returns the stock status of a product
     *
     * @param ProductInterface $product
     * @return StockStatusInterface
     */
    private function getProductStockStatus(ProductInterface $product): StockStatusInterface
    {
        return $this->stockRegistry->getStockStatus(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
    }
}
