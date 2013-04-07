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
 * Shopping cart api
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Checkout_Model_Cart_Api extends Mage_Checkout_Model_Api_Resource
{
    public function __construct(Mage_Api_Helper_Data $apiHelper)
    {
        parent::__construct($apiHelper);
        $this->_storeIdSessionField = "cart_store_id";
        $this->_attributesMap['quote'] = array('quote_id' => 'entity_id');
        $this->_attributesMap['quote_customer'] = array('customer_id' => 'entity_id');
        $this->_attributesMap['quote_address'] = array('address_id' => 'entity_id');
        $this->_attributesMap['quote_payment'] = array('payment_id' => 'entity_id');
    }

    /**
     * Prepare payment data for futher usage
     *
     * @param array $data
     * @return array
     */
    protected function _preparePaymentData($data)
    {
        if (!(is_array($data) && is_null($data[0]))) {
            return array();
        }

        return $data;
    }

    /**
     * Create new quote for shopping cart
     *
     * @param int|string $store
     * @return int
     */
    public function create($store = null)
    {
        $storeId = $this->_getStoreId($store);

        try {
            /*@var $quote Mage_Sales_Model_Quote*/
            $quote = Mage::getModel('Mage_Sales_Model_Quote');
            $quote->setStoreId($storeId)
                    ->setIsActive(false)
                    ->setIsMultiShipping(false)
                    ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('create_quote_fault', $e->getMessage());
        }
        return (int) $quote->getId();
    }

    /**
     * Retrieve full information about quote
     *
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function info($quoteId, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);

        if ($quote->getGiftMessageId() > 0) {
            $quote->setGiftMessage(
                Mage::getSingleton('Mage_GiftMessage_Model_Message')->load($quote->getGiftMessageId())->getMessage()
            );
        }

        $result = $this->_getAttributes($quote, 'quote');
        $result['shipping_address'] = $this->_getAttributes($quote->getShippingAddress(), 'quote_address');
        $result['billing_address'] = $this->_getAttributes($quote->getBillingAddress(), 'quote_address');
        $result['items'] = array();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('Mage_GiftMessage_Model_Message')->load($item->getGiftMessageId())->getMessage()
                );
            }

            $result['items'][] = $this->_getAttributes($item, 'quote_item');
        }

        $result['payment'] = $this->_getAttributes($quote->getPayment(), 'quote_payment');

        return $result;
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return void
     */
    public function totals($quoteId, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);

        $totals = $quote->getTotals();

        $totalsResult = array();
        foreach ($totals as $total) {
            $totalsResult[] = array(
                "title" => $total->getTitle(),
                "amount" => $total->getValue()
            );
        }
        return $totalsResult;
    }

    /**
     * Check wether we can use this payment method with current quote
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    protected function _canUsePaymentMethod($method, $quote)
    {
        if (!($method->isGateway() || $method->canUseInternal())) {
            return false;
        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    /**
     * Create an order from the shopping cart (quote)
     *
     * @param int $quoteId
     * @param string|int $store
     * @param array $agreements
     * @return string
     */
    public function createOrder($quoteId, $store = null, $agreements = null)
    {
        $this->_checkAgreement($agreements);
        $quote = $this->_getQuote($quoteId, $store);
        $orderId = $this->_placeOrder($quote);
        return $orderId;
    }

    /**
     * Create an order from the shopping cart (quote) with ability to set payment method
     *
     * @param int $quoteId
     * @param string|int $store
     * @param array $agreements
     * @param array $paymentData
     * @return string
     */
    public function createOrderWithPayment($quoteId, $store = null, $agreements = null, $paymentData = null)
    {
        $this->_checkAgreement($agreements);
        $quote = $this->_getQuote($quoteId, $store);
        $this->_setPayment($quote, $store, $paymentData);
        $orderId = $this->_placeOrder($quote);
        return $orderId;
    }

    /**
     * Convert quote to order and return order increment id
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    protected function _placeOrder($quote)
    {
        if ($quote->getIsMultiShipping()) {
            $this->_fault('invalid_checkout_type');
        }
        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST
            && !Mage::helper('Mage_Checkout_Helper_Data')->isAllowedGuestCheckout($quote, $quote->getStoreId())
        ) {
            $this->_fault('guest_checkout_is_not_enabled');
        }

        Mage::getConfig()->setCurrentAreaCode('adminhtml');
        /** @var $customerResource Mage_Checkout_Model_Api_Resource_Customer */
        $customerResource = Mage::getModel("Mage_Checkout_Model_Api_Resource_Customer");
        $isNewCustomer = $customerResource->prepareCustomerForQuote($quote);

        try {
            $quote->collectTotals();
            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('Mage_Sales_Model_Service_Quote', array('quote' => $quote));
            $service->submitAll();

            if ($isNewCustomer) {
                try {
                    $customerResource->involveNewCustomer($quote);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            $order = $service->getOrder();
            if ($order) {
                Mage::dispatchEvent(
                    'checkout_type_onepage_save_order_after',
                    array('order' => $order, 'quote' => $quote)
                );

                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        } catch (Mage_Core_Exception $e) {
            $this->_fault('create_order_fault', $e->getMessage());
        }

        return $order->getIncrementId();
    }

    /**
     * Set payment data
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string|int $store
     * @param array $paymentData
     */
    protected function _setPayment($quote, $store = null, $paymentData = null)
    {
        if ($paymentData !== null) {
            $paymentData = $this->_preparePaymentData($paymentData);
            if (empty($paymentData)) {
                $this->_fault('payment_method_empty');
            }

            if ($quote->isVirtual()) {
                // check if billing address is set
                if (is_null($quote->getBillingAddress()->getId())) {
                    $this->_fault('billing_address_is_not_set');
                }
                $quote->getBillingAddress()
                    ->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
            } else {
                // check if shipping address is set
                if (is_null($quote->getShippingAddress()->getId()) ) {
                    $this->_fault('shipping_address_is_not_set');
                }
                $quote->getShippingAddress()
                    ->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
            }

            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }

            $total = $quote->getBaseSubtotal();
            $methods = Mage::helper('Mage_Payment_Helper_Data')->getStoreMethods($store, $quote);
            foreach ($methods as $key => $method) {
                if ($method->getCode() == $paymentData['method']) {
                    /** @var $method Mage_Payment_Model_Method_Abstract */
                    if (!($this->_canUsePaymentMethod($method, $quote) && ($total != 0 || $method->getCode() == 'free'
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))
                    ) {
                        $this->_fault('method_not_allowed');
                    }
                }
            }
            try {
                $quote->getPayment()->importData($paymentData);
                $quote->setTotalsCollectedFlag(false)->collectTotals();
            } catch (Mage_Core_Exception $e) {
                $this->_fault('payment_method_is_not_set', $e->getMessage());
            }
        }
    }

    /**
     * Check required billing agreements
     *
     * @param array $agreements
     */
    protected function _checkAgreement($agreements = null)
    {
        $requiredAgreements = Mage::helper('Mage_Checkout_Helper_Data')->getRequiredAgreementIds();
        if (!empty($requiredAgreements)) {
            $diff = array_diff($agreements, $requiredAgreements);
            if (!empty($diff)) {
                $this->_fault('required_agreements_are_not_all');
            }
        }
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function licenseAgreement($quoteId, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);
        $storeId = $quote->getStoreId();

        $agreements = array();
        if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
            $agreementsCollection = Mage::getModel('Mage_Checkout_Model_Agreement')->getCollection()
                    ->addStoreFilter($storeId)
                    ->addFieldToFilter('is_active', 1);

            foreach ($agreementsCollection as $_a) {
                /** @var $_a  Mage_Checkout_Model_Agreement */
                $agreements[] = $this->_getAttributes($_a, "quote_agreement");
            }
        }

        return $agreements;
    }
}
