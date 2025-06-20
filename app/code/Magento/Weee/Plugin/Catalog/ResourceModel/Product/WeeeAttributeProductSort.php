<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\LinkedProductSelectBuilderByIndexPrice;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class WeeeAttributeProductSort
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Add weee attribute to products sorting query
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param LinkedProductSelectBuilderByIndexPrice $subject
     * @param array $result
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function afterBuild(
        LinkedProductSelectBuilderByIndexPrice $subject,
        array $result,
        int $productId,
        int $storeId
    ):array {
        $select = $this->resourceConnection->getConnection()->select();

        foreach ($result as $select) {
            $select->columns(
                [
                    'weee_min_price' => new \Zend_Db_Expr(
                        '(t.min_price + IFNULL(weee_child.value, IFNULL(weee_parent.value, 0)))'
                    )
                ]
            )->joinLeft(
                ['weee_child' => $this->resourceConnection->getTableName('weee_tax')],
                'weee_child.entity_id = child.entity_id'
            )->joinLeft(
                ['weee_parent' => $this->resourceConnection->getTableName('weee_tax')],
                'weee_parent.entity_id = parent.entity_id'
            )->reset(Select::ORDER)->order('weee_min_price ASC')->limit(1);
        }

        return [$select];
    }
}
