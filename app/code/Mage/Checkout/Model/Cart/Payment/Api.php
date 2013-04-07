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

class Mage_Checkout_Model_Cart_Payment_Api extends Mage_Checkout_Model_Api_Resource
{

    /**
     * Returns payment data or empty array in case it is not valid
     *
     * @param array $data
     * @return array
     */
    protected function _preparePaymentData($data)
    {
        if (!is_array($data)) {
            return array();
        }

        return $data;
    }

    /**
     * @param  $method
     * @param  $quote
     * @return bool
     */
    protected function _canUsePaymentMethod($method, $quote)
    {
        if ( !($method->isGateway() || $method->canUseInternal()) ) {
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

    protected function _getPaymentMethodAvailableCcTypes($method)
    {
        $ccTypes = Mage::getSingleton('Mage_Payment_Model_Config')->getCcTypes();
        $methodCcTypes = explode(',',$method->getConfigData('cctypes'));
        foreach ($ccTypes as $code=>$title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }

    /**
     * Retrieve available payment methods for a quote
     *
     * @param int $quoteId
     * @param int $store
     * @return array
     */
    public function getPaymentMethodsList($quoteId, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);
        $store = $quote->getStoreId();

        $total = $quote->getBaseSubtotal();

        $methodsResult = array();
        $methods = Mage::helper('Mage_Payment_Helper_Data')->getStoreMethods($store, $quote);

        foreach ($methods as $method) {
            /** @var $method Mage_Payment_Model_Method_Abstract */
            if ($this->_canUsePaymentMethod($method, $quote)) {
                $isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();

                if ($total != 0 || $method->getCode() == 'free' || $isRecurring) {
                    $methodsResult[] = array(
                        'code' => $method->getCode(),
                        'title' => $method->getTitle(),
                        'cc_types' => $this->_getPaymentMethodAvailableCcTypes($method),
                    );
                }
            }
        }

        return $methodsResult;
    }

    /**
     * @param  $quoteId
     * @param  $paymentData
     * @param  $store
     * @return bool
     */
    public function setPaymentMethod($quoteId, $paymentData, $store=null)
    {
        $quote = $this->_getQuote($quoteId, $store);
        $store = $quote->getStoreId();

        $paymentData = $this->_preparePaymentData($paymentData);

        if (empty($paymentData)) {
            $this->_fault("payment_method_empty");
        }

        if ($quote->isVirtual()) {
            // check if billing address is set
            if (is_null($quote->getBillingAddress()->getId()) ) {
                $this->_fault('billing_address_is_not_set');
            }
            $quote->getBillingAddress()->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
        } else {
            // check if shipping address is set
            if (is_null($quote->getShippingAddress()->getId()) ) {
                $this->_fault('shipping_address_is_not_set');
            }
            $quote->getShippingAddress()->setPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);
        }

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $total = $quote->getBaseSubtotal();
        $methods = Mage::helper('Mage_Payment_Helper_Data')->getStoreMethods($store, $quote);

        foreach ($methods as $method) {
            /** @var $method Mage_Payment_Model_Method_Abstract */
            if ($method->getCode() == $paymentData['method']) {
                $isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();
                $isAllowedMethod = $total != 0 || $method->getCode() == 'free' || $isRecurring;
                if (!$this->_canUsePaymentMethod($method, $quote) || !$isAllowedMethod) {
                    $this->_fault("method_not_allowed");
                }
            }
        }

        try {
            $payment = $quote->getPayment();
            $payment->importData($paymentData);


            $quote->setTotalsCollectedFlag(false)
                    ->collectTotals()
                    ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('payment_method_is_not_set', $e->getMessage());
        }
        return true;
    }

}
