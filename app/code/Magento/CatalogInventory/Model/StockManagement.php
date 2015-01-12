<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class StockManagement
 */
class StockManagement implements StockManagementInterface
{
    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var StockState
     */
    protected $stockState;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $resource;

    /**
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockState $stockState
     * @param StockConfigurationInterface $stockConfiguration
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        StockState $stockState,
        StockConfigurationInterface $stockConfiguration,
        ProductRepositoryInterface $productRepository
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockState = $stockState;
        $this->stockConfiguration = $stockConfiguration;
        $this->productRepository = $productRepository;
    }

    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param string[] $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws \Magento\Framework\Model\Exception
     */
    public function registerProductsSale($items, $websiteId = null)
    {
        //if (!$websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $this->getResource()->beginTransaction();
        $lockedItems = $this->getResource()->lockProductsStock(array_keys($items), $websiteId);
        $fullSaveItems = $registeredItems = [];
        foreach ($lockedItems as $lockedItemRecord) {
            $productId = $lockedItemRecord['product_id'];
            /** @var StockItemInterface $stockItem */
            $orderedQty = $items[$productId];
            $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
            $canSubtractQty = $stockItem->getItemId() && $this->canSubtractQty($stockItem);
            if (!$canSubtractQty || !$this->stockConfiguration->isQty($this->getProductType($productId))) {
                continue;
            }
            if (!$stockItem->hasAdminArea()
                && !$this->stockState->checkQty($productId, $orderedQty, $stockItem->getWebsiteId())
            ) {
                $this->getResource()->commit();
                throw new \Magento\Framework\Model\Exception(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
            if ($this->canSubtractQty($stockItem)) {
                $stockItem->setQty($stockItem->getQty() - $orderedQty);
            }
            $registeredItems[$productId] = $orderedQty;
            if (!$this->stockState->verifyStock($productId, $stockItem->getWebsiteId())
                || $this->stockState->verifyNotification(
                    $productId,
                    $stockItem->getWebsiteId()
                )
            ) {
                $fullSaveItems[] = $stockItem;
            }
        }
        $this->getResource()->correctItemsQty($registeredItems, $websiteId, '-');
        $this->getResource()->commit();
        return $fullSaveItems;
    }

    /**
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     */
    public function revertProductsSale($items, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $this->getResource()->correctItemsQty($items, $websiteId, '+');
        return true;
    }

    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return bool
     */
    public function backItemQty($productId, $qty, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        if ($stockItem->getItemId() && $this->stockConfiguration->isQty($this->getProductType($productId))) {
            if ($this->canSubtractQty($stockItem)) {
                $stockItem->setQty($stockItem->getQty() + $qty);
            }
            if ($this->stockConfiguration->getCanBackInStock($stockItem->getStoreId()) && $stockItem->getQty()
                > $stockItem->getMinQty()
            ) {
                $stockItem->setIsInStock(true);
                $stockItem->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItem->save();
        }
        return true;
    }

    /**
     * Get Product type
     *
     * @param int $productId
     * @return string
     */
    protected function getProductType($productId)
    {
        return $this->productRepository->getById($productId)->getTypeId();
    }

    /**
     * @return \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected function getResource()
    {
        if (empty($this->resource)) {
            $this->resource = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\CatalogInventory\Model\Resource\Stock'
            );
        }
        return $this->resource;
    }

    /**
     * Check if is possible subtract value from item qty
     *
     * @param StockItemInterface $stockItem
     * @return bool
     */
    protected function canSubtractQty(StockItemInterface $stockItem)
    {
        return $stockItem->getManageStock() && $this->stockConfiguration->canSubtractQty();
    }
}
