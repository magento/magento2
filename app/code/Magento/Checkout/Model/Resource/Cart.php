<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Resource;

/**
 * Resource model for Checkout Cart
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_quote', 'entity_id');
    }

    /**
     * Fetch items summary
     *
     * @param int $quoteId
     * @return array
     */
    public function fetchItemsSummary($quoteId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from(
            ['q' => $this->getTable('sales_quote')],
            ['items_qty', 'items_count']
        )->where(
            'q.entity_id = :quote_id'
        );

        $result = $read->fetchRow($select, [':quote_id' => $quoteId]);
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
        $read = $this->_getReadAdapter();
        $select = $read->select()->from(
            ['qi' => $this->getTable('sales_quote_item')],
            ['id' => 'item_id', 'product_id', 'super_product_id', 'qty', 'created_at']
        )->where(
            'qi.quote_id = :quote_id'
        );

        return $read->fetchAll($select, [':quote_id' => $quoteId]);
    }

    /**
     * Make collection not to load products that are in specified quote
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param int $quoteId
     * @return $this
     */
    public function addExcludeProductFilter($collection, $quoteId)
    {
        $adapter = $this->_getReadAdapter();
        $exclusionSelect = $adapter->select()->from(
            $this->getTable('sales_quote_item'),
            ['product_id']
        )->where(
            'quote_id = ?',
            $quoteId
        );
        $condition = $adapter->prepareSqlCondition('e.entity_id', ['nin' => $exclusionSelect]);
        $collection->getSelect()->where($condition);
        return $this;
    }
}
