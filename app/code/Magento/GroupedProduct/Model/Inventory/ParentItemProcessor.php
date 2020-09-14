<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Inventory;

use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Process parent stock item for grouped product
 */
class ParentItemProcessor implements ParentItemProcessorInterface
{
    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $criteriaInterfaceFactory;

    /**
     * @param Grouped $groupedType
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Grouped $groupedType,
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->groupedType = $groupedType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Process parent products
     *
     * @param Product $product
     * @return void
     */
    public function process(Product $product)
    {
        $parentIds = $this->groupedType->getParentIdsByChild($product->getId());
        foreach ($parentIds as $productId) {
            $this->processStockForParent((int)$productId);
        }
    }

    /**
     * Change stock item for parent product depending on children stock items
     *
     * @param int $productId
     * @return void
     */
    private function processStockForParent(int $productId)
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
        $groupedChildrenIds = $this->groupedType->getChildrenIds($productId);
        $criteria->setProductsFilter($groupedChildrenIds);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();

        $groupedChildrenIsInStock = false;

        foreach ($allItems as $childItem) {
            if ($childItem->getIsInStock() === true) {
                $groupedChildrenIsInStock = true;
                break;
            }
        }

        if ($this->isNeedToUpdateParent($parentStockItem, $groupedChildrenIsInStock)) {
            $parentStockItem->setIsInStock($groupedChildrenIsInStock);
            $parentStockItem->setStockStatusChangedAuto(1);
            $this->stockItemRepository->save($parentStockItem);
        }
    }

    /**
     * Check is parent item should be updated
     *
     * @param StockItemInterface $parentStockItem
     * @param bool $childrenIsInStock
     * @return bool
     */
    private function isNeedToUpdateParent(StockItemInterface $parentStockItem, bool $childrenIsInStock): bool
    {
        return $parentStockItem->getIsInStock() !== $childrenIsInStock &&
            ($childrenIsInStock === false || $parentStockItem->getStockStatusChangedAuto());
    }
}
