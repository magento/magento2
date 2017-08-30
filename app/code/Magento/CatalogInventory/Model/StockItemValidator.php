<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * StockItemValidator
 */
class StockItemValidator
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Validate Stock item
     *
     * @param ProductInterface $product
     * @param StockItemInterface $stockItem
     * @throws LocalizedException
     * @return void
     */
    public function validate(ProductInterface $product, StockItemInterface $stockItem)
    {
        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        $defaultStockId = $this->stockRegistry->getStock($defaultScopeId)->getStockId();
        $stockId = $stockItem->getStockId();
        if ($stockId !== null && $stockId != $defaultStockId) {
            throw new LocalizedException(
                __('Invalid stock id: %1. Only default stock with id %2 allowed', $stockId, $defaultStockId)
            );
        }

        $stockItemId = $stockItem->getItemId();
        if ($stockItemId !== null && (!is_numeric($stockItemId) || $stockItemId <= 0)) {
            throw new LocalizedException(
                __('Invalid stock item id: %1. Should be null or numeric value greater than 0', $stockItemId)
            );
        }

        $defaultStockItemId = $this->stockRegistry->getStockItem($product->getId())->getItemId();
        if ($defaultStockItemId && $stockItemId !== null && $defaultStockItemId != $stockItemId) {
            throw new LocalizedException(
                __('Invalid stock item id: %1. Assigned stock item id is %2', $stockItemId, $defaultStockItemId)
            );
        }
    }
}
