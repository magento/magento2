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
 * Authorize.net DirectPost payment method model.
 */
namespace Magento\Authorizenet\Model;

class Directpost extends \Magento\Paygate\Model\Authorizenet
{
    protected $_code  = 'authorizenet_directpost';
    protected $_formBlockType = 'Magento\Authorizenet\Block\Directpost\Form';
    protected $_infoBlockType = 'Magento\Payment\Block\Info';

    /**
     * Availability options
     */
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;
    protected $_isInitializeNeeded      = true;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\RequestFactory
     */
    protected $_requestFactory;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response
     */
    protected $_response;

    /**
     * Construct
     *
     * @param \Magento\Paygate\Model\Authorizenet\CardsFactory $cardsFactory
     * @param \Magento\Paygate\Model\Authorizenet\RequestFactory $parentRequestFactory
     * @param \Magento\Paygate\Model\Authorizenet\ResultFactory $resultFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Core\Model\Session\AbstractSession $session
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Paygate\Helper\Data $paygateData
     * @param \Magento\App\ModuleListInterface $moduleList
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Authorizenet\Model\Directpost\RequestFactory $requestFactory
     * @param \Magento\Authorizenet\Model\Directpost\Response $response
     * @param array $data
     */
    public function __construct(
        \Magento\Paygate\Model\Authorizenet\CardsFactory $cardsFactory,
        \Magento\Paygate\Model\Authorizenet\RequestFactory $parentRequestFactory,
        \Magento\Paygate\Model\Authorizenet\ResultFactory $resultFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Core\Model\Session\AbstractSession $session,
        \Magento\Core\Model\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Paygate\Helper\Data $paygateData,
        \Magento\App\ModuleListInterface $moduleList,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Centinel\Model\Service $centinelService,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Authorizenet\Model\Directpost\RequestFactory $requestFactory,
        \Magento\Authorizenet\Model\Directpost\Response $response,
        array $data = array()
    ) {
        parent::__construct($cardsFactory, $parentRequestFactory, $resultFactory, $orderFactory, $session,
            $logger, $eventManager, $paygateData, $moduleList, $coreStoreConfig, $paymentData,
            $logAdapterFactory, $locale, $centinelService, $data);
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_requestFactory = $requestFactory;
        $this->_response = $response;
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return  bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Send authorize request to gateway
     *
     * @param  \Magento\Object $payment
     * @param  decimal $amount
     * @return \Magento\Paygate\Model\Authorizenet
     * @throws \Magento\Core\Exception
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
    }

    /**
     * Send capture request to gateway
     *
     * @param \Magento\Object $payment
     * @param decimal $amount
     * @return \Magento\Authorizenet\Model\Directpost
     * @throws \Magento\Core\Exception
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Core\Exception(__('Invalid amount for capture.'));
        }

        $payment->setAmount($amount);

        if ($payment->getParentTransactionId()) {
            $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
            $payment->setXTransId($this->_getRealParentTransactionId($payment));
        } else {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
        }

        $request= $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    if (!$payment->getParentTransactionId() ||
                        $result->getTransactionId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($result->getTransactionId());
                    }
                    $payment
                        ->setIsTransactionClosed(0)
                        ->setTransactionAdditionalInfo($this->_realTransactionIdKey, $result->getTransactionId());
                    return $this;
                }
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                throw new \Magento\Core\Exception(__('Payment capturing error.'));
        }
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
     * Check void availability
     *
     * @param   \Magento\Object $invoicePayment
     * @return  bool
     */
    public function canVoid(\Magento\Object $payment)
    {
        return $this->_canVoid;
    }

    /**
     * Void the payment through gateway
     *
     * @param \Magento\Object $payment
     * @return \Magento\Authorizenet\Model\Directpost
     * @throws \Magento\Core\Exception
     */
    public function void(\Magento\Object $payment)
    {
        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Core\Exception(__('Invalid transaction ID.'));
        }

