<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Inventory;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/***
 * Update stock status of configurable products based on children products stock status
 */
class ChangeParentStockStatus
{
    /**
     * @var Configurable
     */
    private $configurableType;

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
     * @param Configurable $configurableType
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Configurable $configurableType,
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->configurableType = $configurableType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Update stock status of configurable products based on children products stock status
     *
     * @param array $childrenIds
     * @return void
     */
    public function execute(array $childrenIds): void
    {
        $parentIds = $this->configurableType->getParentIdsByChild($childrenIds);
        foreach (array_unique($parentIds) as $productId) {
            $this->processStockForParent((int)$productId);
        }
    }

    /**
     * Update stock status of configurable product based on children products stock status
     *
     * @param int $productId
     * @return void
     */
    private function processStockForParent(int $productId): void
    {
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());

        $criteria->setProductsFilter($productId);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();
        if (empty($allItems)) {
            return;
        }
        $parentStockItem = array_shift($allItems);

        $childrenIds = $this->configurableType->getChildrenIds($productId);
        $criteria->setProductsFilter($childrenIds);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();

        $childrenIsInStock = false;

        foreach ($allItems as $childItem) {
            if ($childItem->getIsInStock() === true) {
                $childrenIsInStock = true;
                break;
            }
        }

        if ($this->isNeedToUpdateParent($parentStockItem, $childrenIsInStock)) {
            $parentStockItem->setIsInStock($childrenIsInStock);
            $parentStockItem->setStockStatusChangedAuto(1);
            $parentStockItem->setStockStatusChangedAutomaticallyFlag(true);
            $this->stockItemRepository->save($parentStockItem);
        }
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
}
