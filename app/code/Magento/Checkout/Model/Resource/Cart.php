<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_init('sales_flat_quote', 'entity_id');
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
            array('q' => $this->getTable('sales_flat_quote')),
            array('items_qty', 'items_count')
        )->where(
            'q.entity_id = :quote_id'
        );

        $result = $read->fetchRow($select, array(':quote_id' => $quoteId));
        return $result ? $result : array('items_qty' => 0, 'items_count' => 0);
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
            array('qi' => $this->getTable('sales_flat_quote_item')),
            array('id' => 'item_id', 'product_id', 'super_product_id', 'qty', 'created_at')
        )->where(
            'qi.quote_id = :quote_id'
        );

        return $read->fetchAll($select, array(':quote_id' => $quoteId));
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
            $this->getTable('sales_flat_quote_item'),
            array('product_id')
        )->where(
            'quote_id = ?',
            $quoteId
        );
        $condition = $adapter->prepareSqlCondition('e.entity_id', array('nin' => $exclusionSelect));
        $collection->getSelect()->where($condition);
        return $this;
    }
}
