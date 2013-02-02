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
 * Checkout api resource for Customer
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Api_Resource_Customer extends Mage_Checkout_Model_Api_Resource
{
    /**
     * Customer address types
     */
    const ADDRESS_BILLING    = Mage_Sales_Model_Quote_Address::TYPE_BILLING;
    const ADDRESS_SHIPPING   = Mage_Sales_Model_Quote_Address::TYPE_SHIPPING;

    /**
     * Customer checkout types
     */
     const MODE_CUSTOMER = Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER;
     const MODE_REGISTER = Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER;
     const MODE_GUEST    = Mage_Checkout_Model_Type_Onepage::METHOD_GUEST;


    /**
     *
     */
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')
            ->load($customerId);
        if (!$customer->getId()) {
            $this->_fault('customer_not_exists');
        }

        return $customer;
    }

    /**
     * Get customer address by identifier
     *
     * @param   int $addressId
     * @return  Mage_Customer_Model_Address
     */
    protected function _getCustomerAddress($addressId)
    {
        $address = Mage::getModel('Mage_Customer_Model_Address')->load((int)$addressId);
        if (is_null($address->getId())) {
            $this->_fault('invalid_address_id');
        }

        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function prepareCustomerForQuote(Mage_Sales_Model_Quote $quote)
    {
        $isNewCustomer = false;
        switch ($quote->getCheckoutMethod()) {
        case self::MODE_GUEST:
            $this->_prepareGuestQuote($quote);
            break;
        case self::MODE_REGISTER:
            $this->_prepareNewCustomerQuote($quote);
            $isNewCustomer = true;
            break;
        default:
            $this->_prepareCustomerQuote($quote);
            break;
        }

        return $isNewCustomer;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareGuestQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareNewCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('Mage_Customer_Model_Customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        Mage::helper('Mage_Core_Helper_Data')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $quote->setCustomer($customer)
            ->setCustomerId(true);

        return $this;
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling())
            || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);

        return $this;
    }

    /**
     * Involve new customer to system
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    public function involveNewCustomer(Mage_Sales_Model_Quote $quote)
    {
        $customer = $quote->getCustomer();
        if ($customer->isConfirmationRequired()) {
            $customer->sendNewAccountEmail('confirmation');
        } else {
            $customer->sendNewAccountEmail();
        }

        return $this;
    }
}
