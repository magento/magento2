<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryApi;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->productRepository = $productRepository;
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
        $productData = $this->productRepository->get($sku);
        $productId = $productData->getId();

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
