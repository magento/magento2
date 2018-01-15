<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Adapt backorders to IsProductInStockInterface
 */
class BackorderStockStatusPlugin
{
    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkusInterface;

    /**
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param GetProductIdsBySkusInterface $getProductIdsBySkusInterface
     */
    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        GetProductIdsBySkusInterface $getProductIdsBySkusInterface
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->getProductIdsBySkusInterface = $getProductIdsBySkusInterface;
    }

    /**
     * Return true status if backorders is enabled for the item
     *
     * @param IsProductInStockInterface $subject
     * @param callable $proceed
     * @param string $sku
     * @param int $stockId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        IsProductInStockInterface $subject,
        callable $proceed,
        string $sku,
        int $stockId
    ): bool {
        $productIds = $this->getProductIdsBySkusInterface->execute([$sku]);
        $productId = $productIds[$sku];

        $stockItemCriteria = $this->stockItemCriteriaFactory->create();
        $stockItemCriteria->setProductsFilter($productId);
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemCriteria);

        /** @var StockItemInterface $legacyStockItem */
        $legacyStockItem = current($stockItemsCollection->getItems());

        if ($legacyStockItem->getBackorders() > 0) {
            return true;
        }
        return $proceed($sku, $stockId);
    }
}
