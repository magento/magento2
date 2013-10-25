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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Express Module
 */
namespace Magento\Paypal\Model;

class Express extends \Magento\Payment\Model\Method\AbstractMethod
    implements \Magento\Payment\Model\Recurring\Profile\MethodInterface
{
    /**
     * @var string
     */
    protected $_code  = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Express\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'Magento\Paypal\Model\Pro';

    /**
     * Availability options
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;

    /**
     * Website Payments Pro instance
     *
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    /**
     * Payment additional information key for payment action
     * @var string
     */
    protected $_isOrderPaymentActionKey = 'is_order_action';

    /**
     * Payment additional information key for number of used authorizations
     *
     * @var string
     */
    protected $_authorizationCountKey = 'authorization_count';

    /**
     * @var \Magento\Paypal\Model\Method\ProTypeFactory
     */
    protected $_proTypeFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        array $data = array()
    ) {
        parent::__construct(
            $eventManager,
            $paymentData,
            $coreStoreConfig,
            $logAdapterFactory,
            $data
        );
        $this->_proTypeFactory = $proTypeFactory;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartFactory = $cartFactory;

        $proInstance = array_shift($data);
        if ($proInstance && ($proInstance instanceof \Magento\Paypal\Model\Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = $this->_proTypeFactory->create($this->_proType);
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param \Magento\Core\Model\Store|int $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        if (null === $store) {
            $store = $this->_storeManager->getStore()->getId();
        }
        $this->_pro->getConfig()->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

   /**
    * Can be used in regular checkout
    *
    * @return bool
    */
   public function canUseCheckout()
   {
       if ($this->_coreStoreConfig->getConfigFlag('payment/hosted_pro/active')
           && !$this->_coreStoreConfig->getConfigFlag('payment/hosted_pro/display_ec')
       ) {
           return false;
       }
       return parent::canUseCheckout();
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
     * @see \Magento\Sales\Model\Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_pro->getConfig()->getPaymentAction();
    }

    /**
     * Check whether payment method can be used
     * @param \Magento\Sales\Model\Quote
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
        return $this->_pro->getConfig()->$field;
    }

    /**
     * Order payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Express
     */
    public function order(\Magento\Object $payment, $amount)
    {
        $this->_placeOrder($payment, $amount);

        $payment->setAdditionalInformation($this->_isOrderPaymentActionKey, true);

        if ($payment->getIsFraudDetected()) {
            return $this;
        }

        $order = $payment->getOrder();
        $orderTransactionId = $payment->getTransactionId();

        $api = $this->_callDoAuthorize($amount, $payment, $payment->getTransactionId());

        $state  = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $status = true;

        $formattedPrice = $order->getBaseCurrency()->formatTxt($amount);
        if ($payment->getIsTransactionPending()) {
            $message = __('The ordering amount of %1 is pending approval on the payment gateway.', $formattedPrice);
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
        } else {
            $message = __('Ordered amount of %1', $formattedPrice);
        }

        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER, null, false, $message);

        $this->_pro->importPaymentInfo($api, $payment);

        if ($payment->getIsTransactionPending()) {
            $message = __(
                'We\'ll authorize the amount of %1 as soon as the payment gateway approves it.',
                $formattedPrice
            );
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
            if ($payment->getIsFraudDetected()) {
                $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
            }
        } else {
            $message = __('The authorized amount is %1.', $formattedPrice);
        }

        $payment->resetTransactionAdditionalInfo();

        $payment->setTransactionId($api->getTransactionId());
        $payment->setParentTransactionId($orderTransactionId);

        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, false,
            $message
        );

        $order->setState($state, $status);

        $payment->setSkipOrderProcessing(true);
        return $this;
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Express
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Express
     */
    public function void(\Magento\Object $payment)
    {
        //Switching to order transaction if needed
        if ($payment->getAdditionalInformation($this->_isOrderPaymentActionKey)
            && !$payment->getVoidOnlyAuthorization()
        ) {
            $orderTransaction = $payment->lookupTransaction(
                false, \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER
            );
            if ($orderTransaction) {
                $payment->setParentTransactionId($orderTransaction->getTxnId());
                $payment->setTransactionId($orderTransaction->getTxnId() . '-void');
            }
        }
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Core\Exception
     * @return \Magento\Paypal\Model\Express
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        $authorizationTransaction = $payment->getAuthorizationTransaction();
        $authorizationPeriod = abs(intval($this->getConfigData('authorization_honor_period')));
        $maxAuthorizationNumber = abs(intval($this->getConfigData('child_authorization_number')));
        $order = $payment->getOrder();
        $isAuthorizationCreated = false;

        if ($payment->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $voided = false;
            if (!$authorizationTransaction->getIsClosed()
                && $this->_isTransactionExpired($authorizationTransaction, $authorizationPeriod)
            ) {
                //Save payment state and configure payment object for voiding
                $isCaptureFinal = $payment->getShouldCloseParentTransaction();
                $payment->setShouldCloseParentTransaction(false);
                $payment->setParentTransactionId($authorizationTransaction->getTxnId());
                $payment->unsTransactionId();
                $payment->setVoidOnlyAuthorization(true);
                $payment->void(new \Magento\Object());

                //Revert payment state after voiding
                $payment->unsAuthorizationTransaction();
                $payment->unsTransactionId();
                $payment->setShouldCloseParentTransaction($isCaptureFinal);
                $voided = true;
            }

            if ($authorizationTransaction->getIsClosed() || $voided) {
                if ($payment->getAdditionalInformation($this->_authorizationCountKey) > $maxAuthorizationNumber - 1) {
                    throw new \Magento\Core\Exception(__('The maximum number of child authorizations is reached.'));
                }
                $api = $this->_callDoAuthorize(
                    $amount,
                    $payment,
                    $authorizationTransaction->getParentTxnId()
                );

                //Adding authorization transaction
                $this->_pro->importPaymentInfo($api, $payment);
                $payment->setTransactionId($api->getTransactionId());
                $payment->setParentTransactionId($authorizationTransaction->getParentTxnId());
                $payment->setIsTransactionClosed(false);

                $formatedPrice = $order->getBaseCurrency()->formatTxt($amount);

                if ($payment->getIsTransactionPending()) {
                    $message = __(
                        'We\'ll authorize the amount of %1 as soon as the payment gateway approves it.',
                        $formatedPrice
                    );
                } else {
                    $message = __('The authorized amount is %1.', $formatedPrice);
                }

                $transaction = $payment->addTransaction(
                    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,
                    null,
                    true,
                    $message
                );

                $payment->setParentTransactionId($api->getTransactionId());
                $isAuthorizationCreated = true;
            }
            //close order transaction if needed
            if ($payment->getShouldCloseParentTransaction()) {
                $orderTransaction = $payment->lookupTransaction(
                    false, \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER
                );

                if ($orderTransaction) {
                    $orderTransaction->setIsClosed(true);
                    $order->addRelatedObject($orderTransaction);
                }
            }
        }

        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }

        if ($isAuthorizationCreated && isset($transaction)) {
            $transaction->setIsClosed(true);
        }

        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Express
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Express
     */
    public function cancel(\Magento\Object $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see \Magento\Sales\Model\Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/express/start');
    }

    /**
     * Fetch transaction details info
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Validate RP data
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     */
    public function validateRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile)
    {
        return $this->_pro->validateRecurringProfile($profile);
    }

    /**
     * Submit RP to the gateway
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     * @param \Magento\Payment\Model\Info $paymentInfo
     */
    public function submitRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile,
        \Magento\Payment\Model\Info $paymentInfo
    ) {
        $token = $paymentInfo->getAdditionalInformation(
            \Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN
        );
        $profile->setToken($token);
        $this->_pro->submitRecurringProfile($profile, $paymentInfo);
    }

    /**
     * Fetch RP details
     *
     * @param string $referenceId
     * @param \Magento\Object $result
     */
    public function getRecurringProfileDetails($referenceId, \Magento\Object $result)
    {
        return $this->_pro->getRecurringProfileDetails($referenceId, $result);
    }

    /**
     * Whether can get recurring profile details
     */
    public function canGetRecurringProfileDetails()
    {
        return true;
    }

    /**
     * Update RP data
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     */
    public function updateRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile)
    {
        return $this->_pro->updateRecurringProfile($profile);
    }

    /**
     * Manage status
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     */
    public function updateRecurringProfileStatus(\Magento\Payment\Model\Recurring\Profile $profile)
    {
        return $this->_pro->updateRecurringProfileStatus($profile);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  \Magento\Payment\Model\Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);
        $key = \Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT;
        if (is_array($data)) {
            $this->getInfoInstance()->setAdditionalInformation($key, isset($data[$key]) ? $data[$key] : null);
        }
        elseif ($data instanceof \Magento\Object) {
            $this->getInfoInstance()->setAdditionalInformation($key, $data->getData($key));
        }
        return $result;
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Express
     */
    protected function _placeOrder(\Magento\Sales\Model\Order\Payment $payment, $amount)
    {
        $order = $payment->getOrder();

        // prepare api call
        $token = $payment->getAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN);
        $parameters = array('params' => array($order));
        $api = $this->_pro->getApi()
            ->setToken($token)
            ->setPayerId($payment->
                getAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
            ->setAmount($amount)
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
            ->setInvNum($order->getIncrementId())
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setPaypalCart($this->_cartFactory->create($parameters))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled);
        if ($order->getIsVirtual()) {
            $api->setAddress($order->getBillingAddress())->setSuppressShipping(true);
        } else {
            $api->setAddress($order->getShippingAddress());
            $api->setBillingAddress($order->getBillingAddress());
        }

        // call api and get details from it
        $api->callDoExpressCheckoutPayment();

        $this->_importToPayment($api, $payment);
        return $this;
    }

    /**
     * Import payment info to payment
     *
     * @param \Magento\Paypal\Model\Api\Nvp
     * @param \Magento\Sales\Model\Order\Payment
     */
    protected function _importToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0)
            ->setAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT,
                $api->getRedirectRequired());

        if ($api->getBillingAgreementId()) {
            $payment->setBillingAgreementData(array(
                'billing_agreement_id'  => $api->getBillingAgreementId(),
                'method_code'           => \Magento\Paypal\Model\Config::METHOD_BILLING_AGREEMENT
            ));
        }

        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Object $payment)
    {
        if ($payment instanceof \Magento\Sales\Model\Order\Invoice
            || $payment instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            return false;
        }
        $info = $this->getInfoInstance();
        if ($info->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $orderTransaction = $info->lookupTransaction(
                false, \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);
            if ($orderTransaction) {
                $info->setParentTransactionId($orderTransaction->getTxnId());
            }
        }

        return $this->_canVoid;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        $payment = $this->getInfoInstance();
        $this->_pro->getConfig()->setStoreId($payment->getOrder()->getStore()->getId());

        if ($payment->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $orderTransaction = $payment->lookupTransaction(false,
                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);
            if ($orderTransaction->getIsClosed()) {
                return false;
            }

            $orderValidPeriod = abs(intval($this->getConfigData('order_valid_period')));

            $dateCompass = new \DateTime($orderTransaction->getCreatedAt());
            $dateCompass->modify('+' . $orderValidPeriod . ' days');
            $currentDate = new \DateTime();

            if ($currentDate > $dateCompass || $orderValidPeriod == 0) {
                return false;
            }
        }
        return $this->_canCapture;
    }

    /**
     * Call DoAuthorize
     *
     * @param int $amount
     * @param \Magento\Object $payment
     * @param string $parentTransactionId
     * @return \Magento\Paypal\Model\Api\AbstractApi
     */
    protected function _callDoAuthorize($amount, $payment, $parentTransactionId)
    {
        $api = $this->_pro->resetApi()->getApi()
            ->setAmount($amount)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setTransactionId($parentTransactionId)
            ->callDoAuthorization();

        $payment->setAdditionalInformation($this->_authorizationCountKey,
            $payment->getAdditionalInformation($this->_authorizationCountKey) + 1);

        return $api;
    }

    /**
     * Check transaction for expiration in PST
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @param int $period
     * @return boolean
     */
    protected function _isTransactionExpired(\Magento\Sales\Model\Order\Payment\Transaction $transaction, $period)
    {
        $period = intval($period);
        if (0 == $period) {
            return true;
        }

        $transactionClosingDate = new \DateTime($transaction->getCreatedAt(), new \DateTimeZone('GMT'));
        $transactionClosingDate->setTimezone(new \DateTimeZone('US/Pacific'));
        /**
         * 11:49:00 PayPal transactions closing time
         */
        $transactionClosingDate->setTime(11, 49, 00);
        $transactionClosingDate->modify('+' . $period . ' days');

        $currentTime = new \DateTime(null, new \DateTimeZone('US/Pacific'));

        if ($currentTime > $transactionClosingDate) {
            return true;
        }

        return false;
    }
}
