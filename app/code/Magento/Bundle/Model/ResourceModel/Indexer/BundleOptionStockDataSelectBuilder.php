<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;

class BundleOptionStockDataSelectBuilder
{
    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param $idxTable
     * @return Select
     */
    public function buildSelect($idxTable)
    {
        $select = $this->resourceConnection->getConnection()->select();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $select->from(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['bo' => $this->resourceConnection->getTableName('catalog_product_bundle_option')],
            "bo.parent_id = product.$linkField",
            []
        )->join(
            ['cis' => $this->resourceConnection->getTableName('cataloginventory_stock')],
            '',
            ['website_id', 'stock_id']
        )->joinLeft(
            ['bs' => $this->resourceConnection->getTableName('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            []
        )->joinLeft(
            ['i' => $idxTable],
            'i.product_id = bs.product_id AND i.website_id = cis.website_id AND i.stock_id = cis.stock_id',
            []
        )->joinLeft(
            ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'e.entity_id = bs.product_id',
            []
        )->group(
            ['product.entity_id', 'cis.website_id', 'cis.stock_id', 'bo.option_id']
        )->columns(['option_id' => 'bo.option_id','status' => new \Zend_Db_Expr('MAX('. $statusExpression. ')')]);

        return $select;
    }
}
