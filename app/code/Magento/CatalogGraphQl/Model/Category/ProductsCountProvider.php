<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

/**
 * Count visible products of multiple categories with a single query against the category product index.
 */
class ProductsCountProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TableMaintainer $tableMaintainer
     * @param Visibility $catalogProductVisibility
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStatusResource $stockStatusResource
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TableMaintainer $tableMaintainer,
        Visibility $catalogProductVisibility,
        StockConfigurationInterface $stockConfiguration,
        StockStatusResource $stockStatusResource
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableMaintainer = $tableMaintainer;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Get the number of visible products per category id
     *
     * Enabled status, website membership and per store visibility are already materialized in the category product
     * index, so only the stock status still needs a join, and only when out of stock products are hidden.
     *
     * @param int $storeId
     * @param int[] $categoryIds
     * @param bool $isAnchor Anchor categories count the whole subtree, others only directly assigned products
     * @return int[] Category id to products count, categories without matching rows are omitted
     */
    public function getProductsCounts(int $storeId, array $categoryIds, bool $isAnchor): array
    {
        if (!$categoryIds) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['cat_index' => $this->tableMaintainer->getMainTable($storeId)], [])
            ->columns(
                [
                    'category_id' => 'cat_index.category_id',
                    'products_count' => new Expression('COUNT(DISTINCT cat_index.product_id)'),
                ]
            )
            ->where('cat_index.store_id = ?', $storeId)
            ->where('cat_index.category_id IN (?)', $categoryIds)
            ->where('cat_index.visibility IN (?)', $this->catalogProductVisibility->getVisibleInSiteIds())
            ->group('cat_index.category_id');

        if (!$isAnchor) {
            $select->where('cat_index.is_parent = ?', 1);
        }

        if (!$this->stockConfiguration->isShowOutOfStock()) {
            $select->join(
                ['stock_status_index' => $this->stockStatusResource->getMainTable()],
                'stock_status_index.product_id = cat_index.product_id'
                . ' AND ' . $connection->quoteInto(
                    'stock_status_index.website_id = ?',
                    (int)$this->stockConfiguration->getDefaultScopeId()
                )
                . ' AND ' . $connection->quoteInto('stock_status_index.stock_id = ?', Stock::DEFAULT_STOCK_ID),
                []
            )->where('stock_status_index.stock_status = ?', StockStatusInterface::STATUS_IN_STOCK);
        }

        return array_map('intval', $connection->fetchPairs($select));
    }
}
