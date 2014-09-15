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

use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Payflow Link payment gateway model
 */
class Payflowlink extends \Magento\Paypal\Model\Payflowpro
{
    /**
     * Default layout template
     */
    const LAYOUT_TEMPLATE = 'minLayout';

    /**
     * Controller for callback urls
     *
     * @var string
     */
    protected $_callbackController = 'payflow';

    /**
     * Response params mappings
     *
     * @var array
     */
    protected $_responseParamsMappings = array(
        'firstname' => 'billtofirstname',
        'lastname' => 'billtolastname',
        'address' => 'billtostreet',
        'city' => 'billtocity',
        'state' => 'billtostate',
        'zip' => 'billtozip',
        'country' => 'billtocountry',
        'phone' => 'billtophone',
        'email' => 'billtoemail',
        'nametoship' => 'shiptofirstname',
        'addresstoship' => 'shiptostreet',
        'citytoship' => 'shiptocity',
        'statetoship' => 'shiptostate',
        'ziptoship' => 'shiptozip',
        'countrytoship' => 'shiptocountry',
        'phonetoship' => 'shiptophone',
        'emailtoship' => 'shiptoemail',
        'faxtoship' => 'shiptofax',
        'method' => 'tender',
        'cscmatch' => 'cvv2match',
        'type' => 'trxtype'
    );

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
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Paypal\Model\Payflow\RequestFactory $requestFactory
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param OrderSender $orderSender
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Centinel\Model\Service $centinelService,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Paypal\Model\Payflow\RequestFactory $requestFactory,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        OrderSender $orderSender,
        array $data = array()
    ) {
        $this->_requestFactory = $requestFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_orderFactory = $orderFactory;
        $this->_requestHttp = $requestHttp;
        $this->_websiteFactory = $websiteFactory;
        $this->orderSender = $orderSender;
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $logger,
            $moduleList,
            $localeDate,
            $centinelService,
            $storeManager,
            $configFactory,
            $mathRandom,
            $httpClientFactory,
            $data
        );
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
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $storeId = $this->_storeManager->getStore($this->getStore())->getId();
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_configFactory->create()->setStoreId($storeId);
        if (\Magento\Payment\Model\Method\AbstractMethod::isAvailable(
            $quote
        ) && $config->isMethodAvailable(
            $this->getCode()
        )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\Object $stateObject
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
                $response = $this->_postRequest($request);
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
     * Fill response with data.
     *
     * @param array $postData
     * @return $this
     */
    public function setResponseData(array $postData)
    {
        foreach ($postData as $key => $val) {
            $this->getResponse()->setData(strtolower($key), $val);
        }
        foreach ($this->_responseParamsMappings as $originKey => $key) {
            $data = $this->getResponse()->getData($key);
            if (isset($data)) {
                $this->getResponse()->setData($originKey, $data);
            }
        }
        // process AVS data separately
        $avsAddr = $this->getResponse()->getData('avsaddr');
        $avsZip = $this->getResponse()->getData('avszip');
        if (isset($avsAddr) && isset($avsZip)) {
            $this->getResponse()->setData('avsdata', $avsAddr . $avsZip);
        }
        // process Name separately
        $firstnameParameter = $this->getResponse()->getData('billtofirstname');
        $lastnameParameter = $this->getResponse()->getData('billtolastname');
        if (isset($firstnameParameter) && isset($lastnameParameter)) {
            $this->getResponse()->setData('name', $firstnameParameter . ' ' . $lastnameParameter);
        }
        return $this;
    }

    /**
     * Operate with order using data from $_POST which came from Silent Post Url.
     *
     * @param array $responseData
     * @return void
     * @throws \Magento\Framework\Model\Exception In case of validation error or order creation error
     */
    public function process($responseData)
    {
        $debugData = array('response' => $responseData);
        $this->_debug($debugData);

        $this->setResponseData($responseData);
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
     * @throws \Magento\Framework\Model\Exception
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
            $fraudMessage = $this->_getFraudMessage() ? $response->getFraudMessage() : $response->getRespmsg();
            $payment->setIsTransactionPending(
                true
            )->setIsFraudDetected(
                true
            )->setAdditionalInformation(
                'paypal_fraud_filters',
                $fraudMessage
            );
        }

        if ($response->getAvsdata() && strstr(substr($response->getAvsdata(), 0, 2), 'N')) {
            $payment->setAdditionalInformation('paypal_avs_code', substr($response->getAvsdata(), 0, 2));
        }
        if ($response->getCvv2match() && $response->getCvv2match() != 'Y') {
            $payment->setAdditionalInformation('paypal_cvv2_match', $response->getCvv2match());
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
            $this->_quoteFactory->create()->load($order->getQuoteId())->setIsActive(false)->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception(__('We cannot send the new order email.'));
        }
    }

    /**
     * Get fraud message from response
     *
     * @return string|false
     */
    protected function _getFraudMessage()
    {
        if ($this->getResponse()->getFpsPrexmldata()) {
            $xml = new \SimpleXMLElement($this->getResponse()->getFpsPrexmldata());
            $this->getResponse()->setFraudMessage((string)$xml->rule->triggeredMessage);
            return $this->getResponse()->getFraudMessage();
        }

        return false;
    }

    /**
     * Check response from Payflow gateway.
     *
     * @return false|\Magento\Sales\Model\Order in case of validation passed
     * @throws \Magento\Framework\Model\Exception In other cases
     */
    protected function _getOrderFromResponse()
    {
        $response = $this->getResponse();
        $order = $this->_orderFactory->create()->loadByIncrementId($response->getInvnum());

        if ($this->_getSecureSilentPostHash(
            $order->getPayment()
        ) != $response->getUser2() || $this->_code != $order->getPayment()->getMethodInstance()->getCode()
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
            throw new \Magento\Framework\Model\Exception($response->getRespmsg());
        }

        $amountCompared = $response->getAmt() == $order->getPayment()->getBaseAmountAuthorized() ? true : false;
        if (!$order->getId() ||
            $order->getState() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT ||
            !$amountCompared
        ) {
            throw new \Magento\Framework\Model\Exception($this->_formatStr(self::RESPONSE_ERROR_MSG, 'Order'));
        }

        $fetchData = $this->fetchTransactionInfo($order->getPayment(), $response->getPnref());
        if (!isset($fetchData['custref']) || $fetchData['custref'] != $order->getIncrementId()) {
            throw new \Magento\Framework\Model\Exception($this->_formatStr(self::RESPONSE_ERROR_MSG, 'Transaction'));
        }

        return $order;
    }

    /**
     * Build request for getting token
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Framework\Object
     */
    protected function _buildTokenRequest(\Magento\Sales\Model\Order\Payment $payment)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setCreatesecuretoken(
            'Y'
        )->setSecuretokenid(
            $this->_generateSecureTokenId()
        )->setTrxtype(
            $this->_getTrxTokenType()
        )->setAmt(
            $this->_formatStr('%.2F', $payment->getOrder()->getBaseTotalDue())
        )->setCurrency(
            $payment->getOrder()->getBaseCurrencyCode()
        )->setInvnum(
            $payment->getOrder()->getIncrementId()
        )->setCustref(
            $payment->getOrder()->getIncrementId()
        )->setPonum(
            $payment->getOrder()->getId()
        );
        //This is PaPal issue with taxes and shipping
        //->setSubtotal($this->_formatStr('%.2F', $payment->getOrder()->getBaseSubtotal()))
        //->setTaxamt($this->_formatStr('%.2F', $payment->getOrder()->getBaseTaxAmount()))
        //->setFreightamt($this->_formatStr('%.2F', $payment->getOrder()->getBaseShippingAmount()));


        $order = $payment->getOrder();
        if (empty($order)) {
            return $request;
        }

        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $request->setFirstname(
                $billing->getFirstname()
            )->setLastname(
                $billing->getLastname()
            )->setStreet(
                implode(' ', $billing->getStreet())
            )->setCity(
                $billing->getCity()
            )->setState(
                $billing->getRegionCode()
            )->setZip(
                $billing->getPostcode()
            )->setCountry(
                $billing->getCountry()
            )->setEmail(
                $order->getCustomerEmail()
            );
        }
        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $request->setShiptofirstname(
                $shipping->getFirstname()
            )->setShiptolastname(
                $shipping->getLastname()
            )->setShiptostreet(
                implode(' ', $shipping->getStreet())
            )->setShiptocity(
                $shipping->getCity()
            )->setShiptostate(
                $shipping->getRegionCode()
            )->setShiptozip(
                $shipping->getPostcode()
            )->setShiptocountry(
                $shipping->getCountry()
            );
        }
        //pass store Id to request
        $request->setUser1($order->getStoreId())->setUser2($this->_getSecureSilentPostHash($payment));

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
        if ($response->getUser1()) {
            return (int)$response->getUser1();
        }
        return $this->_storeManager->getStore($this->getStore())->getId();
    }

    /**
     * Return request object with basic information for gateway request
     *
     * @param \Magento\Framework\Object $payment
     * @return \Magento\Paypal\Model\Payflow\Request
     */
    protected function _buildBasicRequest(\Magento\Framework\Object $payment)
    {
        /** @var \Magento\Paypal\Model\Payflow\Request $request */
        $request = $this->_requestFactory->create();
        $cscEditable = $this->getConfigData('csc_editable');
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_configFactory->create();
        $request->setUser(
            $this->getConfigData('user', $this->_getStoreId())
        )->setVendor(
            $this->getConfigData('vendor', $this->_getStoreId())
        )->setPartner(
            $this->getConfigData('partner', $this->_getStoreId())
        )->setPwd(
            $this->getConfigData('pwd', $this->_getStoreId())
        )->setVerbosity(
            $this->getConfigData('verbosity', $this->_getStoreId())
        )->setData(
            'BNCODE',
            $config->getBuildNotationCode()
        )->setTender(
            self::TENDER_CC
        )->setCancelurl(
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
     * Return unique value for secure token id
     *
     * @return string
     */
    protected function _generateSecureTokenId()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Format values
     *
     * @param mixed $format
     * @param mixed $string
     * @return string
     */
    protected function _formatStr($format, $string)
    {
        return sprintf($format, $string);
    }

    /**
     * If response is failed throw exception
     * Set token data in payment object
     *
     * @param \Magento\Framework\Object $response
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _processTokenErrors($response, $payment)
    {
        if (!$response->getSecuretoken() &&
            $response->getResult() != self::RESPONSE_CODE_APPROVED &&
            $response->getResult() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER
        ) {
            throw new \Magento\Framework\Model\Exception($response->getRespmsg());
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
     * Add transaction with correct transaction Id
     *
     * @deprecated since 1.6.2.0
     * @param \Magento\Framework\Object $payment
     * @param string $txnId
     * @return void
     */
    protected function _addTransaction($payment, $txnId)
    {
    }

    /**
     * Initialize request
     *
     * @deprecated since 1.6.2.0
     * @param \Magento\Framework\Object $payment
     * @param mixed $amount
     * @return $this
     */
    protected function _initialize(\Magento\Framework\Object $payment, $amount)
    {
        return $this;
    }

    /**
     * Check whether order review has enough data to initialize
     *
     * @deprecated since 1.6.2.0
     * @param mixed|null $token
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function prepareOrderReview($token = null)
    {
    }

    /**
     * Additional authorization logic for Account Verification
     *
     * @deprecated since 1.6.2.0
     * @param \Magento\Framework\Object $payment
     * @param mixed $amount
     * @param \Magento\Paypal\Model\Payment\Transaction $transaction
     * @param string $txnId
     * @return $this
     */
    protected function _authorize(\Magento\Framework\Object $payment, $amount, $transaction, $txnId)
    {
        return $this;
    }

    /**
     * Operate with order or quote using information from silent post
     *
     * @deprecated since 1.6.2.0
     * @param \Magento\Framework\Object $document
     * @return void
     */
    protected function _process(\Magento\Framework\Object $document)
    {
    }

    /**
     * Check Transaction
     *
     * @deprecated since 1.6.2.0
     * @param \Magento\Paypal\Model\Payment\Transaction $transaction
     * @param mixed $amount
     * @return $this
     */
    protected function _checkTransaction($transaction, $amount)
    {
        return $this;
    }

    /**
     * Check response from Payflow gateway.
     *
     * @deprecated since 1.6.2.0
     * @return \Magento\Sales\Model\AbstractModel in case of validation passed
     * @throws \Magento\Framework\Model\Exception In other cases
     */
    protected function _getDocumentFromResponse()
    {
        return null;
    }

    /**
     * Get callback controller
     *
     * @return string
     */
    public function getCallbackController()
    {
        return $this->_callbackController;
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
            $websiteUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, $secure);
        }

        return $websiteUrl . 'paypal/' . $this->getCallbackController() . '/' . $actionName;
    }
}
