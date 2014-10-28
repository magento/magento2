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
            array('item' => $this->getTable('sales_order_tax_item')),
            array('tax_id', 'tax_percent')
        )->join(
            array('tax' => $this->getTable('sales_order_tax')),
            'item.tax_id = tax.tax_id',
            array('title', 'percent', 'base_amount')
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
