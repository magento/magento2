<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Inventory;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;

/**
 * Process parent stock item
 */
class ParentItemProcessor implements ParentItemProcessorInterface
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
     * Process parent products
     *
     * @param Product $product
     * @return void
     */
    public function process(Product $product)
    {
        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
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
    private function isNeedToUpdateParent(
        StockItemInterface $parentStockItem,
        bool $childrenIsInStock
    ): bool {
        return $parentStockItem->getIsInStock() !== $childrenIsInStock &&
            ($childrenIsInStock === false || $parentStockItem->getStockStatusChangedAuto());
    }
}
