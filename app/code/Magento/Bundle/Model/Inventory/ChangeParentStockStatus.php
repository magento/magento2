<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Inventory;

use Magento\Bundle\Model\Product\Type;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

/***
 * Update stock status of bundle products based on children products stock status
 */
class ChangeParentStockStatus
{
    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $criteriaInterfaceFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param Type $bundleType
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration,
        Type $bundleType
    ) {
        $this->bundleType = $bundleType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Update stock status of bundle products based on children products stock status
     *
     * @param array $childrenIds
     * @return void
     */
    public function execute(array $childrenIds): void
    {
        $parentIds = $this->bundleType->getParentIdsByChild($childrenIds);
        foreach (array_unique($parentIds) as $productId) {
            $this->processStockForParent((int)$productId);
        }
    }

    /**
     * Update stock status of bundle product based on children products stock status
     *
     * @param int $productId
     * @return void
     */
    private function processStockForParent(int $productId): void
    {
        $stockItems = $this->getStockItems([$productId]);
        $parentStockItem = $stockItems[$productId] ?? null;
        if ($parentStockItem) {
            $childrenIsInStock = $this->isChildrenInStock($productId);
            if ($this->isNeedToUpdateParent($parentStockItem, $childrenIsInStock)) {
                $parentStockItem->setIsInStock($childrenIsInStock);
                $parentStockItem->setStockStatusChangedAuto(1);
                $this->stockItemRepository->save($parentStockItem);
            }
        }
    }

    /**
     * Returns stock status of bundle product based on children stock status
     *
     * Returns TRUE if any of the following conditions is true:
     * - At least one product is in-stock in each required option
     * - Any product is in-stock (if all options are optional)
     *
     * @param int $productId
     * @return bool
     */
    private function isChildrenInStock(int $productId) : bool
    {
        $childrenIsInStock = false;
        $childrenIds = $this->bundleType->getChildrenIds($productId, true);
        $stockItems = $this->getStockItems(array_merge(...array_values($childrenIds)));
        foreach ($childrenIds as $childrenIdsPerOption) {
            $childrenIsInStock = false;
            foreach ($childrenIdsPerOption as $id) {
                $stockItem = $stockItems[$id] ?? null;
                if ($stockItem && $stockItem->getIsInStock()) {
                    $childrenIsInStock = true;
                    break;
                }
            }
            if (!$childrenIsInStock) {
                break;
            }
        }

        return $childrenIsInStock;
    }

    /**
     * Check if parent item should be updated
     *
     * @param StockItemInterface $parentStockItem
     * @param bool $childrenIsInStock
     * @return bool
     */
    private function isNeedToUpdateParent(
        StockItemInterface $parentStockItem,
        bool $childrenIsInStock
    ): bool {
        return $parentStockItem->getIsInStock() !== $childrenIsInStock &&
            ($childrenIsInStock === false || $parentStockItem->getStockStatusChangedAuto());
    }

    /**
     * Get stock items for provided product IDs
     *
     * @param array $productIds
     * @return StockItemInterface[]
     */
    private function getStockItems(array $productIds): array
    {
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());
        $criteria->setProductsFilter(array_unique($productIds));
        $stockItemCollection = $this->stockItemRepository->getList($criteria);

        $stockItems = [];
        foreach ($stockItemCollection->getItems() as $stockItem) {
            $stockItems[$stockItem->getProductId()] = $stockItem;
        }

        return $stockItems;
    }
}
