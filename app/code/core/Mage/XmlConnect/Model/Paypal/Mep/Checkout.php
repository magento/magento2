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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wrapper that performs Paypal MEP and Checkout communication
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Paypal_Mep_Checkout
{
    /**#@+
     * Keys for passthrough variables in sales/quote_payment and sales/order_payment
     * Uses additional_information as storage
     */
    const PAYMENT_INFO_PAYER_EMAIL = 'paypal_payer_email';
    const PAYMENT_INFO_TRANSACTION_ID = 'paypal_mep_checkout_transaction_id';
    /**#@-*/

    /**
     * Payment method type
     *
     * @var string
     */
    protected $_methodType = Mage_XmlConnect_Model_Payment_Method_Paypal_Mep::MEP_METHOD_CODE;

    /**
     * Quote model
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    /**
     * Checkout session model
     *
     * @var Mage_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * XmlConnect default helper
     *
     * @var Mage_XmlConnect_Helper_Data
     */
    protected $_helper;

    /**
     * Set quote instances
     *
     * @throws Mage_Core_Exception
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->_checkoutSession = Mage::getSingleton('Mage_Checkout_Model_Session');
        if (isset($params['quote']) && $params['quote'] instanceof Mage_Sales_Model_Quote) {
            $this->_quote = $params['quote'];
        } else {
            Mage::throwException(
                Mage::helper('Mage_XmlConnect_Helper_Data')->__('Quote instance is required.')
            );
        }
    }

    /**
     * Prepare quote, reserve order ID for specified quote
     *
     * @return string
     */
    public function initCheckout()
    {
        $this->_quote->reserveOrderId()->save();

        /**
         * Reset multishipping flag before any manipulations with quote address
         * addAddress method for quote object related on this flag
         */
        if ($this->_quote->getIsMultiShipping()) {
            $this->_quote->setIsMultiShipping(false);
            $this->_quote->save();
        }

        /*
        * want to load the correct customer information by assigning to address
        * instead of just loading from sales/quote_address
        */
        $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
        if ($customer) {
            $this->_quote->assignCustomer($customer);
        }
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
            && Mage::helper('Mage_Checkout_Helper_Data')->isAllowedGuestCheckout($quote)
        ) {
            $this->_prepareGuestQuote();
        }
        return $this->_quote->getReservedOrderId();
    }

    /**
     * Save shipping and billing address information to quote
     *
     * @param array $data
     * @return array
     */
    public function saveShipping($data)
    {
        if (empty($data)) {
            return array('error' => 1, 'message' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Invalid data.'));
        }

        $address = $this->_quote->getBillingAddress();

        $this->_applyCountryWorkarounds($data);
        /** @var $model Mage_XmlConnect_Model_Application */
        $model = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();

        $paypalMepAllowSpecific = $model->getData('config_data[payment:paypalmep/allowspecific]');
        if ($paypalMepAllowSpecific !== null) {
            if ((int)$paypalMepAllowSpecific > 0) {
                $allowedCountries = explode(',', $model->getData('config_data[payment][paypalmep/applicable]'));
                $allowedCountries = array_map('trim', $allowedCountries);
                if (!in_array(trim($data['country_id']), $allowedCountries)) {
                    return array(
                        'error' => 1,
                        'message' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Buyer country is not allowed by store.')
                    );
                }
            }
        }

        if (empty($data['firstname']) && empty($data['lastname'])) {
            if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
                $data['firstname'] = $customer->getFirstname();
                $data['lastname'] = $customer->getLastname();
            } else {
                $data['firstname'] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Guest');
                $data['lastname'] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Guest');
            }
        }

        $address->addData($data);

        $this->_ignoreAddressValidation();

        $address->implodeStreetAddress();

        if (!$this->_quote->isVirtual()) {
            $billing = clone $address;
            $billing->unsAddressId()->unsAddressType();
            $shipping = $this->_quote->getShippingAddress();
            $shippingMethod = $shipping->getShippingMethod();
            $shipping->addData($billing->getData())->setSameAsBilling(1)->setShippingMethod($shippingMethod)
                ->setCollectShippingRates(true);
        }

        $this->_quote->collectTotals()->save();
        return array();
    }

    /**
     * Specify quote shipping method
     *
     * @param string $shippingMethod
     * @return array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            return array('error' => 1, 'message' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Invalid shipping method.'));
        }

        $rate = $this->_quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => 1, 'message' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Invalid shipping method.'));
        }

        $shippingAddress = $this->_quote->getShippingAddress();
        if (!$this->_quote->getIsVirtual() && $shippingAddress) {
            if ($shippingMethod != $shippingAddress->getShippingMethod()) {
                $this->_ignoreAddressValidation();
                $this->_quote->getShippingAddress()->setShippingMethod($shippingMethod);
                $this->_quote->collectTotals()->save();
            }
        }

        return array();
    }

    /**
     * Specify quote payment method
     *
     * @param array $data
     * @return array
     */
    public function savePayment($data)
    {
        if ($this->_quote->isVirtual()) {
            $this->_quote->getBillingAddress()->setPaymentMethod($this->_methodType);
        } else {
            $this->_quote->getShippingAddress()->setPaymentMethod($this->_methodType);
        }

        $payment = $this->_quote->getPayment();
        $data['method'] = $this->_methodType;
        $payment->importData($data);

        $email = isset($data['payer']) ? $data['payer'] : null;
        $payment->setAdditionalInformation(self::PAYMENT_INFO_PAYER_EMAIL, $email);
        $payment->setAdditionalInformation(
            self::PAYMENT_INFO_TRANSACTION_ID, isset($data['transaction_id']) ? $data['transaction_id'] : null
        );
        $this->_quote->setCustomerEmail($email);

        $this->_quote->collectTotals()->save();

        return array();
    }

    /**
     * Place the order when customer returned from paypal
     * Until this moment all quote data must be valid
     *
     * @return array
     */
    public function saveOrder()
    {
        $this->_ignoreAddressValidation();

        $order = Mage::getModel('Mage_Sales_Model_Service_Quote', $this->_quote)->submit();
        $this->_quote->save();

        $this->_getCheckoutSession()->clear();

        /**
         * Prepare session to success or cancellation page
         */
        $quoteId = $this->_quote->getId();
        $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId)
            ->setLastOrderId($order->getId())->setLastRealOrderId($order->getIncrementId());

        if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING
            && Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
        ) {
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return array();
    }

    /**
     * Get last order increment id by order id
     *
     * @return string
     */
    public function getLastOrderId()
    {
        $lastId  = $this->_getCheckoutSession()->getLastOrderId();
        $orderId = false;
        if ($lastId) {
            $order = Mage::getModel('Mage_Sales_Model_Order');
            $order->load($lastId);
            $orderId = $order->getIncrementId();
        }
        return $orderId;
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return null
     */
    protected function _ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Get frontend checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return Mage_XmlConnect_Model_Paypal_Mep_Checkout
     */
    protected function _prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Adopt specified request array from PayPal
     *
     * @param array $request
     * @return null
     */
    protected function _applyCountryWorkarounds(&$request)
    {
        $request['country_id'] = isset($request['country_id']) ? trim($request['country_id']) : null;
        if (empty($request['country_id'])) {
            $request['country_id'] = strtoupper(Mage::getStoreConfig('general/country/default'));
        } else {
            $request['country_id'] = strtoupper($request['country_id']);
        }
    }
}
