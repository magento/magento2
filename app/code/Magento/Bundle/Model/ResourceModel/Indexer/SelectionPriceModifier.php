<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class SelectionPriceModifier implements SelectionPriceModifierInterface
{
    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param string $connectionName
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly StockConfigurationInterface $stockConfiguration,
        private readonly string $connectionName = 'indexer'
    ) {
    }

    /**
     * @inheritDoc
     */
    public function modify(string $indexTable, array $dimensions): void
    {
        if (!$this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->resource->getConnection($this->connectionName);

        $stockIndexTableName = $this->getTable('cataloginventory_stock_status');
        $select = $connection->select()
            ->from(['i' => $indexTable])
            ->joinInner(
                ['selection' => $this->getTable('catalog_product_bundle_selection')],
                "selection.selection_id = i.selection_id",
                []
            )->joinInner(
                ['child_stock' => $stockIndexTableName],
                'child_stock.product_id = selection.product_id',
                []
            )->joinInner(
                ['parent_stock' => $stockIndexTableName],
                'parent_stock.product_id = i.entity_id',
                []
            )->where(
                'parent_stock.stock_status = 1'
            )->where(
                'child_stock.stock_status = 0'
            );
        $connection->query($connection->deleteFromSelect($select, 'i'));
    }

    /**
     * Returns fully qualified table name
     *
     * @param string $tableName
     * @return string
     */
    private function getTable(string $tableName): string
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
