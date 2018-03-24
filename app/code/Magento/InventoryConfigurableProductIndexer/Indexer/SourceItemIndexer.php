<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

class SourceItemIndexer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexHandlerInterface $indexHandler
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexNameBuilder $indexNameBuilder,
        IndexHandlerInterface $indexHandler
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexHandler = $indexHandler;
    }

    /**
     * @param array $sourceItemIds
     */
    public function executeList(array $sourceItemIds)
    {
        $stockData = $this->getAggregatedStockDataPerStock($sourceItemIds);
        foreach ($stockData as $stockId => $indexData) {
            $indexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexHandler->cleanIndex(
                $indexName,
                $indexData,
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->indexHandler->saveIndex(
                $indexName,
                $indexData,
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }

    private function getAggregatedStockDataPerStock(array $sourceItemIds): array
    {
        $indexData = $this->resourceConnection->getConnection()
            ->query(
            'select 
                  stock_link.stock_id as stock_id, 
                  parent_entity.sku as sku, 
                  sum(source_item.quantity) as quantity, 
                  max(source_item.status) as is_salable 
                from `inventory_source_item` AS `changed_source_item`
                INNER JOIN `catalog_product_entity` AS `changed_child_entity` ON changed_child_entity.sku = changed_source_item.sku
                INNER JOIN `catalog_product_super_link` AS `parent_link` ON parent_link.product_id = changed_child_entity.entity_id
                INNER JOIN `catalog_product_entity` AS `parent_entity` ON parent_entity.entity_id = parent_link.parent_id
                inner join catalog_product_super_link as child_link on child_link.parent_id = parent_entity.entity_id
                inner join catalog_product_entity as child on child.entity_id = child_link.product_id
                inner join inventory_source_item as source_item on source_item.sku = child.`sku`
                inner join inventory_source_stock_link as stock_link on stock_link.source_code = source_item.source_code
                WHERE (changed_source_item.source_item_id IN (' . implode(',', $sourceItemIds) . '))
                group by stock_link.stock_id, parent_entity.entity_id
                order by stock_link.stock_id'
            )
            ->fetchAll();

        $dataPerStock = [];
        foreach ($indexData as $data) {
            if (!isset($dataPerStock[$data['stock_id']])) {
                $dataPerStock[$data['stock_id']] = [];
            }
            $dataPerStock[$data['stock_id']][] = [
                IndexStructure::SKU => $data['sku'],
                IndexStructure::QUANTITY => $data['quantity'],
                IndexStructure::IS_SALABLE => $data['is_salable'],
            ];
        }

        foreach ($dataPerStock as $stockId => $indexData) {
            $dataPerStock[$stockId] = new \ArrayIterator($indexData);
        }

        return $dataPerStock;
    }
}
