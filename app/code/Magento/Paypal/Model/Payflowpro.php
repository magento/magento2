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

use Magento\Sales\Model\Order\Payment;

/**
 * Payflow Pro payment gateway model
 */
class Payflowpro extends \Magento\Payment\Model\Method\Cc
{
    /**
     * Transaction action codes
     */
    const TRXTYPE_AUTH_ONLY = 'A';

    const TRXTYPE_SALE = 'S';

    const TRXTYPE_CREDIT = 'C';

    const TRXTYPE_DELAYED_CAPTURE = 'D';

    const TRXTYPE_DELAYED_VOID = 'V';

    const TRXTYPE_DELAYED_VOICE = 'F';

    const TRXTYPE_DELAYED_INQUIRY = 'I';

    const TRXTYPE_ACCEPT_DENY       = 'U';

    const UPDATEACTION_APPROVED = 'APPROVE';

    const UPDATEACTION_DECLINED_BY_MERCHANT = 'FPS_MERCHANT_DECLINE';

    /**
     * Tender type codes
     */
    const TENDER_CC = 'C';

    /**
     * Gateway request URLs
     */
    const TRANSACTION_URL = 'https://payflowpro.paypal.com/transaction';

    const TRANSACTION_URL_TEST_MODE = 'https://pilot-payflowpro.paypal.com/transaction';

    /**#@+
     * Response code
     */
    const RESPONSE_CODE_APPROVED = 0;

    const RESPONSE_CODE_INVALID_AMOUNT = 4;

    const RESPONSE_CODE_FRAUDSERVICE_FILTER = 126;

    const RESPONSE_CODE_DECLINED = 12;

    const RESPONSE_CODE_DECLINED_BY_FILTER = 125;

    const RESPONSE_CODE_DECLINED_BY_MERCHANT = 128;

    const RESPONSE_CODE_CAPTURE_ERROR = 111;

    const RESPONSE_CODE_VOID_ERROR = 108;

    /**#@-*/

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_PAYFLOWPRO;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

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
    protected $_canUseInternal = true;

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
    protected $_canSaveCc = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isProxy = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * Gateway request timeout
     *
     * @var int
     */
    protected $_clientTimeout = 45;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = array('user', 'pwd', 'acct', 'expdate', 'cvv2');

