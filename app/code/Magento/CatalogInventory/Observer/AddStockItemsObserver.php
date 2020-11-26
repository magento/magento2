<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add Stock items to product collection.
 */
class AddStockItemsObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * @var StockRegistryPreloader
     */
    private $stockRegistryPreloader;

    /**
     * AddStockItemsObserver constructor.
     *
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryPreloader $stockRegistryPreloader
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryPreloader $stockRegistryPreloader
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryPreloader = $stockRegistryPreloader;
    }

    /**
     * Add stock items to products in collection.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Collection $productCollection */
        $productCollection = $observer->getData('collection');
        $productIds = array_keys($productCollection->getItems());
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $stockItems = [];
        if ($productIds) {
            $stockItems = $this->stockRegistryPreloader->preloadStockItems($productIds, $scopeId);
            $this->stockRegistryPreloader->preloadStockStatuses($productIds, $scopeId);
        }
        foreach ($stockItems as $item) {
            /** @var Product $product */
            $product = $productCollection->getItemById($item->getProductId());
            $productExtension = $product->getExtensionAttributes();
            $productExtension->setStockItem($item);
            $product->setExtensionAttributes($productExtension);
        }
    }
}
