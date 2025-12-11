<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Class calculates composite product relations.
 */
class CompositeProductRelationsCalculator
{
    /**
     * @var DefaultPrice
     */
    private $indexerResource;

    /**
     * @param DefaultPrice $indexerResource
     */
    public function __construct(DefaultPrice $indexerResource)
    {
        $this->indexerResource = $indexerResource;
    }

    /**
     * Returns maximum number of composite related products.
     *
     * @return int
     */
    public function getMaxRelationsCount()
    {
        $connection = $this->indexerResource->getConnection();
        $relationSelect = $connection->select();
        $relationSelect->from(
            ['relation' => $this->indexerResource->getTable('catalog_product_relation')],
            ['count' => new \Zend_Db_Expr('count(relation.child_id)')]
        );
        $relationSelect->group('parent_id');

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $relationSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );
        return $connection->fetchOne($maxSelect);
    }
}
