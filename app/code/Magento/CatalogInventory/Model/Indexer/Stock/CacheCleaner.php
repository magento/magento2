<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\Product;

/**
 * Clean product cache only when stock status was updated
 */
class CacheCleaner
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ResourceConnection $resource,
        StockConfigurationInterface $stockConfiguration,
        CacheContext $cacheContext,
        ManagerInterface $eventManager
    ) {
        $this->resource = $resource;
        $this->stockConfiguration = $stockConfiguration;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    /**
     * @param array $productIds
     * @param callable $reindex
     * @return void
     */
    public function clean(array $productIds, callable $reindex)
    {
        $productStatusesBefore = $this->getProductStockStatuses($productIds);
        $reindex();
        $productStatusesAfter = $this->getProductStockStatuses($productIds);
        $productIds = $this->getProductIds($productStatusesBefore, $productStatusesAfter);
        if ($productIds) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }

    /**
     * @param array $productIds
     * @return array
     */
    private function getProductStockStatuses(array $productIds)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->resource->getTableName('cataloginventory_stock_status'),
                ['product_id', 'stock_status', 'qty']
            )->where('product_id IN (?)', $productIds)
            ->where('stock_id = ?', Stock::DEFAULT_STOCK_ID)
            ->where('website_id = ?', $this->stockConfiguration->getDefaultScopeId());

        $statuses = [];
        foreach ($this->getConnection()->fetchAll($select) as $item) {
            $statuses[$item['product_id']] = $item;
        }
        return $statuses;
    }

    /**
     * @param array $productStatusesBefore
     * @param array $productStatusesAfter
     * @return array
     */
    private function getProductIds(array $productStatusesBefore, array $productStatusesAfter)
    {
        $productIds = [];
        $stockThresholdQty = $this->stockConfiguration->getStockThresholdQty();
        foreach ($productStatusesBefore as $productId => $statusBefore) {
            $statusAfter = $productStatusesAfter[$productId];
            if ($statusBefore['stock_status'] !== $statusAfter['stock_status']
                || ($stockThresholdQty && $statusAfter['qty'] <= $stockThresholdQty)) {
                $productIds[] = $productId;
            }
        }

        return $productIds;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }

        return $this->connection;
    }
}
