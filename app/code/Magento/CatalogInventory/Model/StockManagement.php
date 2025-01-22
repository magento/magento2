<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\RegisterProductSaleInterface;
use Magento\CatalogInventory\Api\RevertProductSaleInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\Framework\Exception\LocalizedException;

/**
 * Implements a few interfaces for backward compatibility
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockManagement implements StockManagementInterface, RegisterProductSaleInterface, RevertProductSaleInterface
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
     * @var ResourceStock
     */
    protected $resource;

    /**
     * @var QtyCounterInterface
     */
    private $qtyCounter;

    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;

    /**
     * @param ResourceStock $stockResource
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockState $stockState
     * @param StockConfigurationInterface $stockConfiguration
     * @param ProductRepositoryInterface $productRepository
     * @param QtyCounterInterface $qtyCounter
     * @param StockRegistryStorage|null $stockRegistryStorage
     */
    public function __construct(
        ResourceStock $stockResource,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockState $stockState,
        StockConfigurationInterface $stockConfiguration,
        ProductRepositoryInterface $productRepository,
        QtyCounterInterface $qtyCounter,
        ?StockRegistryStorage $stockRegistryStorage = null
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockState = $stockState;
        $this->stockConfiguration = $stockConfiguration;
        $this->productRepository = $productRepository;
        $this->qtyCounter = $qtyCounter;
        $this->resource = $stockResource;
        $this->stockRegistryStorage = $stockRegistryStorage ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StockRegistryStorage::class);
    }

    /**
     * Subtract product qtys from stock.
     *
     * Return array of items that require full save.
     *
     * @param string[] $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws StockStateException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function registerProductsSale($items, $websiteId = null)
    {
        //if (!$websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        //}
        $this->getResource()->beginTransaction();
        $lockedItems = $this->getResource()->lockProductsStock(array_keys($items), $websiteId);
        $fullSaveItems = $registeredItems = [];
        foreach ($lockedItems as $lockedItemRecord) {
            $productId = $lockedItemRecord['product_id'];
            $this->stockRegistryStorage->removeStockItem($productId, $websiteId);

            /** @var StockItemInterface $stockItem */
            $orderedQty = $items[$productId];
            $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
            $stockItem->setQty($lockedItemRecord['qty']); // update data from locked item
            $canSubtractQty = $stockItem->getItemId() && $this->canSubtractQty($stockItem);
            if (!$canSubtractQty || !$this->stockConfiguration->isQty($lockedItemRecord['type_id'])) {
                continue;
            }
            if (!$stockItem->hasAdminArea()
                && !$this->stockState->checkQty($productId, $orderedQty, $stockItem->getWebsiteId())
            ) {
                $this->getResource()->commit();
                throw new StockStateException(
                    __('Some of the products are out of stock.')
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
        $this->qtyCounter->correctItemsQty($registeredItems, $websiteId, '-');
        $this->getResource()->commit();

        return $fullSaveItems;
    }

    /**
     * @inheritdoc
     */
    public function revertProductsSale($items, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultScopeId();
        //}
        $revertItems = [];
        foreach ($items as $productId => $qty) {
            $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
            $canSubtractQty = $stockItem->getItemId() && $this->canSubtractQty($stockItem);
            if (!$canSubtractQty || !$this->stockConfiguration->isQty($stockItem->getTypeId())) {
                continue;
            }
            $revertItems[$productId] = $qty;
        }
        $this->qtyCounter->correctItemsQty($revertItems, $websiteId, '+');

        return $revertItems;
    }

    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @return bool
     */
    public function backItemQty($productId, $qty, $scopeId = null)
    {
        //if (!$scopeId) {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        //}
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
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
     * Get stock resource.
     *
     * @return ResourceStock
     */
    protected function getResource()
    {
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
