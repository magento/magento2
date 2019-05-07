<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\TransparentInterface;

/**
 * Authorize.net DirectPost payment method model.
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class Directpost extends \Magento\Authorizenet\Model\Authorizenet implements TransparentInterface, ConfigInterface
{
    const METHOD_CODE = 'authorizenet_directpost';

    /**
     * @var string
     */
    protected $_formBlockType = \Magento\Payment\Block\Transparent\Info::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Magento\Authorizenet\Block\Adminhtml\Order\View\Info\PaymentDetails::class;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response
     */
    protected $response;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * Order factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $psrLogger;

    /**
     * @var \Magento\Sales\Api\PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Authorizenet\Helper\Data $dataHelper
     * @param \Magento\Authorizenet\Model\Directpost\Request\Factory $requestFactory
     * @param \Magento\Authorizenet\Model\Directpost\Response\Factory $responseFactory
     * @param \Magento\Authorizenet\Model\TransactionService $transactionService
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Sales\Api\PaymentFailuresInterface|null $paymentFailures
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Authorizenet\Helper\Data $dataHelper,
        \Magento\Authorizenet\Model\Directpost\Request\Factory $requestFactory,
        \Magento\Authorizenet\Model\Directpost\Response\Factory $responseFactory,
        \Magento\Authorizenet\Model\TransactionService $transactionService,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Sales\Api\PaymentFailuresInterface $paymentFailures = null
    ) {
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->response = $responseFactory->create();
        $this->orderSender = $orderSender;
        $this->transactionRepository = $transactionRepository;
        $this->_code = static::METHOD_CODE;
        $this->paymentFailures = $paymentFailures ? : ObjectManager::getInstance()
            ->get(\Magento\Sales\Api\PaymentFailuresInterface::class);

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $dataHelper,
            $requestFactory,
            $responseFactory,
            $transactionService,
            $httpClientFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set data helper
     *
     * @param \Magento\Authorizenet\Helper\Data $dataHelper
     * @return void
     */
    public function setDataHelper(\Magento\Authorizenet\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Send authorize request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param  float $amount
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
    }

    /**
     * Send capture request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }

        $payment->setAmount($amount);

        if ($payment->getParentTransactionId()) {
            $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
            $payment->setXTransId($this->getRealParentTransactionId($payment));
        } else {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
        }

        $result = $this->getResponse();
        if (empty($result->getData())) {
            $request = $this->buildRequest($payment);
            $result = $this->postRequest($request);
        }

        return $this->processCapture($result, $payment);
    }

    /**
     * Process capture request
     *
     * @param \Magento\Authorizenet\Model\Directpost\Response $result
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processCapture($result, $payment)
    {
        switch ($result->getXResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
            case self::RESPONSE_CODE_HELD:
                if (in_array(
                    $result->getXResponseReasonCode(),
                    [
                            self::RESPONSE_REASON_CODE_APPROVED,
                            self::RESPONSE_REASON_CODE_PENDING_REVIEW,
                            self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED
                        ]
                )
                ) {
                    if (!$payment->getParentTransactionId()
                        || $result->getXTransId() != $payment->getParentTransactionId()
                    ) {
                        $payment->setTransactionId($result->getXTransId());
                    }
                    $payment->setIsTransactionClosed(0)
                        ->setTransactionAdditionalInfo(
                            self::REAL_TRANSACTION_ID_KEY,
                            $result->getXTransId()
                        );
                    return $this;
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment capturing error.'));
        }
    }

    /**
     * Void the payment through gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid transaction ID.'));
        }

        $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
        $payment->setXTransId($this->getRealParentTransactionId($payment));

        $request = $this->buildRequest($payment);
        $result = $this->postRequest($request);

        switch ($result->getXResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getXResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    if ($result->getXTransId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($result->getXTransId());
                    }
                    $payment->setIsTransactionClosed(1)
                        ->setShouldCloseParentTransaction(1)
                        ->setTransactionAdditionalInfo(self::REAL_TRANSACTION_ID_KEY, $result->getXTransId());
                    return $this;
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment voiding error.'));
        }
    }

    /**
     * Refund the amount need to decode last 4 digits for request.
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $last4 = $payment->getCcLast4();
        $payment->setCcLast4($payment->decrypt($last4));
        try {
            $this->processRefund($payment, $amount);
        } catch (\Exception $e) {
            $payment->setCcLast4($last4);
            throw $e;
        }
        $payment->setCcLast4($last4);
        return $this;
    }

    /**
     * Refund the amount with transaction id
     *
     * @param \Magento\Framework\DataObject $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processRefund(\Magento\Framework\DataObject $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for refund.'));
        }

        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid transaction ID.'));
        }

        $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
        $payment->setAmount($amount);
        $payment->setXTransId($this->getRealParentTransactionId($payment));

        $request = $this->buildRequest($payment);
        $result = $this->postRequest($request);

        switch ($result->getXResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getXResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    if ($result->getXTransId() != $payment->getParentTransactionId()) {
                        $payment->setTransactionId($result->getXTransId());
                    }
                    $payment->setIsTransactionClosed(true)
                        ->setTransactionAdditionalInfo(self::REAL_TRANSACTION_ID_KEY, $result->getXTransId());
                    return $this;
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Framework\Exception\LocalizedException(
                    $this->dataHelper->wrapGatewayError($result->getXResponseReasonText())
                );
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment refunding error.'));
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
        return $this->dataHelper->getRelayUrl($storeId);
    }

    /**
     * Return response.
     *
     * @return \Magento\Authorizenet\Model\Directpost\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        $requestType = null;
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE:
                $requestType = self::REQUEST_TYPE_AUTH_ONLY;
                //intentional
            case self::ACTION_AUTHORIZE_CAPTURE:
                $requestType = $requestType ?: self::REQUEST_TYPE_AUTH_CAPTURE;
                $payment = $this->getInfoInstance();
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setAnetTransType($requestType);
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
        $request = $this->requestFactory->create()
            ->setConstantData($this)
            ->setDataFromOrder($order, $this)
            ->signRequestData();

        $this->_debug(['request' => $request->getData()]);

        return $request;
    }

    /**
     * Fill response with data.
     *
     * @param array $postData
     * @return $this
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
     * @throws \Magento\Framework\Exception\LocalizedException In case of validation error
     */
    public function validateResponse()
    {
        $response = $this->getResponse();
        $hashConfigKey = !empty($response->getData('x_SHA2_Hash')) ? 'signature_key' : 'trans_md5';

        //hash check
        if (!$response->isValidHash($this->getConfigData($hashConfigKey), $this->getConfigData('login'))
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The transaction was declined because the response hash validation failed.')
            );
        }

        return true;
    }

    /**
     * Operate with order using data from $_POST which came from authorize.net by Relay URL.
     *
     * @param array $responseData data from Authorize.net from $_POST
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException In case of validation error or order creation error
     */
    public function process(array $responseData)
    {
        $this->_debug(['response' => $responseData]);

        $this->setResponseData($responseData);

        //check MD5 error or others response errors
        //throws exception on false.
        $this->validateResponse();

        $response = $this->getResponse();
        $responseText = $this->dataHelper->wrapGatewayError($response->getXResponseReasonText());
        $isError = false;
        if ($this->getOrderIncrementId()) {
            $order = $this->getOrderFromResponse();
            //check payment method
            $payment = $order->getPayment();
            if (!$payment || $payment->getMethod() != $this->getCode()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This payment didn\'t work out because we can\'t find this order.')
                );
            }
            if ($order->getId()) {
                //operate with order
                $this->processOrder($order);
            } else {
                $isError = true;
            }
        } else {
            $isError = true;
        }

        if ($isError) {
            $responseText = $responseText && !$response->isApproved()
                ? $responseText
                : __('This payment didn\'t work out because we can\'t find this order.');
            throw new \Magento\Framework\Exception\LocalizedException($responseText);
        }
    }

    /**
     * Fill payment with credit card data from response from Authorize.net.
     *
     * @param \Magento\Framework\DataObject $payment
     * @return void
     */
    protected function fillPaymentByResponse(\Magento\Framework\DataObject $payment)
    {
        $response = $this->getResponse();
        $payment->setTransactionId($response->getXTransId())
            ->setParentTransactionId(null)
            ->setIsTransactionClosed(0)
            ->setTransactionAdditionalInfo(self::REAL_TRANSACTION_ID_KEY, $response->getXTransId());

        if ($response->getXMethod() == self::REQUEST_METHOD_CC) {
            $payment->setCcAvsStatus($response->getXAvsCode())
                ->setCcLast4($payment->encrypt(substr($response->getXAccountNumber(), -4)));
        }

        if ($response->getXResponseCode() == self::RESPONSE_CODE_HELD) {
            $payment->setIsTransactionPending(true)
                ->setIsFraudDetected(true);
        }

        $additionalInformationKeys = explode(',', $this->getValue('paymentInfoKeys'));
        foreach ($additionalInformationKeys as $paymentInfoKey) {
            $paymentInfoValue = $response->getDataByKey($paymentInfoKey);
            if ($paymentInfoValue !== null) {
                $payment->setAdditionalInformation($paymentInfoKey, $paymentInfoValue);
            }
        }
    }

    /**
     * Check response code came from Authorize.net.
     *
     * @return true in case of Approved response
     * @throws \Magento\Framework\Exception\LocalizedException In case of Declined or Error response from Authorize.net
     */
    public function checkResponseCode()
    {
        switch ($this->getResponse()->getXResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
            case self::RESPONSE_CODE_HELD:
                return true;
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                $errorMessage = $this->dataHelper->wrapGatewayError($this->getResponse()->getXResponseReasonText());
                $order = $this->getOrderFromResponse();
                $this->paymentFailures->handle((int)$order->getQuoteId(), (string)$errorMessage);
                throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('There was a payment authorization error.')
                );
        }
    }

    /**
     * Check transaction id came from Authorize.net
     *
     * @return true in case of right transaction id
     * @throws \Magento\Framework\Exception\LocalizedException In case of bad transaction id.
     */
    public function checkTransId()
    {
        if (!$this->getResponse()->getXTransId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please enter a transaction ID to authorize this payment.')
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
    protected function matchAmount($amount)
    {
        return sprintf('%.2F', $amount) == sprintf('%.2F', $this->getResponse()->getXAmount());
    }

    /**
     * Operate with order using information from Authorize.net.
     *
     * Authorize order or authorize and capture it.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processOrder(\Magento\Sales\Model\Order $order)
    {
        try {
            $this->checkResponseCode();
            $this->checkTransId();
        } catch (\Exception $e) {
            //decline the order (in case of wrong response code) but don't return money to customer.
            $message = $e->getMessage();
            $this->declineOrder($order, $message, false);

            throw $e;
        }

        $response = $this->getResponse();

        //create transaction. need for void if amount will not match.
        $payment = $order->getPayment();
        $this->fillPaymentByResponse($payment);
        $payment->getMethodInstance()->setIsInitializeNeeded(false);
        $payment->getMethodInstance()->setResponseData($response->getData());
        $this->processPaymentFraudStatus($payment);
        $payment->place();
        $this->addStatusComment($payment);
        $order->save();

        //match amounts. should be equals for authorization.
        //decline the order if amount does not match.
        if (!$this->matchAmount($payment->getBaseAmountAuthorized())) {
            $message = __(
                'Something went wrong: the paid amount doesn\'t match the order amount.'
                . ' Please correct this and try again.'
            );
            $this->declineOrder($order, $message, true);
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }

        try {
            if (!$response->hasOrderSendConfirmation() || $response->getOrderSendConfirmation()) {
                $this->orderSender->send($order);
            }

            $quote = $this->quoteRepository->get($order->getQuoteId())->setIsActive(false);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            // do not cancel order if we couldn't send email
        }
    }

    /**
     * Process fraud status
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    protected function processPaymentFraudStatus(\Magento\Sales\Model\Order\Payment $payment)
    {
        try {
            $fraudDetailsResponse = $payment->getMethodInstance()
                ->fetchTransactionFraudDetails($this->getResponse()->getXTransId());
            $fraudData = $fraudDetailsResponse->getData();

            if (empty($fraudData)) {
                $payment->setIsFraudDetected(false);
                return $this;
            }

            $fdsFilterAction = (string)$fraudDetailsResponse->getFdsFilterAction();
            if ($this->fdsFilterActionIsReportOnly($fdsFilterAction) === false) {
                $payment->setIsFraudDetected(true);
            }

            $payment->setAdditionalInformation('fraud_details', $fraudData);
        } catch (\Exception $e) {
            //this request is optional
        }

        return $this;
    }

    /**
     * Add status comment to history
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    protected function addStatusComment(\Magento\Sales\Model\Order\Payment $payment)
    {
        try {
            $transactionId = $this->getResponse()->getXTransId();
            $data = $this->transactionService->getTransactionDetails($this, $transactionId);
            $transactionStatus = (string)$data->transaction->transactionStatus;
            $fdsFilterAction = (string)$data->transaction->FDSFilterAction;

            if ($payment->getIsTransactionPending()) {
                $message = 'Amount of %1 is pending approval on the gateway.<br/>'
                    . 'Transaction "%2" status is "%3".<br/>'
                    . 'Transaction FDS Filter Action is "%4"';
                $message = __(
                    $message,
                    $payment->getOrder()->getBaseCurrency()->formatTxt($this->getResponse()->getXAmount()),
                    $transactionId,
                    $this->dataHelper->getTransactionStatusLabel($transactionStatus),
                    $this->dataHelper->getFdsFilterActionLabel($fdsFilterAction)
                );
                $payment->getOrder()->addStatusHistoryComment($message);
            }
        } catch (\Exception $e) {
            $this->getPsrLogger()->critical($e);
            //this request is optional
        }
        return $this;
    }

    /**
     * Register order cancellation. Return money to customer if needed.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $message
     * @param bool $voidPayment
     * @return void
     */
    protected function declineOrder(\Magento\Sales\Model\Order $order, $message = '', $voidPayment = true)
    {
        try {
            $response = $this->getResponse();
            if ($voidPayment
                && $response->getXTransId()
                && strtoupper($response->getXType()) == self::REQUEST_TYPE_AUTH_ONLY
            ) {
                $order->getPayment()
                      ->setTransactionId(null)
                      ->setParentTransactionId($response->getXTransId())
                      ->void($response);
            }
            $order->registerCancellation($message)->save();
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $order ]);
        } catch (\Exception $e) {
            //quiet decline
            $this->getPsrLogger()->critical($e);
        }
    }

    /**
     * Return additional information`s transaction_id value of parent transaction model
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function getRealParentTransactionId($payment)
    {
        $transaction = $this->transactionRepository->getByTransactionId(
            $payment->getParentTransactionId(),
            $payment->getId(),
            $payment->getOrder()->getId()
        );
        return $transaction->getAdditionalInformation(self::REAL_TRANSACTION_ID_KEY);
    }

    /**
     * {inheritdoc}
     */
    public function getConfigInterface()
    {
        return $this;
    }

    /**
     * Getter for specified value according to set payment method code
     *
     * @param mixed $key
     * @param mixed $storeId
     * @return mixed
     */
    public function getValue($key, $storeId = null)
    {
        return $this->getConfigData($key, $storeId);
    }

    /**
     * Set initialization requirement state
     *
     * @param bool $isInitializeNeeded
     * @return void
     */
    public function setIsInitializeNeeded($isInitializeNeeded = true)
    {
        $this->_isInitializeNeeded = (bool)$isInitializeNeeded;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canCapture()
    {
        return !$this->isGatewayActionsLocked($this->getInfoInstance());
    }

    /**
     * Fetch transaction details info
     *
     * Update transaction info if there is one placing transaction only
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        $transaction = $this->transactionRepository->getByTransactionId(
            $transactionId,
            $payment->getId(),
            $payment->getOrder()->getId()
        );

        $response = $this->getTransactionResponse($transactionId);
        if ($response->getXResponseCode() == self::RESPONSE_CODE_APPROVED) {
            if ($response->getTransactionStatus() == 'voided') {
                $payment->setIsTransactionDenied(true);
                $payment->setIsTransactionClosed(true);
                $transaction->close();
            } else {
                $transaction->setAdditionalInformation(self::TRANSACTION_FRAUD_STATE_KEY, false);
                $payment->setIsTransactionApproved(true);
            }
        } elseif ($response->getXResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED) {
            $payment->setIsTransactionDenied(true);
        }
        $this->addStatusCommentOnUpdate($payment, $response, $transactionId);
        return $response->getData();
    }

    /**
     * Add status comment on update
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Magento\Framework\DataObject $response
     * @param string $transactionId
     * @return $this
     */
    protected function addStatusCommentOnUpdate(
        \Magento\Sales\Model\Order\Payment $payment,
        \Magento\Framework\DataObject $response,
        $transactionId
    ) {
        if ($payment->getIsTransactionApproved()) {
            $message = __(
                'Transaction %1 has been approved. Amount %2. Transaction status is "%3"',
                $transactionId,
                $payment->getOrder()->getBaseCurrency()->formatTxt($payment->getAmountAuthorized()),
                $this->dataHelper->getTransactionStatusLabel($response->getTransactionStatus())
            );
            $payment->getOrder()->addStatusHistoryComment($message);
        } elseif ($payment->getIsTransactionDenied()) {
            $message = __(
                'Transaction %1 has been voided/declined. Transaction status is "%2". Amount %3.',
                $transactionId,
                $this->dataHelper->getTransactionStatusLabel($response->getTransactionStatus()),
                $payment->getOrder()->getBaseCurrency()->formatTxt($payment->getAmountAuthorized())
            );
            $payment->getOrder()->addStatusHistoryComment($message);
        }
        return $this;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function setMethodCode($methodCode)
    {
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
    }

    /**
     * This function returns full transaction details for a specified transaction ID.
     *
     * @param string $transactionId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @link http://www.authorize.net/support/ReportingGuide_XML.pdf
     * @link http://developer.authorize.net/api/transaction_details/
     */
    protected function getTransactionResponse($transactionId)
    {
        $responseXmlDocument = $this->transactionService->getTransactionDetails($this, $transactionId);

        $response = new \Magento\Framework\DataObject();
        $response->setXResponseCode((string)$responseXmlDocument->transaction->responseCode)
            ->setXResponseReasonCode((string)$responseXmlDocument->transaction->responseReasonCode)
            ->setTransactionStatus((string)$responseXmlDocument->transaction->transactionStatus);

        return $response;
    }

    /**
     * Get psr logger.
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 100.1.0
     */
    private function getPsrLogger()
    {
        if (null === $this->psrLogger) {
            $this->psrLogger = ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->psrLogger;
    }

    /**
     * Fetch order by increment id from response.
     *
     * @return \Magento\Sales\Model\Order
     */
    private function getOrderFromResponse(): \Magento\Sales\Model\Order
    {
        if (!$this->order) {
            $this->order = $this->orderFactory->create();

            if ($incrementId = $this->getOrderIncrementId()) {
                $this->order = $this->order->loadByIncrementId($incrementId);
            }
        }

        return $this->order;
    }

    /**
     * Fetch order increment id from response.
     *
     * @return string
     */
    private function getOrderIncrementId(): string
    {
        return $this->getResponse()->getXInvoiceNum();
    }

    /**
     * Checks if filter action is Report Only.
     *
     * Transactions that trigger this filter are processed as normal,
     * but are also reported in the Merchant Interface as triggering this filter.
     *
     * @param string $fdsFilterAction
     * @return bool
     */
    private function fdsFilterActionIsReportOnly($fdsFilterAction)
    {
        return $fdsFilterAction === (string)$this->dataHelper->getFdsFilterActionLabel('report');
    }
}