        $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
        $payment->setXTransId($this->_getRealParentTransactionId($payment));

        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    if ($result->getTransactionId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($result->getTransactionId());
                    }
                    $payment
                        ->setIsTransactionClosed(1)
                        ->setShouldCloseParentTransaction(1)
                        ->setTransactionAdditionalInfo($this->_realTransactionIdKey, $result->getTransactionId());
                    return $this;
                }
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                throw new \Magento\Core\Exception(__('Payment voiding error.'));
        }
    }

    /**
     * Set capture transaction ID to invoice for informational purposes
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function processInvoice($invoice, $payment)
    {
        return \Magento\Payment\Model\Method\AbstractMethod::processInvoice($invoice, $payment);
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        return \Magento\Payment\Model\Method\AbstractMethod::processCreditmemo($creditmemo, $payment);
    }

    /**
     * Refund the amount
     * Need to decode Last 4 digits for request.
     *
     * @param \Magento\Object $payment
     * @param decimal $amount
     * @return \Magento\Authorizenet\Model\Directpost
     * @throws \Magento\Core\Exception
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        $last4 = $payment->getCcLast4();
        $payment->setCcLast4($payment->decrypt($last4));
        try {
            $this->_refund($payment, $amount);
        } catch (\Exception $e) {
            $payment->setCcLast4($last4);
            throw $e;
        }
        $payment->setCcLast4($last4);
        return $this;
    }

    /**
     * refund the amount with transaction id
     *
     * @param string $payment \Magento\Object object
     * @return \Magento\Authorizenet\Model\Directpost
     * @throws \Magento\Core\Exception
     */
    protected function _refund(\Magento\Object $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Core\Exception(__('Invalid amount for refund.'));
        }

        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Core\Exception(__('Invalid transaction ID.'));
        }

        $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
        $payment->setAmount($amount);
        $payment->setXTransId($this->_getRealParentTransactionId($payment));

        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    if ($result->getTransactionId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($result->getTransactionId());
                    }
                    $shouldCloseCaptureTransaction = $payment->getOrder()->canCreditmemo() ? 0 : 1;
                    $payment
                         ->setIsTransactionClosed(1)
                         ->setShouldCloseParentTransaction($shouldCloseCaptureTransaction)
                         ->setTransactionAdditionalInfo($this->_realTransactionIdKey, $result->getTransactionId());
                    return $this;
                }
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Core\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                throw new \Magento\Core\Exception(__('Payment refunding error.'));
        }
    }

    /**
     * Get CGI url
     *
     * @return string
     */
    public function getCgiUrl()
    {
        $uri = $this->getConfigData('cgi_url');
        return $uri ? $uri : self::CGI_URL;
    }

    /**
     * Return URL on which Authorize.net server will return payment result data in hidden request.
     *
     * @param int $storeId
     * @return string
     */
    public function getRelayUrl($storeId = null)
    {
        if ($storeId == null && $this->getStore()) {
            $storeId = $this->getStore();
        }
        return $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_LINK)
            . 'authorizenet/directpost_payment/response';
    }

    /**
     * Return response.
     *
     * @return \Magento\Authorizenet\Model\Directpost\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE:
            case self::ACTION_AUTHORIZE_CAPTURE:
                $payment = $this->getInfoInstance();
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
                $payment->setAmountAuthorized($order->getTotalDue());

                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, 'pending_payment', '', false);

                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus('pending_payment');
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Generate request object and fill its fields from Quote or Order object
     *
     * @param \Magento\Sales\Model\Order $order Quote or order object.
     * @return \Magento\Authorizenet\Model\Directpost\Request
     */
    public function generateRequestFromOrder(\Magento\Sales\Model\Order $order)
    {
        $request = $this->_requestFactory->create()
            ->setConstantData($this)
            ->setDataFromOrder($order, $this)
            ->signRequestData();

        $this->_debug(array('request' => $request->getData()));

        return $request;
    }

    /**
     * Fill response with data.
     *
     * @param array $postData
     * @return \Magento\Authorizenet\Model\Directpost
     */
    public function setResponseData(array $postData)
    {
        $this->getResponse()->setData($postData);
        return $this;
    }

    /**
     * Validate response data. Needed in controllers.
     *
     * @return bool true in case of validation success.
     * @throws \Magento\Core\Exception in case of validation error
     */
    public function validateResponse()
    {
        $response = $this->getResponse();
        //md5 check
        if (!$this->getConfigData('trans_md5') || !$this->getConfigData('login') ||
            !$response->isValidHash($this->getConfigData('trans_md5'), $this->getConfigData('login'))
        ) {
            throw new \Magento\Core\Exception(
                __('The transaction was declined because the response hash validation failed.')
            );
        }
        return true;
    }

    /**
     * Operate with order using data from $_POST which came from authorize.net by Relay URL.
     *
     * @param array $responseData data from Authorize.net from $_POST
     * @throws \Magento\Core\Exception in case of validation error or order creation error
     */
    public function process(array $responseData)
    {
        $debugData = array(
            'response' => $responseData
        );
        $this->_debug($debugData);

        $this->setResponseData($responseData);

        //check MD5 error or others response errors
        //throws exception on false.
        $this->validateResponse();

        $response = $this->getResponse();
        //operate with order
        $orderIncrementId = $response->getXInvoiceNum();
        $responseText = $this->_wrapGatewayError($response->getXResponseReasonText());
        $isError = false;
        if ($orderIncrementId) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
            //check payment method
            $payment = $order->getPayment();
            if (!$payment || $payment->getMethod() != $this->getCode()) {
                throw new \Magento\Core\Exception(
                    __('This payment didn\'t work out because we can\'t find this order.')
                );
            }
            if ($order->getId() &&  $order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                //operate with order
                $this->_authOrder($order);
            } else {
                $isError = true;
            }
        } else {
            $isError = true;
        }

        if ($isError) {
            throw new \Magento\Core\Exception(
                ($responseText && !$response->isApproved()) ?
                $responseText :
                __('This payment didn\'t work out because we can\'t find this order.')
            );
        }
    }

    /**
     * Fill payment with credit card data from response from Authorize.net.
     *
     * @param \Magento\Object $payment
     */
    protected function _fillPaymentByResponse(\Magento\Object $payment)
    {
        $response = $this->getResponse();
        $payment->setTransactionId($response->getXTransId())
            ->setParentTransactionId(null)
            ->setIsTransactionClosed(0)
            ->setTransactionAdditionalInfo($this->_realTransactionIdKey, $response->getXTransId());

        if ($response->getXMethod() == self::REQUEST_METHOD_CC) {
            $payment->setCcAvsStatus($response->getXAvsCode())
                ->setCcLast4($payment->encrypt(substr($response->getXAccountNumber(), -4)));
        }
    }

    /**
     * Check response code came from authorize.net.
     *
     * @return true in case of Approved response
     * @throws \Magento\Core\Exception in case of Declined or Error response from Authorize.net
     */
    public function checkResponseCode()
    {
        switch ($this->getResponse()->getXResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                return true;
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Core\Exception($this->_wrapGatewayError($this->getResponse()->getXResponseReasonText()));
            default:
                throw new \Magento\Core\Exception(__('There was a payment authorization error.'));
        }
    }

    /**
     * Check transaction id came from Authorize.net
     *
     * @return true in case of right transaction id
     * @throws \Magento\Core\Exception in case of bad transaction id.
     */
    public function checkTransId()
    {
        if (!$this->getResponse()->getXTransId()) {
            throw new \Magento\Core\Exception(
                __('This payment was not authorized because the transaction ID field is empty.')
            );
        }
        return true;
    }

    /**
     * Compare amount with amount from the response from Authorize.net.
     *
     * @param float $amount
     * @return bool
     */
    protected function _matchAmount($amount)
    {
         return sprintf('%.2F', $amount) == sprintf('%.2F', $this->getResponse()->getXAmount());
    }

    /**
     * Operate with order using information from Authorize.net.
     * Authorize order or authorize and capture it.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function _authOrder(\Magento\Sales\Model\Order $order)
    {
        try {
            $this->checkResponseCode();
            $this->checkTransId();
        } catch (\Exception $e) {
            //decline the order (in case of wrong response code) but don't return money to customer.
            $message = $e->getMessage();
            $this->_declineOrder($order, $message, false);
            throw $e;
        }

        $response = $this->getResponse();

        //create transaction. need for void if amount will not match.
        $payment = $order->getPayment();
        $this->_fillPaymentByResponse($payment);

        $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);

        // Set transaction approval message
        $message = __(
            'Amount of %1 approved by payment gateway. Transaction ID: "%2".',
            $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized()),
            $response->getXTransId()
        );

        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $orderStatus = $this->getConfigData('order_status');
        if (!$orderStatus || $order->getIsVirtual()) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $order->setState($orderState, $orderStatus ? $orderStatus : true, $message, false)
            ->save();

        //match amounts. should be equals for authorization.
        //decline the order if amount does not match.
        if (!$this->_matchAmount($payment->getBaseAmountAuthorized())) {
            $message = __('Something went wrong: the paid amount doesn\'t match the order amount. Please correct this and try again.');
            $this->_declineOrder($order, $message, true);
            throw new \Magento\Core\Exception($message);
        }

        //capture order using AIM if needed
        $this->_captureOrder($order);

        try {
            if (!$response->hasOrderSendConfirmation() || $response->getOrderSendConfirmation()) {
                $order->sendNewOrderEmail();
            }

            $this->_quoteFactory->create()->load($order->getQuoteId())
                ->setIsActive(false)
                ->save();
        } catch (\Exception $e) {} // do not cancel order if we couldn't send email
    }

    /**
     * Register order cancellation. Return money to customer if needed.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $message
     * @param bool $voidPayment
     */
    protected function _declineOrder(\Magento\Sales\Model\Order $order, $message = '', $voidPayment = true)
    {
        try {
            $response = $this->getResponse();
            if ($voidPayment &&
                $response->getXTransId() &&
                strtoupper($response->getXType()) == self::REQUEST_TYPE_AUTH_ONLY
            ) {
                $order->getPayment()
                    ->setTransactionId(null)
                    ->setParentTransactionId($response->getXTransId())
                    ->void();
            }
            $order->registerCancellation($message)
                ->save();
        } catch (\Exception $e) {
            //quiet decline
            $this->_logger->logException($e);
        }
    }

    /**
     * Capture order's payment using AIM.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function _captureOrder(\Magento\Sales\Model\Order $order)
    {
        $payment = $order->getPayment();
        if ($payment->getAdditionalInformation('payment_type') == self::ACTION_AUTHORIZE_CAPTURE) {
            try {
                $payment->setTransactionId(null)
                    ->setParentTransactionId($this->getResponse()->getXTransId())
                    ->capture(null);

                // set status from config for AUTH_AND_CAPTURE orders.
                if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                    $orderStatus = $this->getConfigData('order_status');
                    if (!$orderStatus || $order->getIsVirtual()) {
                        $orderStatus = $order->getConfig()
                                ->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    }
                    if ($orderStatus) {
                        $order->setStatus($orderStatus);
                    }
                }

                $order->save();
            } catch (\Exception $e) {
                $this->_logger->logException($e);
                //if we couldn't capture order, just leave it as NEW order.
            }
        }
    }

    /**
     * Return additional information`s transaction_id value of parent transaction model
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function _getRealParentTransactionId($payment)
    {
        $transaction = $payment->getTransaction($payment->getParentTransactionId());
        return $transaction->getAdditionalInformation($this->_realTransactionIdKey);
    }
}
