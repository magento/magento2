<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Product;

/**
 * Resolver to get product positions by ids assigned to specific category
 */
class PositionResolver extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Get category product positions
     *
     * @param int $categoryId
     * @return array
     */
    public function getPositions(int $categoryId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            'catalog_product_entity',
            'entity_id'
        )->where(
            'catalog_category_product.category_id = ?', $categoryId
        )->order(
            'catalog_category_product.position ' . \Magento\Framework\DB\Select::SQL_ASC
        );
        $select->joinLeft(
            'catalog_category_product',
            'catalog_category_product.product_id=catalog_product_entity.entity_id',
            []
        );

        return array_flip($connection->fetchCol($select));
    }
}
