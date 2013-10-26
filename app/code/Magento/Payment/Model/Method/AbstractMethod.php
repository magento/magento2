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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment method abstract model
 */
namespace Magento\Payment\Model\Method;

abstract class AbstractMethod extends \Magento\Object
{
    const ACTION_ORDER             = 'order';
    const ACTION_AUTHORIZE         = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    const STATUS_UNKNOWN    = 'UNKNOWN';
    const STATUS_APPROVED   = 'APPROVED';
    const STATUS_ERROR      = 'ERROR';
    const STATUS_DECLINED   = 'DECLINED';
    const STATUS_VOID       = 'VOID';
    const STATUS_SUCCESS    = 'SUCCESS';

    /**
     * Bit masks to specify different payment method checks.
     * @see \Magento\Payment\Model\Method\AbstractMethod::isApplicableToQuote
     */
    const CHECK_USE_FOR_COUNTRY       = 1;
    const CHECK_USE_FOR_CURRENCY      = 2;
    const CHECK_USE_CHECKOUT          = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL          = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX   = 32;
    const CHECK_RECURRING_PROFILES    = 64;
    const CHECK_ZERO_TOTAL            = 128;

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info';

    /**#@+
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = false;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = true;
    /**#@-*/

    /**
     * TODO: whether a captured transaction may be voided by this gateway
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $_canCancelInvoice        = false;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Log adapter factory
     *
     * @var \Magento\Core\Model\Log\AdapterFactory
     */
    protected $_logAdapterFactory;

    /**
     * Construct
     *
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        array $data = array()
    ) {
        parent::__construct($data);
        $this->_eventManager = $eventManager;
        $this->_paymentData = $paymentData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_logAdapterFactory = $logAdapterFactory;
    }

    /**
     * Check order availability
     *
     * @return bool
     */
    public function canOrder()
    {
        return $this->_canOrder;
    }

    /**
     * Check authorise availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->_canCapture;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     */
    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->_canRefundInvoicePartial;
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Object $payment)
    {
        return $this->_canVoid;
    }

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return $this->_canUseCheckout;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return $this->_canUseForMultishipping;
    }

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit()
    {
        return true;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     */
    public function canFetchTransactionInfo()
    {
        return $this->_canFetchTransactionInfo;
    }

    /**
     * Check whether payment method instance can create billing agreements
     *
     * @return bool
     */
    public function canCreateBillingAgreement()
    {
        return $this->_canCreateBillingAgreement;
    }

    /**
     * Fetch transaction info
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        return array();
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     */
    public function isGateway()
    {
        return $this->_isGateway;
    }

    /**
     * flag if we need to run payment initialize while order place
     *
     * @return bool
     */
    public function isInitializeNeeded()
    {
        return $this->_isInitializeNeeded;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        if ($this->getConfigData('allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }

        }
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * Check manage billing agreements availability
     *
     * @return bool
     */
    public function canManageBillingAgreements()
    {
        return ($this instanceof \Magento\Payment\Model\Billing\Agreement\MethodInterface);
    }

    /**
     * Whether can manage recurring profiles
     *
     * @return bool
     */
    public function canManageRecurringProfiles()
    {
        return $this->_canManageRecurringProfiles
               && ($this instanceof \Magento\Payment\Model\Recurring\Profile\MethodInterface);
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     * @throws \Magento\Core\Exception
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            throw new \Magento\Core\Exception(__('We cannot retrieve the payment method code.'));
        }
        return $this->_code;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     */
    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }

    /**
     * Retrieve payment iformation model object
     *
     * @return \Magento\Payment\Model\Info
     * @throws \Magento\Core\Exception
     */
    public function getInfoInstance()
    {
        $instance = $this->getData('info_instance');
        if (!($instance instanceof \Magento\Payment\Model\Info)) {
            throw new \Magento\Core\Exception(__('We cannot retrieve the payment information object instance.'));
        }
        return $instance;
    }

    /**
     * Validate payment method information object
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function validate()
    {
         /**
          * to validate payment method is allowed for billing country or not
          */
         $paymentInfo = $this->getInfoInstance();
         if ($paymentInfo instanceof \Magento\Sales\Model\Order\Payment) {
             $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
         } else {
             $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
         }
         if (!$this->canUseForCountry($billingCountry)) {
             throw new \Magento\Core\Exception(
                 __('You can\'t use the payment type you selected to make payments to the billing country.')
             );
         }
         return $this;
    }

    /**
     * Order payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function order(\Magento\Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new \Magento\Core\Exception(__('The order action is not available.'));
        }
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Core\Exception(__('The authorize action is not available.'));
        }
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Core\Exception(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * Set capture transaction ID to invoice for informational purposes
     *
     * Candidate to be deprecated
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Set refund transaction id to payment object for informational purposes
     * Candidate to be deprecated:
     * there can be multiple refunds per payment, thus payment.refund_transaction_id doesn't make big sense
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function processBeforeRefund($invoice, $payment)
    {
        $payment->setRefundTransactionId($invoice->getTransactionId());
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Core\Exception(__('The refund action is not available.'));
        }
        return $this;
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $creditmemo->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Cancel payment abstract method
     *
     * @param \Magento\Object $payment
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function cancel(\Magento\Object $payment)
    {
        return $this;
    }

    /**
     * Void payment abstract method
     *
     * @param \Magento\Object $payment
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     * @throws \Magento\Core\Exception
     */
    public function void(\Magento\Object $payment)
    {
        if (!$this->canVoid($payment)) {
            throw new \Magento\Core\Exception(__('Void action is not available.'));
        }
        return $this;
    }

    /**
     * Whether this method can accept or deny payment
     *
     * @param \Magento\Payment\Model\Info $payment
     *
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return $this->_canReviewPayment;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     * @throws \Magento\Core\Exception
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        if (!$this->canReviewPayment($payment)) {
            throw new \Magento\Core\Exception(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     * @throws \Magento\Core\Exception
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        if (!$this->canReviewPayment($payment)) {
            throw new \Magento\Core\Exception(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Core\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/' . $this->getCode() . '/' . $field;
        return $this->_coreStoreConfig->getConfig($path, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  \Magento\Payment\Model\Info
     */
    public function assignData($data)
    {
        if (is_array($data)) {
            $this->getInfoInstance()->addData($data);
        }
        elseif ($data instanceof \Magento\Object) {
            $this->getInfoInstance()->addData($data->getData());
        }
        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function prepareSave()
    {
        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $checkResult = new \StdClass;
        $isActive = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
        $this->_eventManager->dispatch('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this,
            'quote'           => $quote,
        ));

        if ($checkResult->isAvailable && $quote) {
            $checkResult->isAvailable = $this->isApplicableToQuote($quote, self::CHECK_RECURRING_PROFILES);
        }
        return $checkResult->isAvailable;
    }

    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param int|null $checksBitMask
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
            if (!$this->canUseCheckout()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_MULTISHIPPING) {
            if (!$this->canUseForMultishipping()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_INTERNAL) {
            if (!$this->canUseInternal()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
            $total = $quote->getBaseGrandTotal();
            $minTotal = $this->getConfigData('min_order_total');
            $maxTotal = $this->getConfigData('max_order_total');
            if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_RECURRING_PROFILES) {
            if (!$this->canManageRecurringProfiles() && $quote->hasRecurringItems()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
            if ($total < 0.0001 && $this->getCode() != 'free'
                && !($this->canManageRecurringProfiles() && $quote->hasRecurringItems())
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->_logAdapterFactory
                ->create(array('fileName' => 'payment_' . $this->getCode() . '.log'))
               ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
               ->log($debugData);
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }
}
