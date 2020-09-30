<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Generic in-stock status filter
 */
class StockStatusFilter implements StockStatusFilterInterface
{
    private const TABLE_NAME = 'cataloginventory_stock_status';
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        ResourceConnection $resource,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->resource = $resource;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        Select $select,
        string $productTableAlias,
        string $stockStatusTableAlias = self::TABLE_ALIAS,
        ?int $websiteId = null
    ): Select {
        $stockStatusTable = $this->resource->getTableName(self::TABLE_NAME);
        $joinCondition = [
            "{$stockStatusTableAlias}.product_id = {$productTableAlias}.entity_id",
            $select->getConnection()->quoteInto(
                "{$stockStatusTableAlias}.website_id = ?",
                $this->stockConfiguration->getDefaultScopeId()
            ),
            $select->getConnection()->quoteInto(
                "{$stockStatusTableAlias}.stock_id = ?",
                Stock::DEFAULT_STOCK_ID
            )
        ];
        $select->join(
            [$stockStatusTableAlias => $stockStatusTable],
            implode(' AND ', $joinCondition),
            []
        );
        $select->where("{$stockStatusTableAlias}.stock_status = ?", StockStatusInterface::STATUS_IN_STOCK);

        return $select;
    }
}
