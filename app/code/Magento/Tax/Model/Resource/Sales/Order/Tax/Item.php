<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order tax resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Resource\Sales\Order\Tax;

class Item extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax_item', 'tax_item_id');
    }

    /**
     * Get Tax Items with order tax information
     *
     * @param int $item_id
     * @return array
     */
    public function getTaxItemsByItemId($item_id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            ['item' => $this->getTable('sales_order_tax_item')],
            ['tax_id', 'tax_percent']
        )->join(
            ['tax' => $this->getTable('sales_order_tax')],
            'item.tax_id = tax.tax_id',
            ['title', 'percent', 'base_amount']
        )->where(
            'item_id = ?',
            $item_id
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Get Tax Items with order tax information
     *
     * @param int $orderId
     * @return array
     */
    public function getTaxItemsByOrderId($orderId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            ['item' => $this->getTable('sales_order_tax_item')],
            [
                'tax_id',
                'tax_percent',
                'item_id',
                'taxable_item_type',
                'associated_item_id',
                'real_amount',
                'real_base_amount',
            ]
        )->join(
            ['tax' => $this->getTable('sales_order_tax')],
            'item.tax_id = tax.tax_id',
            ['code', 'title', 'order_id']
        )->where(
            'tax.order_id = ?',
            $orderId
        );

        return $adapter->fetchAll($select);
    }
}
