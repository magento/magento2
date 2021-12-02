<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Inventory;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/***
 * Update stock status of bundle products based on children products stock status
 */
class ChangeParentStockStatus
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

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
     * @var ProductWebsiteLink
     */
    private $productWebsiteLink;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param Type $bundleType
     * @param ProductWebsiteLink $productWebsiteLink
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration,
        Type $bundleType,
        ProductWebsiteLink $productWebsiteLink
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->bundleType = $bundleType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->productWebsiteLink = $productWebsiteLink;
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
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());

        $criteria->setProductsFilter($productId);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();
        if (empty($allItems)) {
            return;
        }
        $parentStockItem = array_shift($allItems);
        $childrenIsInStock = $this->isChildrenInStock($productId);

        if ($this->isNeedToUpdateParent($parentStockItem, $childrenIsInStock)) {
            $parentStockItem->setIsInStock($childrenIsInStock);
            $parentStockItem->setStockStatusChangedAuto(1);
            $this->stockItemRepository->save($parentStockItem);
        }
    }

    /**
     * Check if any of bundle children products is in stock
     *
     * @param int $productId
     * @return bool
     */
    private function isChildrenInStock(int $productId) : bool
    {
        $childrenIsInStock = false;
        $childrenIds = $this->bundleType->getChildrenIds($productId, true);
        $websiteIds = $this->productWebsiteLink->getWebsiteIdsByProductId($productId);
        //prepend global scope
        array_unshift($websiteIds, null);
        foreach ($childrenIds as $childrenIdsPerOption) {
            $childrenIsInStock = false;
            foreach ($childrenIdsPerOption as $id) {
                foreach ($websiteIds as $scopeId) {
                    if ((int)$this->stockRegistry->getProductStockStatus($id, $scopeId) === 1) {
                        $childrenIsInStock = true;
                        break 2;
                    }
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
}
