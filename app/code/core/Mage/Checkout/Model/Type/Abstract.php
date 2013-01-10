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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cehckout type abstract class
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Checkout_Model_Type_Abstract extends Varien_Object
{
    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        $checkout = $this->getData('checkout_session');
        if (is_null($checkout)) {
            $checkout = Mage::getSingleton('Mage_Checkout_Model_Session');
            $this->setData('checkout_session', $checkout);
        }
        return $checkout;
    }

    /**
     * Retrieve quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Retrieve quote items
     *
     * @return array
     */
    public function getQuoteItems()
    {
        return $this->getQuote()->getAllItems();
    }

    /**
     * Retrieve customer session vodel
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        $customer = $this->getData('customer_session');
        if (is_null($customer)) {
            $customer = Mage::getSingleton('Mage_Customer_Model_Session');
            $this->setData('customer_session', $customer);
        }
        return $customer;
    }

    /**
     * Retrieve customer object
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * Retrieve customer default shipping address
     *
     * @return Mage_Customer_Model_Address || false
     */
    public function getCustomerDefaultShippingAddress()
    {
        $address = $this->getData('customer_default_shipping_address');
        if (is_null($address)) {
            $address = $this->getCustomer()->getDefaultShippingAddress();
            if (!$address) {
                foreach ($this->getCustomer()->getAddresses() as $address) {
                    if($address){
                        break;
                    }
                }
            }
            $this->setData('customer_default_shipping_address', $address);
        }
        return $address;
    }

    /**
     * Retrieve customer default billing address
     *
     * @return Mage_Customer_Model_Address || false
     */
    public function getCustomerDefaultBillingAddress()
    {
        $address = $this->getData('customer_default_billing_address');
        if (is_null($address)) {
            $address = $this->getCustomer()->getDefaultBillingAddress();
            if (!$address) {
                foreach ($this->getCustomer()->getAddresses() as $address) {
                    if($address){
                        break;
                    }
                }
            }
            $this->setData('customer_default_billing_address', $address);
        }
        return $address;
    }

    protected function _createOrderFromAddress($address)
    {
        $order = Mage::getModel('Mage_Sales_Model_Order')->createFromQuoteAddress($address)
            ->setCustomerId($this->getCustomer()->getId())
            ->setGlobalCurrencyCode('USD')
            ->setBaseCurrencyCode('USD')
            ->setStoreCurrencyCode('USD')
            ->setOrderCurrencyCode('USD')
            ->setStoreToBaseRate(1)
            ->setStoreToOrderRate(1);
        return $order;
    }
}
