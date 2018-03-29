<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolverInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\GetIsStockItemSalableConditionInterface;

class SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetIsStockItemSalableConditionInterface
     */
    private $getIsStockItemSalableCondition;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetIsStockItemSalableConditionInterface $getIsStockItemSalableCondition
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetIsStockItemSalableConditionInterface $getIsStockItemSalableCondition,
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getIsStockItemSalableCondition = $getIsStockItemSalableCondition;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * Prepare select.
     *
     * @param int $stockId
     * @return Select
     */
    public function execute(int $stockId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $indexTableName = $this->indexNameBuilder
            ->setIndexId(InventoryIndexer::INDEXER_ID)
            ->addDimension('stock_', (string)$stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();

        $stockTableName = $this->indexNameResolver->resolveName($indexTableName);

        $select = $connection->select();
        $select->joinInner(
            ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_entity.sku = stock.sku',
            []
        )->joinInner(
            ['parent_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
            'parent_link.product_id = product_entity.entity_id',
            []
        )->joinInner(
            ['parent_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'parent_product_entity.entity_id = parent_link.parent_id',
            []
        );

        $select->from(
            ['stock' => $stockTableName],
            [
                SourceItemInterface::SKU => 'parent_product_entity.sku',
                IndexStructure::QUANTITY => 'SUM(stock.quantity)',
                IndexStructure::IS_SALABLE => 'MAX(stock.is_salable)',
            ]
        )
            ->group(['parent_product_entity.sku']);

        return $select;
    }
}
