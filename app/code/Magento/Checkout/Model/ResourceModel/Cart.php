<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\ResourceModel;

/**
 * Resource model for Checkout Cart
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('quote', 'entity_id');
    }

    /**
     * Fetch items summary
     *
     * @param int $quoteId
     * @return array
     */
    public function fetchItemsSummary($quoteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['q' => $this->getTable('quote')],
            ['items_qty', 'items_count']
        )->where(
            'q.entity_id = :quote_id'
        );

        $result = $connection->fetchRow($select, [':quote_id' => $quoteId]);
        return $result ? $result : ['items_qty' => 0, 'items_count' => 0];
    }

    /**
     * Fetch items
     *
     * @param int $quoteId
     * @return array
     */
    public function fetchItems($quoteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['qi' => $this->getTable('quote_item')],
            ['id' => 'item_id', 'product_id', 'super_product_id', 'qty', 'created_at']
        )->where(
            'qi.quote_id = :quote_id'
        );

        return $connection->fetchAll($select, [':quote_id' => $quoteId]);
    }

    /**
     * Make collection not to load products that are in specified quote
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param int $quoteId
     * @return $this
     */
    public function addExcludeProductFilter($collection, $quoteId)
    {
        $connection = $this->getConnection();
        $exclusionSelect = $connection->select()->from(
            $this->getTable('quote_item'),
            ['product_id']
        )->where(
            'quote_id = ?',
            $quoteId
        );
        $condition = $connection->prepareSqlCondition('e.entity_id', ['nin' => $exclusionSelect]);
        $collection->getSelect()->where($condition);
        return $this;
    }
}
