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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

use Magento\Paypal\Model\Express\Checkout as ExpressCheckout;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Quote;

/**
 * PayPal Express Module
 */
class Express extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Express\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * Website Payments Pro instance
     *
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    /**
     * Payment additional information key for payment action
     *
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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Model\ExceptionFactory
     */
    protected $_exception;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param ProFactory $proFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Model\ExceptionFactory $exception
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        ProFactory $proFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ExceptionFactory $exception,
        array $data = array()
    ) {
        parent::__construct($eventManager, $paymentData, $scopeConfig, $logAdapterFactory, $data);
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartFactory = $cartFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_exception = $exception;

        $proInstance = array_shift($data);
        if ($proInstance && $proInstance instanceof \Magento\Paypal\Model\Pro) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = $proFactory->create();
        }
        $this->_pro->setMethod($this->_code);
        $this->_setApiProcessableErrors();
    }

    /**
     * Set processable error codes to API model
     *
     * @return \Magento\Paypal\Model\Api\Nvp
     */
    protected function _setApiProcessableErrors()
    {
        return $this->_pro->getApi()->setProcessableErrors(
            [
                ApiProcessableException::API_INTERNAL_ERROR,
                ApiProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
                ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
                ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
                ApiProcessableException::API_TRANSACTION_EXPIRED,
                ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
                ApiProcessableException::API_COUNTRY_FILTER_DECLINE,
                ApiProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
                ApiProcessableException::API_OTHER_FILTER_DECLINE
            ]
        );
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param \Magento\Store\Model\Store|int $store
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
        if ($this->_scopeConfig->isSetFlag(
            'payment/hosted_pro/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->_scopeConfig->isSetFlag(
            'payment/hosted_pro/display_ec',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
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
     * @param Quote|null $quote
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
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->_pro->getConfig()->getConfigValue($field);
    }

    /**
     * Order payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function order(\Magento\Framework\Object $payment, $amount)
    {
        $paypalTransactionData = $this->_checkoutSession->getPaypalTransactionData();
        if (!is_array($paypalTransactionData)) {
            $this->_placeOrder($payment, $amount);
        } else {
            $this->_importToPayment($this->_pro->getApi()->setData($paypalTransactionData), $payment);
        }

        $payment->setAdditionalInformation($this->_isOrderPaymentActionKey, true);

        if ($payment->getIsFraudDetected()) {
            return $this;
        }

        $order = $payment->getOrder();
        $orderTransactionId = $payment->getTransactionId();

        $api = $this->_callDoAuthorize($amount, $payment, $orderTransactionId);

        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $status = true;

        $formattedPrice = $order->getBaseCurrency()->formatTxt($amount);
        if ($payment->getIsTransactionPending()) {
            $message = __('The ordering amount of %1 is pending approval on the payment gateway.', $formattedPrice);
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
        } else {
            $message = __('Ordered amount of %1', $formattedPrice);
        }

        $payment->addTransaction(Transaction::TYPE_ORDER, null, false, $message);

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

        $payment->addTransaction(Transaction::TYPE_AUTH, null, false, $message);

        $order->setState($state, $status);

        $payment->setSkipOrderProcessing(true);
        return $this;
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Framework\Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return $this
     */
    public function void(\Magento\Framework\Object $payment)
    {
        //Switching to order transaction if needed
        if ($payment->getAdditionalInformation(
            $this->_isOrderPaymentActionKey
        ) && !$payment->getVoidOnlyAuthorization()
        ) {
            $orderTransaction = $payment->lookupTransaction(false, Transaction::TYPE_ORDER);
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
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function capture(\Magento\Framework\Object $payment, $amount)
    {
        $authorizationTransaction = $payment->getAuthorizationTransaction();
        $authorizationPeriod = abs(intval($this->getConfigData('authorization_honor_period')));
        $maxAuthorizationNumber = abs(intval($this->getConfigData('child_authorization_number')));
        $order = $payment->getOrder();
        $isAuthorizationCreated = false;

        if ($payment->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $voided = false;
            if (!$authorizationTransaction->getIsClosed() && $this->_isTransactionExpired(
                $authorizationTransaction,
                $authorizationPeriod
            )
            ) {
                //Save payment state and configure payment object for voiding
                $isCaptureFinal = $payment->getShouldCloseParentTransaction();
                $payment->setShouldCloseParentTransaction(false);
                $payment->setParentTransactionId($authorizationTransaction->getTxnId());
                $payment->unsTransactionId();
                $payment->setVoidOnlyAuthorization(true);
                $payment->void(new \Magento\Framework\Object());

                //Revert payment state after voiding
                $payment->unsAuthorizationTransaction();
                $payment->unsTransactionId();
                $payment->setShouldCloseParentTransaction($isCaptureFinal);
                $voided = true;
            }

            if ($authorizationTransaction->getIsClosed() || $voided) {
                if ($payment->getAdditionalInformation($this->_authorizationCountKey) > $maxAuthorizationNumber - 1) {
                    $this->_exception->create(
                        ['message' => __('The maximum number of child authorizations is reached.')]
                    );
                }
                $api = $this->_callDoAuthorize($amount, $payment, $authorizationTransaction->getParentTxnId());

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

                $transaction = $payment->addTransaction(Transaction::TYPE_AUTH, null, true, $message);

                $payment->setParentTransactionId($api->getTransactionId());
                $isAuthorizationCreated = true;
            }
            //close order transaction if needed
            if ($payment->getShouldCloseParentTransaction()) {
                $orderTransaction = $payment->lookupTransaction(false, Transaction::TYPE_ORDER);

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
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function refund(\Magento\Framework\Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Framework\Object $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param \Magento\Payment\Model\Info|Payment $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param \Magento\Payment\Model\Info|Payment $payment
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
     * @param \Magento\Payment\Model\Info|Payment $payment
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
     * @see Quote\Payment::getCheckoutRedirectUrl()
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
     * @return Api\Nvp
     */
    public function getApi()
    {
        return $this->_pro->getApi();
    }

    /**
     * Assign data to info model instance
     *
     * @param array|\Magento\Framework\Object $data
     * @return \Magento\Payment\Model\Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);
        $key = ExpressCheckout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT;
        if (is_array($data)) {
            $this->getInfoInstance()->setAdditionalInformation($key, isset($data[$key]) ? $data[$key] : null);
        } elseif ($data instanceof \Magento\Framework\Object) {
            $this->getInfoInstance()->setAdditionalInformation($key, $data->getData($key));
        }
        return $result;
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param Payment $payment
     * @param float $amount
     * @return $this
     */
    protected function _placeOrder(Payment $payment, $amount)
    {
        $order = $payment->getOrder();

        // prepare api call
        $token = $payment->getAdditionalInformation(ExpressCheckout::PAYMENT_INFO_TRANSPORT_TOKEN);

        $cart = $this->_cartFactory->create(array('salesModel' => $order));

        $api = $this->getApi()->setToken(
            $token
        )->setPayerId(
            $payment->getAdditionalInformation(ExpressCheckout::PAYMENT_INFO_TRANSPORT_PAYER_ID)
        )->setAmount(
            $amount
        )->setPaymentAction(
            $this->_pro->getConfig()->getConfigValue('paymentAction')
        )->setNotifyUrl(
            $this->_urlBuilder->getUrl('paypal/ipn/')
        )->setInvNum(
            $order->getIncrementId()
        )->setCurrencyCode(
            $order->getBaseCurrencyCode()
        )->setPaypalCart(
            $cart
        )->setIsLineItemsEnabled(
            $this->_pro->getConfig()->getConfigValue('lineItemsEnabled')
        );
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
     * @param Nvp $api
     * @param Payment $payment
     * @return void
     */
    protected function _importToPayment($api, $payment)
    {
        $payment->setTransactionId(
            $api->getTransactionId()
        )->setIsTransactionClosed(
            0
        )->setAdditionalInformation(
            ExpressCheckout::PAYMENT_INFO_TRANSPORT_REDIRECT,
            $api->getRedirectRequired()
        );

        if ($api->getBillingAgreementId()) {
            $payment->setBillingAgreementData(
                array(
                    'billing_agreement_id' => $api->getBillingAgreementId(),
                    'method_code' => \Magento\Paypal\Model\Config::METHOD_BILLING_AGREEMENT
                )
            );
        }

        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Check void availability
     *
     * @param \Magento\Framework\Object $payment
     * @return bool
     */
    public function canVoid(\Magento\Framework\Object $payment)
    {
        if ($payment instanceof \Magento\Sales\Model\Order\Invoice ||
            $payment instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            return false;
        }
        $info = $this->getInfoInstance();
        if ($info->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $orderTransaction = $info->lookupTransaction(false, Transaction::TYPE_ORDER);
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
            $orderTransaction = $payment->lookupTransaction(false, Transaction::TYPE_ORDER);
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
     * @param \Magento\Framework\Object $payment
     * @param string $parentTransactionId
     * @return \Magento\Paypal\Model\Api\AbstractApi
     */
    protected function _callDoAuthorize($amount, $payment, $parentTransactionId)
    {
        $apiData = $this->_pro->getApi()->getData();
        foreach ($apiData as $k => $v) {
            if (is_object($v)) {
                unset($apiData[$k]);
            }
        }
        $this->_checkoutSession->setPaypalTransactionData($apiData);
        $this->_pro->resetApi();
        $api = $this->_setApiProcessableErrors()
            ->setAmount($amount)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setTransactionId($parentTransactionId)
            ->callDoAuthorization();

        $payment->setAdditionalInformation(
            $this->_authorizationCountKey,
            $payment->getAdditionalInformation($this->_authorizationCountKey) + 1
        );

        return $api;
    }

    /**
     * Check transaction for expiration in PST
     *
     * @param Transaction $transaction
     * @param int $period
     * @return bool
     */
    protected function _isTransactionExpired(Transaction $transaction, $period)
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
