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
use Magento\CatalogInventory\Model\StockStatusApplierInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ObjectManager;

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
     * @var StockStatusApplierInterface
     */
    private $stockStatusApplier;

    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStatusApplierInterface|null $stockStatusApplier
     */
    public function __construct(
        ResourceConnection $resource,
        StockConfigurationInterface $stockConfiguration,
        ?StockStatusApplierInterface $stockStatusApplier = null
    ) {
        $this->resource = $resource;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStatusApplier = $stockStatusApplier
            ?? ObjectManager::getInstance()->get(StockStatusApplierInterface::class);
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

        if ($this->stockStatusApplier->hasSearchResultApplier()) {
            $select->columns(["{$stockStatusTableAlias}.stock_status AS is_salable"]);
        } else {
            $select->where("{$stockStatusTableAlias}.stock_status = ?", StockStatusInterface::STATUS_IN_STOCK);
        }

        return $select;
    }
}
