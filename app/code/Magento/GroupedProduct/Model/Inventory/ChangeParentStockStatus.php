<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Inventory;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

/**
 * Change stock status of grouped product by child product id
 */
class ChangeParentStockStatus
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
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Product metadata pool
     *
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Grouped $groupedType
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Grouped $groupedType,
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resource,
        MetadataPool $metadataPool
    ) {
        $this->groupedType = $groupedType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItemRepository = $stockItemRepository;
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Change stock item for parent product depending on children stock items
     *
     * @param int $productId
     * @return void
     */
    public function execute(int $productId): void
    {
        $parentIds = $this->getParentEntityIdsByChild($productId);
        foreach ($parentIds as $productId) {
            $this->changeParentStockStatus((int)$productId);
        }
    }

    /**
     * Change stock status of grouped product
     *
     * @param int $productId
     * @return void
     */
    private function changeParentStockStatus(int $productId): void
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

    /**
     * Retrieve parent ids array by child id
     *
     * @param int $childId
     * @return array
     */
    private function getParentEntityIdsByChild(int $childId): array
    {
        $select = $this->resource->getConnection()
            ->select()
            ->from(['l' => $this->resource->getTableName('catalog_product_link')], [])
            ->join(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                'e.' .
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField() . ' = l.product_id',
                ['e.entity_id']
            )
            ->where('l.linked_product_id = ?', $childId)
            ->where(
                'link_type_id = ?',
                Link::LINK_TYPE_GROUPED
            );

        return $this->resource->getConnection()->fetchCol($select);
    }
}
