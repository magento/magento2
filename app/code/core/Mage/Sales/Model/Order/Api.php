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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Api extends Mage_Sales_Model_Api_Resource
{
    public function __construct()
    {
        $this->_attributesMap['order']         = array('order_id' => 'entity_id');
        $this->_attributesMap['order_address'] = array('address_id' => 'entity_id');
        $this->_attributesMap['order_payment'] = array('payment_id' => 'entity_id');

    }

    /**
     * Initialize basic order model
     *
     * @param mixed $orderIncrementId
     * @return Mage_Sales_Model_Order
     */
    protected function _initOrder($orderIncrementId)
    {
        $order = Mage::getModel('Mage_Sales_Model_Order');

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            $this->_fault('not_exists');
        }

        return $order;
    }

    /**
     * Retrieve list of orders by filters
     *
     * @param array $filters
     * @return array
     */
    public function items($filters = null)
    {
        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        $collection = Mage::getModel('Mage_Sales_Model_Order')->getCollection()
            ->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect(
                'billing_firstname', "{{billing_firstname}}", array('billing_firstname'=>"$billingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'billing_lastname', "{{billing_lastname}}", array('billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname'=>"$shippingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname'=>"$shippingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'billing_name',
                    "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                    array('billing_firstname'=>"$billingAliasName.firstname", 'billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname'=>"$shippingAliasName.firstname", 'shipping_lastname'=>"$shippingAliasName.lastname")
            );

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_attributesMap['order'][$field])) {
                        $field = $this->_attributesMap['order'][$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $order) {
            $result[] = $this->_getAttributes($order, 'order');
        }

        return $result;
    }

    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        if ($order->getGiftMessageId() > 0) {
            $order->setGiftMessage(
                Mage::getSingleton('Mage_GiftMessage_Model_Message')->load($order->getGiftMessageId())->getMessage()
            );
        }

        $result = $this->_getAttributes($order, 'order');

        $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
        $result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
        $result['items'] = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('Mage_GiftMessage_Model_Message')->load($item->getGiftMessageId())->getMessage()
                );
            }

            $result['items'][] = $this->_getAttributes($item, 'order_item');
        }

        $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');

        $result['status_history'] = array();

        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
        }

        return $result;
    }

    /**
     * Add comment to order
     *
     * @param string $orderIncrementId
     * @param string $status
     * @param string $comment
     * @param boolean $notify
     * @return boolean
     */
    public function addComment($orderIncrementId, $status, $comment = null, $notify = false)
    {
        $order = $this->_initOrder($orderIncrementId);

        $order->addStatusToHistory($status, $comment, $notify);


        try {
            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Hold order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function hold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->hold();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Unhold order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function unhold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->unhold();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Cancel order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function cancel($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->cancel();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

} // Class Mage_Sales_Model_Order_Api End
