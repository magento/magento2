<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Add the stock item extension attribute to products returned by the product list, as the single product load does
 */
class ProductListStockItem
{
    /**
     * @param StockRegistryPreloader $stockRegistryPreloader
     * @param StockItemInterfaceFactory $stockItemFactory
     */
    public function __construct(
        private readonly StockRegistryPreloader $stockRegistryPreloader,
        private readonly StockItemInterfaceFactory $stockItemFactory
    ) {
    }

    /**
     * Set the stock item on every listed product, loading all stock items with one query
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductSearchResultsInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProductSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $result,
        SearchCriteriaInterface $searchCriteria
    ): ProductSearchResultsInterface {
        $products = $result->getItems();
        if (!$products) {
            return $result;
        }

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = (int)$product->getId();
        }

        $stockItems = [];
        foreach ($this->stockRegistryPreloader->preloadStockItems($productIds) as $stockItem) {
            $stockItems[(int)$stockItem->getProductId()] = $stockItem;
        }

        foreach ($products as $product) {
            $extension = $product->getExtensionAttributes();
            $extension->setStockItem($stockItems[(int)$product->getId()] ?? $this->stockItemFactory->create());
            $product->setExtensionAttributes($extension);
        }

        return $result;
    }
}
