<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.2.0
 */
class CacheCleaner
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @var StockConfigurationInterface
     * @since 2.2.0
     */
    private $stockConfiguration;

    /**
     * @var CacheContext
     * @since 2.2.0
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     * @since 2.2.0
     */
    private $eventManager;

    /**
     * @var AdapterInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function clean(array $productIds, callable $reindex)
    {
        $productStatusesBefore = $this->getProductStockStatuses($productIds);
        $reindex();
        $productStatusesAfter = $this->getProductStockStatuses($productIds);
        $productIds = $this->getProductIdsForCacheClean($productStatusesBefore, $productStatusesAfter);
        if ($productIds) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }

    /**
     * @param array $productIds
     * @return array
     * @since 2.2.0
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
     * Return list of product ids that need to be flushed from cache
     *
     * @param array $productStatusesBefore
     * @param array $productStatusesAfter
     * @return array
     * @since 2.2.0
     */
    private function getProductIdsForCacheClean(array $productStatusesBefore, array $productStatusesAfter)
    {
        $disabledProductsIds = array_diff(array_keys($productStatusesBefore), array_keys($productStatusesAfter));
        $enabledProductsIds = array_diff(array_keys($productStatusesAfter), array_keys($productStatusesBefore));
        $commonProductsIds = array_intersect(array_keys($productStatusesBefore), array_keys($productStatusesAfter));
        $productIds = array_merge($disabledProductsIds, $enabledProductsIds);

        $stockThresholdQty = $this->stockConfiguration->getStockThresholdQty();

        foreach ($commonProductsIds as $productId) {
            $statusBefore = $productStatusesBefore[$productId];
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
     * @since 2.2.0
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }

        return $this->connection;
    }
}
