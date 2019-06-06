<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Inventory;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Process parent stock item
 */
class ParentItemProcessor
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
     * Process pare
     *
     * @param Product $product
     */
    public function process(Product $product) {
        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
        foreach ($parentIds as $productId) {
            $this->processStockForParent((int)$productId);
        }
    }

    /**
     * Change stock item for parent product depending on children stock items
     *
     * @param int $productId
     */
    private function processStockForParent(int $productId)
    {
        $childrenIds = $this->configurableType->getChildrenIds($productId);
        $allIds = $childrenIds;
        $allIds[] = $productId;

        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setProductsFilter($allIds);
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();
        if (empty($allItems[$productId])) {
            return;
        }
        $parentStockItem = $allItems[$productId];
        unset($allItems[$productId]);
        $childrenIsInStock = false;

        foreach ($allItems as $childItem) {
            if ($childItem->getIsInStock() === true) {
                $childrenIsInStock = true;
                break;
            }
        }

        if ($parentStockItem->getIsInStock() !== $childrenIsInStock) {
            if ($childrenIsInStock === false || $parentStockItem->getStockStatusChangedAuto()) {
                $parentStockItem->setIsInStock($childrenIsInStock);
                $parentStockItem->setStockStatusChangedAuto(1);
                $this->stockItemRepository->save($parentStockItem);
            }
        }
    }
}
