<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Payflow Link payment gateway model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payflowlink extends \Magento\Paypal\Model\Payflowpro
{
    /**
     * Default layout template
     */
    const LAYOUT_TEMPLATE = 'mobile';

    /**
     * Controller for callback urls
     *
     * @var string
     */
    protected $_callbackController = 'payflow';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Payflow\Link\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payflow\Link\Info';

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
    protected $_isInitializeNeeded = true;

    /**
     * Request & response model
     *
     * @var \Magento\Paypal\Model\Payflow\Request
     */
    protected $_response;

    /**
     * Gateway request URL
     */
    const TRANSACTION_PAYFLOW_URL = 'https://payflowlink.paypal.com/';

    /**
     * Error message
     */
    const RESPONSE_ERROR_MSG = 'Payment error. %s was not found.';

    /**
     * Key for storing secure hash in additional information of payment model
     *
     * @var string
     */
    protected $_secureSilentPostHashKey = 'secure_silent_post_hash';

    /**
     * @var \Magento\Paypal\Model\Payflow\RequestFactory
     */
    protected $_requestFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ConfigInterfaceFactory $configFactory
     * @param Payflow\Service\Gateway $gateway
     * @param HandlerInterface $errorHandler
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param Payflow\RequestFactory $requestFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param OrderSender $orderSender
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigInterfaceFactory $configFactory,
        \Magento\Paypal\Model\Payflow\Service\Gateway $gateway,
        HandlerInterface $errorHandler,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Paypal\Model\Payflow\RequestFactory $requestFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        OrderSender $orderSender,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_requestFactory = $requestFactory;
        $this->quoteRepository = $quoteRepository;
        $this->_orderFactory = $orderFactory;
        $this->_requestHttp = $requestHttp;
        $this->_websiteFactory = $websiteFactory;
        $this->orderSender = $orderSender;
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
            $storeManager,
            $configFactory,
            $gateway,
            $errorHandler,
            $resource,
            $resourceCollection,
            $data
        );
        $this->mathRandom = $mathRandom;
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return true
     */
    public function validate()
    {
        return true;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return AbstractMethod::isAvailable($quote) && $this->getConfig()->isMethodAvailable($this->getCode());
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->getConfigData('active', $storeId);
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH:
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_SALE:
                $payment = $this->getInfoInstance();
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
                $this->_generateSecureSilentPostHash($payment);
                $request = $this->_buildTokenRequest($payment);
                $response = $this->postRequest($request, $this->getConfig());
                $this->_processTokenErrors($response, $payment);

                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);

                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus('pending_payment');
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Return response model.
     *
     * @return \Magento\Paypal\Model\Payflow\Request
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_requestFactory->create();
        }

        return $this->_response;
    }

    /**
     * Operate with order using data from $_POST which came from Silent Post Url.
     *
     * @param array $responseData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException In case of validation error or order creation error
     */
    public function process($responseData)
    {
        $debugData = ['response' => $responseData];
        $this->_debug($debugData);

        $this->mapGatewayResponse($responseData, $this->getResponse());
        $order = $this->_getOrderFromResponse();
        if ($order) {
            $this->_processOrder($order);
        }
    }

    /**
     * Operate with order using information from silent post
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processOrder(\Magento\Sales\Model\Order $order)
    {
        $response = $this->getResponse();
        $payment = $order->getPayment();
        $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
        $canSendNewOrderEmail = true;

        if ($response->getResult() == self::RESPONSE_CODE_FRAUDSERVICE_FILTER ||
            $response->getResult() == self::RESPONSE_CODE_DECLINED_BY_FILTER
        ) {
            $canSendNewOrderEmail = false;

            $payment->setIsTransactionPending(
                true
            )->setIsFraudDetected(
                true
            );

            $fraudMessage = $response->getData('respmsg');
            if ($response->getData('fps_prexmldata')) {
                $xml = new \SimpleXMLElement($response->getData('fps_prexmldata'));
                $fraudMessage = (string)$xml->rule->triggeredMessage;
            }
            $payment->setAdditionalInformation(
                Info::PAYPAL_FRAUD_FILTERS,
                $fraudMessage
            );
        }

        if ($response->getData('avsdata') && strstr(substr($response->getData('avsdata'), 0, 2), 'N')) {
            $payment->setAdditionalInformation(Info::PAYPAL_AVS_CODE, substr($response->getData('avsdata'), 0, 2));
        }

        if ($response->getData('cvv2match') && $response->getData('cvv2match') != 'Y') {
            $payment->setAdditionalInformation(Info::PAYPAL_CVV_2_MATCH, $response->getData('cvv2match'));
        }

        switch ($response->getType()) {
            case self::TRXTYPE_AUTH_ONLY:
                $payment->registerAuthorizationNotification($payment->getBaseAmountAuthorized());
                break;
            case self::TRXTYPE_SALE:
                $payment->registerCaptureNotification($payment->getBaseAmountAuthorized());
                break;
            default:
                break;
        }
        $order->save();

        try {
            if ($canSendNewOrderEmail) {
                $this->orderSender->send($order);
            }
            $quote = $this->quoteRepository->get($order->getQuoteId())->setIsActive(false);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We cannot send the new order email.'));
        }
    }

    /**
     * Check response from Payflow gateway.
     *
     * @return false|\Magento\Sales\Model\Order in case of validation passed
     * @throws \Magento\Framework\Exception\LocalizedException In other cases
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getOrderFromResponse()
    {
        $response = $this->getResponse();
        $order = $this->_orderFactory->create()->loadByIncrementId($response->getInvnum());

        if ($this->_getSecureSilentPostHash(
            $order->getPayment()
        ) != $response->getData('user2') || $this->_code != $order->getPayment()->getMethodInstance()->getCode()
        ) {
            return false;
        }

        if ($response->getResult() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER &&
            $response->getResult() != self::RESPONSE_CODE_DECLINED_BY_FILTER &&
            $response->getResult() != self::RESPONSE_CODE_APPROVED
        ) {
            if ($order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
                $order->registerCancellation($response->getRespmsg())->save();
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($response->getRespmsg()));
        }

        $amountCompared = $response->getAmt() == $order->getPayment()->getBaseAmountAuthorized() ? true : false;
        if (!$order->getId() ||
            $order->getState() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT ||
            !$amountCompared
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment error. %value was not found.', ['value' => 'Order'])
            );
        }

        $fetchData = $this->fetchTransactionInfo($order->getPayment(), $response->getPnref());
        if (!isset($fetchData['custref']) || $fetchData['custref'] != $order->getIncrementId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment error. %value was not found.', ['value' => 'Transaction'])
            );
        }

        return $order;
    }

    /**
     * Build request for getting token
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Framework\DataObject
     */
    protected function _buildTokenRequest(\Magento\Sales\Model\Order\Payment $payment)
    {
        $request = $this->buildBasicRequest();
        $request->setCreatesecuretoken('Y')
            ->setSecuretokenid($this->mathRandom->getUniqueHash())
            ->setTrxtype($this->_getTrxTokenType());

        $order = $payment->getOrder();
        $request->setAmt(sprintf('%.2F', $order->getBaseTotalDue()))
            ->setCurrency($order->getBaseCurrencyCode());
        $this->addRequestOrderInfo($request, $order);

        $request = $this->fillCustomerContacts($order, $request);
        //pass store Id to request
        $request->setData('USER1', $order->getStoreId());
        $request->setData('USER2', $this->_getSecureSilentPostHash($payment));

        return $request;
    }

    /**
     * Get store id from response if exists
     * or default
     *
     * @return int
     */
    protected function _getStoreId()
    {
        $response = $this->getResponse();
        if ($response->getData('user1')) {
            return (int)$response->getData('user1');
        }
        return $this->storeManager->getStore($this->getStore())->getId();
    }

    /**
     * Return request object with basic information for gateway request
     *
     * @return \Magento\Paypal\Model\Payflow\Request
     */
    public function buildBasicRequest()
    {
        /** @var \Magento\Paypal\Model\Payflow\Request $request */
        $request = $this->_requestFactory->create();
        $cscEditable = $this->getConfigData('csc_editable');

        $data = parent::buildBasicRequest();

        $request->setData($data->getData());

        $request->setCancelurl(
            $this->_getCallbackUrl('cancelPayment')
        )->setErrorurl(
            $this->_getCallbackUrl('returnUrl')
        )->setSilentpost(
            'TRUE'
        )->setSilentposturl(
            $this->_getCallbackUrl('silentPost')
        )->setReturnurl(
            $this->_getCallbackUrl('returnUrl')
        )->setTemplate(
            self::LAYOUT_TEMPLATE
        )->setDisablereceipt(
            'TRUE'
        )->setCscrequired(
            $cscEditable && $this->getConfigData('csc_required') ? 'TRUE' : 'FALSE'
        )->setCscedit(
            $cscEditable ? 'TRUE' : 'FALSE'
        )->setEmailcustomer(
            $this->getConfigData('email_confirmation') ? 'TRUE' : 'FALSE'
        )->setUrlmethod(
            $this->getConfigData('url_method')
        );
        return $request;
    }

    /**
     * Get payment action code
     *
     * @return string
     */
    protected function _getTrxTokenType()
    {
        switch ($this->getConfigData('payment_action')) {
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH:
                return self::TRXTYPE_AUTH_ONLY;
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_SALE:
                return self::TRXTYPE_SALE;
            default:
                break;
        }
    }

    /**
     * If response is failed throw exception
     * Set token data in payment object
     *
     * @param \Magento\Framework\DataObject $response
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processTokenErrors($response, $payment)
    {
        if (!$response->getSecuretoken() &&
            $response->getResult() != self::RESPONSE_CODE_APPROVED &&
            $response->getResult() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__($response->getRespmsg()));
        } else {
            $payment->setAdditionalInformation(
                'secure_token_id',
                $response->getSecuretokenid()
            )->setAdditionalInformation(
                'secure_token',
                $response->getSecuretoken()
            );
        }
    }

    /**
     * Return secure hash value for silent post request
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function _getSecureSilentPostHash($payment)
    {
        return $payment->getAdditionalInformation($this->_secureSilentPostHashKey);
    }

    /**
     * Generate end return new secure hash value
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function _generateSecureSilentPostHash($payment)
    {
        $secureHash = md5($this->mathRandom->getRandomString(10));
        $payment->setAdditionalInformation($this->_secureSilentPostHashKey, $secureHash);
        return $secureHash;
    }

    /**
     * Get callback url
     *
     * @param string $actionName
     * @return string
     */
    protected function _getCallbackUrl($actionName)
    {
        if ($this->_requestHttp->getParam('website')) {
            /** @var $website \Magento\Store\Model\Website */
            $website = $this->_websiteFactory->create()->load($this->_requestHttp->getParam('website'));
            $secure = $this->_scopeConfig->isSetFlag(
                \Magento\Store\Model\Store::XML_PATH_SECURE_IN_FRONTEND,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultStore()
            );
            $path = $secure ? \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL : \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL;
            $websiteUrl = $this->_scopeConfig->getValue(
                $path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultStore()
            );
        } else {
            $secure = $this->_scopeConfig->isSetFlag(
                \Magento\Store\Model\Store::XML_PATH_SECURE_IN_FRONTEND,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $websiteUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, $secure);
        }

        return $websiteUrl . 'paypal/' . $this->_callbackController . '/' . $actionName;
    }
}