    /**
     * Centinel cardinal fields map
     *
     * @var string[]
     */
    protected $_centinelFieldMap = array(
        'centinel_mpivendor' => 'MPIVENDOR3DS',
        'centinel_authstatus' => 'AUTHSTATUS3DS',
        'centinel_cavv' => 'CAVV',
        'centinel_eci' => 'ECI',
        'centinel_xid' => 'XID'
    );

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

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
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
        $this->mathRandom = $mathRandom;
        $this->_httpClientFactory = $httpClientFactory;
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $logger,
            $moduleList,
            $localeDate,
            $centinelService,
            $data
        );
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
        if (parent::isAvailable($quote) && $config->isMethodAvailable($this->getCode())) {
            return true;
        }
        return false;
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see \Magento\Sales\Model\Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        switch ($this->getConfigData('payment_action')) {
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_SALE:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
            default:
                break;
        }
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
        $request = $this->_buildPlaceRequest($payment, $amount);
        $request->setTrxtype(self::TRXTYPE_AUTH_ONLY);
        $this->_setReferenceTransaction($payment, $request);
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response->getResultCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                $payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                break;
            default:
                break;
        }
        return $this;
    }

    /**
     * Get capture amount
     *
     * @param float $amount
     * @return float
     */
    protected function _getCaptureAmount($amount)
    {
        $infoInstance = $this->getInfoInstance();
        $amountToPay = round($amount, 2);
        $authorizedAmount = round($infoInstance->getAmountAuthorized(), 2);
        return $amountToPay != $authorizedAmount ? $amountToPay : 0;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Framework\Object $payment, $amount)
    {
        if ($payment->getReferenceTransactionId()) {
            $request = $this->_buildPlaceRequest($payment, $amount);
            $request->setTrxtype(self::TRXTYPE_SALE);
            $request->setOrigid($payment->getReferenceTransactionId());
        } elseif ($payment->getParentTransactionId()) {
            $request = $this->_buildBasicRequest($payment);
            $request->setOrigid($payment->getParentTransactionId());
            $captureAmount = $this->_getCaptureAmount($amount);
            if ($captureAmount) {
                $request->setAmt($captureAmount);
            }
            $trxType = $this->getInfoInstance()->hasAmountPaid() ? self::TRXTYPE_SALE : self::TRXTYPE_DELAYED_CAPTURE;
            $request->setTrxtype($trxType);
        } else {
            $request = $this->_buildPlaceRequest($payment, $amount);
            $request->setTrxtype(self::TRXTYPE_SALE);
        }

        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response->getResultCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                $payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                break;
            default:
                break;
        }
        return $this;
    }

    /**
     * Void payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return $this
     */
    public function void(\Magento\Framework\Object $payment)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setTrxtype(self::TRXTYPE_DELAYED_VOID);
        $request->setOrigid($payment->getParentTransactionId());
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response->getResultCode() == self::RESPONSE_CODE_APPROVED) {
            $payment->setTransactionId(
                $response->getPnref()
            )->setIsTransactionClosed(
                1
            )->setShouldCloseParentTransaction(
                1
            );
        }

        return $this;
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Framework\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Framework\Object $payment)
    {
        if ($payment instanceof \Magento\Sales\Model\Order\Invoice ||
            $payment instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            return false;
        }
        if ($payment->getAmountPaid()) {
            $this->_canVoid = false;
        }

        return $this->_canVoid;
    }

    /**
     * Attempt to void the authorization on cancelling
     *
     * @param \Magento\Framework\Object $payment
     * @return $this
     */
    public function cancel(\Magento\Framework\Object $payment)
    {
        if (!$payment->getOrder()->getInvoiceCollection()->count()) {
            return $this->void($payment);
        }

        return false;
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
        $request = $this->_buildBasicRequest($payment);
        $request->setTrxtype(self::TRXTYPE_CREDIT);
        $request->setOrigid($payment->getParentTransactionId());
        $request->setAmt(round($amount, 2));
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response->getResultCode() == self::RESPONSE_CODE_APPROVED) {
            $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(1);
            $payment->setShouldCloseParentTransaction(!$payment->getCreditmemo()->getInvoice()->canRefund());
        }
        return $this;
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
        $request = $this->_buildBasicRequest($payment);
        $request->setTrxtype(self::TRXTYPE_DELAYED_INQUIRY);
        $transactionId = $payment->getCcTransId() ? $payment->getCcTransId() : $transactionId;
        $request->setOrigid($transactionId);
        $response = $this->_postRequest($request);

        $this->_processErrors($response);

        if (!$this->_isTransactionUnderReview($response->getOrigresult())) {
            $payment->setTransactionId($response->getOrigpnref())->setIsTransactionClosed(0);
            if ($response->getOrigresult() == self::RESPONSE_CODE_APPROVED) {
                $payment->setIsTransactionApproved(true);
            } else if ($response->getOrigresult() == self::RESPONSE_CODE_DECLINED_BY_MERCHANT) {
                $payment->setIsTransactionDenied(true);
            }
        }

        $rawData = $response->getData();
        return $rawData ? $rawData : array();
    }

    /**
     * Check whether the transaction is in payment review status
     *
     * @param string $status
     * @return bool
     */
    protected static function _isTransactionUnderReview($status)
    {
        if (in_array($status, array(self::RESPONSE_CODE_APPROVED, self::RESPONSE_CODE_DECLINED_BY_MERCHANT))) {
            return false;
        }
        return true;
    }

    /**
     * Getter for URL to perform Payflow requests, based on test mode by default
     *
     * @param bool|null $testMode Ability to specify test mode using
     * @return string
     */
    protected function _getTransactionUrl($testMode = null)
    {
        $testMode = is_null($testMode) ? $this->getConfigData('sandbox_flag') : (bool)$testMode;
        if ($testMode) {
            return self::TRANSACTION_URL_TEST_MODE;
        }
        return self::TRANSACTION_URL;
    }

    /**
     * Post request to gateway and return response
     *
     * @param \Magento\Framework\Object $request
     * @return \Magento\Framework\Object
     * @throws \Exception
     */
    protected function _postRequest(\Magento\Framework\Object $request)
    {
        $debugData = array('request' => $request->getData());

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->_httpClientFactory->create();
        $result = new \Magento\Framework\Object();

        $_config = array('maxredirects' => 5, 'timeout' => 30, 'verifypeer' => $this->getConfigData('verify_peer'));

        $_isProxy = $this->getConfigData('use_proxy', false);
        if ($_isProxy) {
            $_config['proxy'] = $this->getConfigData('proxy_host') . ':' . $this->getConfigData('proxy_port');
            //http://proxy.shr.secureserver.net:3128',
            $_config['httpproxytunnel'] = true;
            $_config['proxytype'] = CURLPROXY_HTTP;
        }

        $client->setUri(
            $this->_getTransactionUrl()
        )->setConfig(
            $_config
        )->setMethod(
            \Zend_Http_Client::POST
        )->setParameterPost(
            $request->getData()
        )->setHeaders(
            'X-VPS-VIT-CLIENT-CERTIFICATION-ID: 33baf5893fc2123d8b191d2d011b7fdc'
        )->setHeaders(
            'X-VPS-Request-ID: ' . $request->getRequestId()
        )->setHeaders(
            'X-VPS-CLIENT-TIMEOUT: ' . $this->_clientTimeout
        );

        try {
            /**
             * we are sending request to payflow pro without url encoding
             * so we set up _urlEncodeBody flag to false
             */
            $response = $client->setUrlEncodeBody(false)->request();
        } catch (\Exception $e) {
            $result->setResponseCode(
                -1
            )->setResponseReasonCode(
                $e->getCode()
            )->setResponseReasonText(
                $e->getMessage()
            );

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            throw $e;
        }



        $response = strstr($response->getBody(), 'RESULT');
        $valArray = explode('&', $response);

        foreach ($valArray as $val) {
            $valArray2 = explode('=', $val);
            $result->setData(strtolower($valArray2[0]), $valArray2[1]);
        }

        $result->setResultCode($result->getResult())->setRespmsg($result->getRespmsg());

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }

    /**
     * Return request object with information for 'authorization' or 'sale' action
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return \Magento\Framework\Object
     */
    protected function _buildPlaceRequest(\Magento\Framework\Object $payment, $amount)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setAmt(round($amount, 2));
        $request->setAcct($payment->getCcNumber());
        $request->setExpdate(sprintf('%02d', $payment->getCcExpMonth()) . substr($payment->getCcExpYear(), -2, 2));
        $request->setCvv2($payment->getCcCid());

        if ($this->getIsCentinelValidationEnabled()) {
            $params = array();
            $params = $this->getCentinelValidator()->exportCmpiData($params);
            $request = \Magento\Framework\Object\Mapper::accumulateByMap($params, $request, $this->_centinelFieldMap);
        }

        $order = $payment->getOrder();
        if (!empty($order)) {
            $request->setCurrency($order->getBaseCurrencyCode());

            $orderIncrementId = $order->getIncrementId();

            $request->setCurrency($order->getBaseCurrencyCode())
                ->setInvnum($orderIncrementId)
                ->setPonum($order->getId())
                ->setComment1($orderIncrementId);
            $customerId = $order->getCustomerId();
            if ($customerId) {
                $request->setCustref($customerId);
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
                    $payment->getOrder()->getCustomerEmail()
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
        }
        return $request;
    }

    /**
     * Return request object with basic information for gateway request
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return \Magento\Framework\Object
     */
    protected function _buildBasicRequest(\Magento\Framework\Object $payment)
    {
        $request = new \Magento\Framework\Object();
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_configFactory->create();
        $request->setUser(
            $this->getConfigData('user')
        )->setVendor(
            $this->getConfigData('vendor')
        )->setPartner(
            $this->getConfigData('partner')
        )->setPwd(
            $this->getConfigData('pwd')
        )->setVerbosity(
            $this->getConfigData('verbosity')
        )->setData(
            'BNCODE',
            $config->getBuildNotationCode()
        )->setTender(
            self::TENDER_CC
        )->setRequestId(
            $this->_generateRequestId()
        );
        return $request;
    }

    /**
     * Return unique value for request
     *
     * @return string
     */
    protected function _generateRequestId()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * If response is failed throw exception
     *
     * @param \Magento\Framework\Object $response
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _processErrors(\Magento\Framework\Object $response)
    {
        if ($response->getResultCode() == self::RESPONSE_CODE_VOID_ERROR) {
            throw new \Magento\Paypal\Exception(__('You cannot void a verification transaction.'));
        } elseif ($response->getResultCode() != self::RESPONSE_CODE_APPROVED &&
            $response->getResultCode() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER
        ) {
            throw new \Magento\Framework\Model\Exception($response->getRespmsg());
        } elseif ($response->getOrigresult() == self::RESPONSE_CODE_FRAUDSERVICE_FILTER) {
            throw new \Magento\Framework\Model\Exception($response->getRespmsg());
        }
    }

    /**
     * Set reference transaction data into request
     *
     * @param \Magento\Framework\Object $payment
     * @param \Magento\Framework\Object $request
     * @return $this
     */
    protected function _setReferenceTransaction(\Magento\Framework\Object $payment, $request)
    {
        return $this;
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        return $this->reviewPayment($payment, self::UPDATEACTION_APPROVED);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        return $this->reviewPayment($payment, self::UPDATEACTION_DECLINED_BY_MERCHANT);
    }


    /**
     * Perform the payment review
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $action
     * @return bool
     */
    public function reviewPayment(\Magento\Payment\Model\Info $payment, $action)
    {
        $request = $this->_buildBasicRequest($payment);
        $transactionId = ($payment->getCcTransId()) ? $payment->getCcTransId() : $payment->getLastTransId();
        $request->setTrxtype(self::TRXTYPE_ACCEPT_DENY);
        $request->setOrigid($transactionId);
        $request->setUpdateaction($action);

        $response = $this->_postRequest($request);
        $payment->setAdditionalInformation((array)$response->getData());
        $this->_processErrors($response);

        if (!$this->_isTransactionUnderReview($response->getOrigresult())) {
            $payment->setTransactionId($response->getOrigpnref())->setIsTransactionClosed(0);
            if ($response->getOrigresult() == self::RESPONSE_CODE_APPROVED) {
                $payment->setIsTransactionApproved(true);
            } else if ($response->getOrigresult() == self::RESPONSE_CODE_DECLINED_BY_MERCHANT) {
                $payment->setIsTransactionDenied(true);
            }
        }
        $rawData = $response->getData();
        return ($rawData) ? $rawData : array();
    }
}
