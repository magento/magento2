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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Order Webhook observer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Webhook_Observer
{
    public function dispatchOrderCreatedEvent($observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                $isCompleted = $this->_isValueRegistered('webhook_order_completed', $order->getId());
                if (!$isCompleted) {
                    $this->_registerValue('webhook_order_completed', $order->getId());
                    Mage::helper('Mage_Webhook_Helper_Data')->dispatchEvent(
                        'order/created',
                        array('order' => $order, 'shipping_address' => $order->getShippingAddress())
                    );
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /** The following functions belong in a parent class or helper method */
    /**
     * Sets a value in the Mage::registry with a key based on registerName and key.
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @param $value the value to set
     */
    protected function _registerKeyValue($registerName, $key, $value)
    {
        $array = Mage::registry($registerName);

        if (!isset($array)) {
            $array = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        $array[$key] = $value;

        Mage::register($registerName, $array);
    }

    /**
     * Gets the value registered in the Mage::registry and the key (e.g. order id).
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @return the value registered, null if no value
     */
    protected function _getRegisterValueForKey($registerName, $key)
    {
        $array = Mage::registry($registerName);

        if (isset($array)) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }

        return null;
    }

    /**
     * Registers a value (such as an order or product id) in an array acting
     * as a set.
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     */
    protected function _registerValue($registerName, $value)
    {
        $set = Mage::registry($registerName);

        if (!isset($set)) {
            $set = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        if (!in_array($value, $set)) {
            array_push($set, $value);
        }

        Mage::register($registerName, $set);
    }

    /**
     * Determines if a value has been registered
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     * @return true if the value is in the set, false otherwise
     */
    protected function _isValueRegistered($registerName, $value)
    {
        $set = Mage::registry($registerName);
        if (isset($set) && in_array($value, $set)) {
            return true;
        }

        return false;
    }
}
