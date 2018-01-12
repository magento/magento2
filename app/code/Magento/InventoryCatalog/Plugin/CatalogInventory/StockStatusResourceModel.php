<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Inventory\Indexer\IndexStructure;
use Magento\Inventory\Model\StockIndexManager;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class StockStatusResourceModel
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StockIndexManager
     */
    private $stockIndexManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param StockIndexManager $stockIndexManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        StockIndexManager $stockIndexManager
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->stockIndexManager = $stockIndexManager;
    }

    /**
     * Around  plugin for @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::addStockDataToCollection()
     *
     * @param Status $stockStatus
     * @param callable $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $tableName = $this->stockIndexManager->getTableNameByStockId($this->getStockId());

        $method = $isFilterInStock ? 'join' : 'joinLeft';

        $isSalableExpression = $collection->getConnection()->getCheckSql(
            'stock_status_index.' . IndexStructure::QUANTITY . ' > 0',
            1,
            0
        );
        $collection->getSelect()->$method(
            ['stock_status_index' => $tableName],
            'e.sku = stock_status_index.' . IndexStructure::SKU,
            ['is_salable' => $isSalableExpression]
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.' . IndexStructure::QUANTITY . ' > 0'
            );
        }

        return $collection;
    }

    /**
     * Get stock id by website code.
     * @return string
     */
    private function getStockId(): string
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();

        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();

        return (string)$stockId;
    }
}
