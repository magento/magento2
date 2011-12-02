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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = Mage_Paypal_Model_Config::METHOD_WPS;
    protected $_formBlockType = 'Mage_Paypal_Block_Standard_Form';
    protected $_infoBlockType = 'Mage_Paypal_Block_Payment_Info';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    /**
     * Config instance
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

     /**
     * Get paypal session namespace
     *
     * @return Mage_Paypal_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('Mage_Paypal_Model_Session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Create main block for standard form
     *
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('Mage_Paypal_Block_Standard_Form', $name)
            ->setMethod('paypal_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('standard/form.phtml');

        return $block;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('paypal/standard/redirect', array('_secure' => true));
    }

    /**
     * Return form field array
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($orderIncrementId);
        /* @var $api Mage_Paypal_Model_Api_Standard */
        $api = Mage::getModel('Mage_Paypal_Model_Api_Standard')->setConfigObject($this->getConfig());
        $api->setOrderId($orderIncrementId)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            //->setPaymentAction()
            ->setOrder($order)
            ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
            ->setReturnUrl(Mage::getUrl('paypal/standard/success'))
            ->setCancelUrl(Mage::getUrl('paypal/standard/cancel'));

        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($address->validate()) {
            $api->setAddress($address);
        }

        // add cart totals and line items
        $api->setPaypalCart(Mage::getModel('Mage_Paypal_Model_Cart', array($order)))
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
        ;
        $api->setCartSummary($this->_getAggregatedCartSummary());
        $api->setLocale($api->getLocaleCode());
        $result = $api->getStandardCheckoutRequest();
        return $result;
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * Config instance getter
     * @return Mage_Paypal_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            if ($store = $this->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = Mage::getModel('Mage_Paypal_Model_Config', $params);
        }
        return $this->_config;
    }

    /**
     * Check whether payment method can be used
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) && $this->getConfig()->isMethodAvailable()) {
            return true;
        }
        return false;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfig()->$field;
    }

    /**
     * Aggregated cart summary label getter
     *
     * @return string
     */
    private function _getAggregatedCartSummary()
    {
        if ($this->_config->lineItemsSummary) {
            return $this->_config->lineItemsSummary;
        }
        return Mage::app()->getStore($this->getStore())->getFrontendName();
    }
}
