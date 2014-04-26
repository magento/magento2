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
namespace Magento\RecurringPayment\Model\Resource;

/**
 * Recurring payment resource model
 */
class Payment extends \Magento\Sales\Model\Resource\AbstractResource
{
    /**
     * Initialize main table and column
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('recurring_payment', 'payment_id');

        $this->_serializableFields = array(
            'payment_vendor_info' => array(null, array()),
            'additional_info' => array(null, array()),
            'order_info' => array(null, array()),
            'order_item_info' => array(null, array()),
            'billing_address_info' => array(null, array()),
            'shipping_address_info' => array(null, array())
        );
    }

    /**
     * Return recurring payment child Orders Ids
     *
     *
     * @param \Magento\Framework\Object $object
     * @return array
     */
    public function getChildOrderIds($object)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(':payment_id' => $object->getId());
        $select = $adapter->select()->from(
            array('main_table' => $this->getTable('recurring_payment_order')),
            array('order_id')
        )->where(
            'payment_id=:payment_id'
        );

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Add order relation to recurring payment
     *
     * @param int $recurringPaymentId
     * @param int $orderId
     * @return $this
     */
    public function addOrderRelation($recurringPaymentId, $orderId)
    {
        $this->_getWriteAdapter()->insert(
            $this->getTable('recurring_payment_order'),
            array('payment_id' => $recurringPaymentId, 'order_id' => $orderId)
        );
        return $this;
    }
}
