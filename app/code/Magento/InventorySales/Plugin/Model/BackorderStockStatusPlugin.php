<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventorySales\Plugin\Model;

use Magento\CatalogInventory\Model\Stock\Item;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\Catalog\Model\ProductRepository;

class BackorderStockStatusPlugin
{
    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        StockItemRepository $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        ProductRepository $productRepository
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
     */
    public function aroundExecute(IsProductInStockInterface $subject, callable $proceed, string $sku, int $stockId)
    {
        $productData = $this->productRepository->get($sku);
        $productId = $productData->getId();

        $stockItemCriteria = $this->stockItemCriteriaFactory->create();
        $stockItemCriteria->setProductsFilter($productId);
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemCriteria);

        /** @var Item $stockItem */
        $stockItem = current($stockItemsCollection->getItems());

        if ($stockItem->getData('backorders') > 0) {
            return true;
        }

        return $proceed($sku, $stockId);
    }
}
