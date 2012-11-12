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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Direct Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Direct extends Mage_Payment_Model_Method_Cc
{
    protected $_code  = Mage_Paypal_Model_Config::METHOD_WPP_DIRECT;
    protected $_infoBlockType = 'Mage_Paypal_Block_Payment_Info';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment        = true;

    /**
     * Website Payments Pro instance
     *
     * @var Mage_Paypal_Model_Pro
     */
    protected $_pro = null;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'Mage_Paypal_Model_Pro';

    public function __construct($params = array())
    {
        $proInstance = array_shift($params);
        if ($proInstance && ($proInstance instanceof Mage_Paypal_Model_Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = Mage::getModel($this->_proType);
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param Mage_Core_Model_Store|int $store
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        if (null === $store) {
            $store = Mage::app()->getStore()->getId();
        }
        $this->_pro->getConfig()->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_pro->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see Mage_Sales_Model_Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_pro->getConfig()->getPaymentAction();
    }

    /**
     * Return available CC types for gateway based on merchant country
     *
     * @return string
     */
    public function getAllowedCcTypes()
    {
        $ccTypes = explode(',', $this->_pro->getConfig()->cctypes);
        $country = $this->_pro->getConfig()->getMerchantCountry();

        if ($country == 'GB') {
            $ccTypes = array_intersect(array('SM', 'SO', 'MC', 'DI', 'VI'), $ccTypes);
        } elseif ($country == 'CA') {
            $ccTypes = array_intersect(array('MC', 'VI'), $ccTypes);
        }
        return implode(',', $ccTypes);
    }

    /**
     * Check whether payment method can be used
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) && $this->_pro->getConfig()->isMethodAvailable()) {
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
        $value = null;
        switch ($field)
        {
            case 'cctypes':
                $value = $this->getAllowedCcTypes();
                break;
            default:
                $value = $this->_pro->getConfig()->$field;
        }
        return $value;
    }

    /**
     * Authorize payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function void(Varien_Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function cancel(Varien_Object $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    public function canReviewPayment(Mage_Payment_Model_Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, Mage_Paypal_Model_Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, Mage_Paypal_Model_Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Set fallback API URL if not defined in configuration
     *
     * @return Mage_Centinel_Model_Service
     */
    public function getCentinelValidator()
    {
        $validator = parent::getCentinelValidator();
        if (!$validator->getCustomApiEndpointUrl()) {
            $validator->setCustomApiEndpointUrl($this->_pro->getConfig()->centinelDefaultApiUrl);
        }
        return $validator;
    }

    /**
     * Fetch transaction details info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Direct
     */
    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $order = $payment->getOrder();
        $api = $this->_pro->getApi()
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setIpAddress(Mage::app()->getRequest()->getClientIp(false))
            ->setAmount($amount)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setInvNum($order->getIncrementId())
            ->setEmail($order->getCustomerEmail())
            ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
            ->setCreditCardType($payment->getCcType())
            ->setCreditCardNumber($payment->getCcNumber())
            ->setCreditCardExpirationDate(
                $this->_getFormattedCcExpirationDate($payment->getCcExpMonth(), $payment->getCcExpYear())
            )
            ->setCreditCardCvv2($payment->getCcCid())
            ->setMaestroSoloIssueNumber($payment->getCcSsIssue())
        ;
        if ($payment->getCcSsStartMonth() && $payment->getCcSsStartYear()) {
            $year = sprintf('%02d', substr($payment->getCcSsStartYear(), -2, 2));
            $api->setMaestroSoloIssueDate(
                $this->_getFormattedCcExpirationDate($payment->getCcSsStartMonth(), $year)
            );
        }
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->exportCmpiData($api);
        }

        // add shipping and billing addresses
        if ($order->getIsVirtual()) {
            $api->setAddress($order->getBillingAddress())->setSuppressShipping(true);
        } else {
            $api->setAddress($order->getShippingAddress());
            $api->setBillingAddress($order->getBillingAddress());
        }

        // add line items
        $parameters = array('params' => array($order));
        $api->setPaypalCart(Mage::getModel('Mage_Paypal_Model_Cart', $parameters))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled)
        ;

        // call api and import transaction and other payment information
        $api->callDoDirectPayment();
        $this->_importResultToPayment($api, $payment);

        try {
            $api->callGetTransactionDetails();
        } catch (Mage_Core_Exception $e) {
            // if we recieve errors, but DoDirectPayment response is Success, then set Pending status for transaction
            $payment->setIsTransactionPending(true);
        }
        $this->_importResultToPayment($api, $payment);
        return $this;
    }

    /**
     * Format credit card expiration date based on month and year values
     * Format: mmyyyy
     *
     * @param string|int $month
     * @param string|int $year
     * @return string
     */
    protected function _getFormattedCcExpirationDate($month, $year)
    {
        return sprintf('%02d%02d', $month, $year);
    }

    /**
     * Import direct payment results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0);
        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Check void availability
     *
     * @param   Varien_Object $payment
     * @return  bool
     */
    public function canVoid(Varien_Object $payment)
    {
        if ($payment instanceof Mage_Sales_Model_Order_Invoice
            || $payment instanceof Mage_Sales_Model_Order_Creditmemo
        ) {
            return false;
        }

        return $this->_canVoid;
    }
}
