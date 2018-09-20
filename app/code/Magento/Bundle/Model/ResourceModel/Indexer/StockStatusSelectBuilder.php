<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Framework\DB\Select;

/**
 * Class StockStatusSelectBuilder
 * Is used to create Select object that is used for Bundle product stock status indexation
 *
 * @see \Magento\Bundle\Model\ResourceModel\Indexer\Stock::_getStockStatusSelect
 */
class StockStatusSelectBuilder
{

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param Select $select
     * @return Select
     * @throws \Exception
     */
    public function buildSelect(Select $select)
    {
        $select = clone $select;

        $select->reset(
            Select::COLUMNS
        )->columns(
            ['e.entity_id', 'cis.website_id', 'cis.stock_id']
        )->joinLeft(
            ['o' => $this->resourceConnection->getTableName('catalog_product_bundle_stock_index')],
            'o.entity_id = e.entity_id AND o.website_id = cis.website_id AND o.stock_id = cis.stock_id',
            []
        )->columns(
            ['qty' => new \Zend_Db_Expr('0')]
        );

        return $select;
    }
}
