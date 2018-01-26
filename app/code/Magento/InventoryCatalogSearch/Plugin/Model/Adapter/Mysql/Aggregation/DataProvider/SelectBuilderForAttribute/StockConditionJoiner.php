<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\StockConditionJoiner
    as LegacyStockConditionJoiner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;

/**
 * Adapt stock condition joiner to multi stocks.
 */
class StockConditionJoiner
{
    /**
     * @var StockIndexTableNameResolver
     */
    private $stockIndexTableNameResolver;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param StockIndexTableNameResolver $stockIndexTableNameResolver
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param ResourceConnection $resource
     */
    public function __construct(
        StockIndexTableNameResolver $stockIndexTableNameResolver,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        ResourceConnection $resource
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->resource = $resource;
    }

    /**
     * @param LegacyStockConditionJoiner $stockConditionJoiner
     * @param callable $proceed
     * @param Select $select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        LegacyStockConditionJoiner $stockConditionJoiner,
        callable $proceed,
        Select $select
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $select->joinInner(
            ['product' => $this->resource->getTableName('catalog_product_entity')],
            'main_table.source_id = product.entity_id',
            []
        );
        $select->joinInner(
            ['stock_index' => $tableName],
            'product.sku = stock_index.sku',
            []
        )->where('stock_index.' . IndexStructure::QUANTITY . ' > 0');
    }
}
