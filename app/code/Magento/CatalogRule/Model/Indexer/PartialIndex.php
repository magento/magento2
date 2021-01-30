<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Catalog rule partial index
 *
 * This class triggers the dependent index "catalog_product_price",
 * and the cache is cleared only for the matched products for partial indexing.
 */
class PartialIndex
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @param ResourceConnection $resource
     * @param IndexBuilder $indexBuilder
     */
    public function __construct(
        ResourceConnection $resource,
        IndexBuilder $indexBuilder
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * Synchronization replica table with original table "catalogrule_product_price"
     *
     * Used replica table for correctly working MySQL trigger
     *
     * @return void
     */
    public function partialUpdateCatalogRuleProductPrice(): void
    {
        $this->indexBuilder->reindexFull();
        $indexTableName = $this->resource->getTableName('catalogrule_product_price');
        $select = $this->connection->select()->from(
            ['crp' => $indexTableName],
            'product_id'
        );
        $selectFields = $this->connection->select()->from(
            ['crp' => $indexTableName],
            [
                'rule_date',
                'customer_group_id',
                'product_id',
                'rule_price',
                'website_id',
                'latest_start_date',
                'earliest_end_date',
            ]
        );
        $where = ['product_id' .' NOT IN (?)' => $select];
        //remove products that are no longer used in indexing
        $this->connection->delete($this->resource->getTableName('catalogrule_product_price_replica'), $where);
        //add updated products to indexing
        $this->connection->query(
            $this->connection->insertFromSelect(
                $selectFields,
                $this->resource->getTableName('catalogrule_product_price_replica'),
                [
                    'rule_date',
                    'customer_group_id',
                    'product_id',
                    'rule_price',
                    'website_id',
                    'latest_start_date',
                    'earliest_end_date',
                ],
                AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }
}
