<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Api;

use Magento\Payment\Model\Cart;
use Magento\Payment\Model\Method\Logger;

/**
 * NVP API wrappers model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowNvp extends \Magento\Paypal\Model\Api\Nvp
{
    /**#@+
     * Transaction types declaration
     */
    const TRXTYPE_AUTH_ONLY = 'A';

    const TRXTYPE_SALE = 'S';

    const TRXTYPE_CREDIT = 'C';

    const TRXTYPE_DELAYED_CAPTURE = 'D';

    const TRXTYPE_DELAYED_VOID = 'V';

    /**#@-*/

    /**#@+
     * Tender definition
     */
    const TENDER_CC = 'C';

    const TENDER_PAYPAL = 'P';

    /**#@-*/

    /**#@+
     * Express Checkout Actions
     */
    const EXPRESS_SET = 'S';

    const EXPRESS_GET = 'G';

    const EXPRESS_DO_PAYMENT = 'D';

    /**#@-*/

    /**#@+
     * Response codes definition
     */
    const RESPONSE_CODE_APPROVED = 0;

    const RESPONSE_CODE_FRAUD = 126;

    /**#@-*/

    /**#@-*/
    protected $_captureTypeComplete = 'Y';

    /**
     * Capture type (make authorization close or remain open)
     *
     * @var string
     */
    protected $_captureTypeNotcomplete = 'N';

    /**
     * Global public interface map
     *
     * @var array
     */
    protected $_globalMap = [
        // each call
        'PARTNER' => 'partner',
        'VENDOR' => 'vendor',
        'USER' => 'user',
        'PWD' => 'password',
        'BUTTONSOURCE' => 'build_notation_code',
        'TENDER' => 'tender',

        // commands
        'RETURNURL' => 'return_url',
        'CANCELURL' => 'cancel_url',
        'INVNUM' => 'inv_num',
        'TOKEN' => 'token',
        'CORRELATIONID' => 'correlation_id',
        'CUSTIP' => 'ip_address',
        'NOTIFYURL' => 'notify_url',
        'NOTE' => 'note',

        // style settings
        'PAGESTYLE' => 'page_style',
        'HDRIMG' => 'hdrimg',
        'HDRBORDERCOLOR' => 'hdrbordercolor',
        'HDRBACKCOLOR' => 'hdrbackcolor',
        'PAYFLOWCOLOR' => 'payflowcolor',
        'LOCALECODE' => 'locale_code',

        // transaction info
        //We need to store paypal trx id for correct IPN working
        'PPREF' => 'paypal_transaction_id',
        'PAYMENTINFO_0_TRANSACTIONID' => 'paypal_transaction_id',
        'TRANSACTIONID' => 'paypal_transaction_id',
        'REFUNDTRANSACTIONID' => 'paypal_transaction_id',

        'PNREF' => 'transaction_id',
        'ORIGID' => 'authorization_id',
        'CAPTURECOMPLETE' => 'complete_type',
        'AMT' => 'amount',
        'AVSADDR' => 'address_verification',
        'AVSZIP' => 'postcode_verification',

        // payment/billing info
        'CURRENCY' => 'currency_code',
        'PAYMENTSTATUS' => 'payment_status',
        'PENDINGREASON' => 'pending_reason',
        'PAYERID' => 'payer_id',
        'PAYERSTATUS' => 'payer_status',
        'EMAIL' => 'email',

        // backwards compatibility
        'FIRSTNAME' => 'firstname',
        'LASTNAME' => 'lastname',

        // paypal direct credit card information
        'ACCT' => 'credit_card_number',
        'EXPDATE' => 'credit_card_expiration_date',
        'CVV2' => 'credit_card_cvv2',
        'CARDSTART' => 'maestro_solo_issue_date',
        'CARDISSUE' => 'maestro_solo_issue_number',
        'CVV2MATCH' => 'cvv2_check_result',

        'USERSELECTEDFUNDINGSOURCE' => 'funding_source',

        'NOSHIPPING' => 'suppress_shipping',
        'REQBILLINGADDRESS' => 'require_billing_address',
    ];

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = ['ACCT', 'EXPDATE', 'CVV2', 'PARTNER', 'USER', 'VENDOR', 'PWD'];

    /**
     * DoDirectPayment request map
     *
     * @var string[]
     */
    protected $_doDirectPaymentRequest = [
        'ACCT',
        'EXPDATE',
        'CVV2',
        'CURRENCY',
        'EMAIL',
        'TENDER',
        'NOTIFYURL',
        'AMT',
        'CUSTIP',
        'INVNUM',
        'CARDISSUE',
        'CARDSTART',
        'AUTHSTATUS3DS',
        'MPIVENDOR3DS',
        'CAVV',
        'ECI',
        'XID',
        'TAXAMT',
        'FREIGHTAMT',
    ];

    /**
     * DoDirectPayment response map
     *
     * @var string[]
     */
    protected $_doDirectPaymentResponse = [
        'PNREF',
        'PPREF',
        'CORRELATIONID',
        'CVV2MATCH',
        'AVSADDR',
        'AVSZIP',
        'PENDINGREASON',
    ];

    /**
     * DoCapture request map
     *
     * @var string[]
     */
    protected $_doCaptureRequest = ['ORIGID', 'CAPTURECOMPLETE', 'AMT', 'TENDER', 'NOTE', 'INVNUM'];

    /**
     * DoCapture response map
     *
     * @var string[]
     */
    protected $_doCaptureResponse = ['PNREF', 'PPREF'];

    /**
     * DoVoid request map
     *
     * @var string[]
     */
    protected $_doVoidRequest = ['ORIGID', 'NOTE', 'TENDER'];

    /**
     * Request map for each API call
     *
     * @var string[]
     */
    protected $_eachCallRequest = ['PARTNER', 'USER', 'VENDOR', 'PWD', 'BUTTONSOURCE'];

    /**
     * RefundTransaction request map
     *
     * @var string[]
     */
    protected $_refundTransactionRequest = ['ORIGID', 'TENDER'];

    /**
     * RefundTransaction response map
     *
     * @var string[]
     */
    protected $_refundTransactionResponse = ['PNREF', 'PPREF'];

    /**
     * SetExpressCheckout request map
     *
     * @var string[]
     */
    protected $_setExpressCheckoutRequest = [
        'TENDER',
        'AMT',
        'CURRENCY',
        'RETURNURL',
        'CANCELURL',
        'INVNUM',
        'PAGESTYLE',
        'HDRIMG',
        'HDRBORDERCOLOR',
        'HDRBACKCOLOR',
        'PAYFLOWCOLOR',
        'LOCALECODE',
        'USERSELECTEDFUNDINGSOURCE',
        'NOSHIPPING',
        'REQBILLINGADDRESS',
    ];

    /**
     * SetExpressCheckout response map
     *
     * @var string[]
     */
    protected $_setExpressCheckoutResponse = ['REPMSG', 'TOKEN'];

    /**
     * GetExpressCheckoutDetails request/response map
     *
     * @var string[]
     */
    protected $_getExpressCheckoutDetailsRequest = ['TENDER', 'TOKEN'];

    /**
     * DoExpressCheckoutPayment request map
     *
     * @var string[]
     */
    protected $_doExpressCheckoutPaymentRequest = [
        'TENDER',
        'TOKEN',
        'PAYERID',
        'AMT',
        'CURRENCY',
        'CUSTIP',
        'BUTTONSOURCE',
        'NOTIFYURL',
    ];

    /**
     * DoExpressCheckoutPayment response map
     *
     * @var string[]
     */
    protected $_doExpressCheckoutPaymentResponse = [
        'PNREF',
        'PPREF',
        'REPMSG',
        'AMT',
        'PENDINGREASON',
        'CVV2MATCH',
        'AVSADDR',
        'AVSZIP',
        'CORRELATIONID',
    ];

    /**
     * GetTransactionDetailsRequest
     *
     * @var string[]
     */
    protected $_getTransactionDetailsRequest = ['ORIGID', 'TENDER'];

    /**
     * GetTransactionDetailsResponse
     *
     * @var string[]
     */
    protected $_getTransactionDetailsResponse = [
        'PAYERID',
        'FIRSTNAME',
        'LASTNAME',
        'TRANSACTIONID',
        'PARENTTRANSACTIONID',
        'CURRENCYCODE',
        'AMT',
        'PAYMENTSTATUS',
    ];

    /**
     * Map for shipping address import/export (extends billing address mapper)
     *
     * @var array
     */
    protected $_shippingAddressMap = [
        'SHIPTOCOUNTRY' => 'country_id',
        'SHIPTOSTATE' => 'region',
        'SHIPTOCITY' => 'city',
        'SHIPTOSTREET' => 'street',
        'SHIPTOSTREET2' => 'street2',
        'SHIPTOZIP' => 'postcode',
        'SHIPTOPHONENUM' => 'telephone',
    ];

    /**
     * Map for billing address import/export
     *
     * @var array
     */
    protected $_billingAddressMap = [
        'BUSINESS' => 'company',
        'NOTETEXT' => 'customer_notes',
        'EMAIL' => 'email',
        'FIRSTNAME' => 'firstname',
        'LASTNAME' => 'lastname',
        'MIDDLENAME' => 'middlename',
        'SALUTATION' => 'prefix',
        'SUFFIX' => 'suffix',
        'COUNTRYCODE' => 'country_id',
        'STATE' => 'region',
        'CITY' => 'city',
        'STREET' => 'street',
        'STREET2' => 'street2',
        'ZIP' => 'postcode',
        'PHONENUM' => 'telephone',
    ];

    /**
     * Map for billing address to do request to Payflow
     *
     * @var array
     */
    protected $_billingAddressMapRequest = ['country_id' => 'COUNTRY'];

    /**
     * Line items export mapping settings
     *
     * @var array
     */
    protected $_lineItemTotalExportMap = [Cart::AMOUNT_TAX => 'TAXAMT', Cart::AMOUNT_SHIPPING => 'FREIGHTAMT'];

    /**
     * Line items export request totals format
     *
     * @var array
     */
    protected $_lineItemsExportRequestTotalsFormat = [
        'amount' => 'PAYMENTREQUEST_%d_ITEMAMT',
        Cart::AMOUNT_TAX => 'TAXAMT',
        Cart::AMOUNT_SHIPPING => 'FREIGHTAMT',
    ];

    /**
     * Line items export items format
     *
     * @var array
     */
    protected $_lineItemExportItemsFormat = [
        'name' => 'L_NAME%d',
        'qty' => 'L_QTY%d',
        'amount' => 'L_COST%d',
    ];

    /**
     * Payment information response specifically to be collected after some requests
     *
     * @var string[]
     */
    protected $_paymentInformationResponse = [
        'PAYERID',
        'CORRELATIONID',
        'ADDRESSID',
        'ADDRESSSTATUS',
        'PAYMENTSTATUS',
        'PENDINGREASON',
        'PROTECTIONELIGIBILITY',
        'EMAIL',
    ];

    /**
     * Required fields in the response
     *
     * @var array
     */
    protected $_requiredResponseParams = [
        self::DO_DIRECT_PAYMENT => ['RESULT', 'PNREF', 'PPREF'],
    ];

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var NvpFactory
     */
    protected $nvpFactory;

    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Psr\Log\LoggerInterface $logger
     * @param Logger $customLogger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param ProcessableExceptionFactory $processableExceptionFactory
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param NvpFactory $nvpFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Psr\Log\LoggerInterface $logger,
        Logger $customLogger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Paypal\Model\Api\ProcessableExceptionFactory $processableExceptionFactory,
        \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Math\Random $mathRandom,
        NvpFactory $nvpFactory,
        array $data = []
    ) {
        parent::__construct(
            $customerAddress,
            $logger,
            $customLogger,
            $localeResolver,
            $regionFactory,
            $countryFactory,
            $processableExceptionFactory,
            $frameworkExceptionFactory,
            $curlFactory,
            $data
        );
        $this->mathRandom = $mathRandom;
        $this->nvpFactory = $nvpFactory;
    }

    /**
     * API endpoint getter
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return sprintf(
            'https://%spayflowpro.paypal.com/transaction',
            $this->_config->getValue('sandboxFlag') ? 'pilot-' : ''
        );
    }

    /**
     * Return Payflow partner based on config data
     *
     * @return string
     */
    public function getPartner()
    {
        return $this->_getDataOrConfig('partner');
    }

    /**
     * Return Payflow user based on config data
     *
     * @return string
     */
    public function getUser()
    {
        return $this->_getDataOrConfig('user');
    }

    /**
     * Return Payflow password based on config data
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_getDataOrConfig('pwd');
    }

    /**
     * Return Payflow vendor based on config data
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->_getDataOrConfig('vendor');
    }

    /**
     * Return Payflow tender based on config data
     *
     * @return string
     */
    public function getTender()
    {
        if ($this->_config->getMethodCode() == \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS) {
            return self::TENDER_PAYPAL;
        }
        return self::TENDER_CC;
    }

    /**
     * Override transaction id getting to process payflow accounts not assigned to paypal side
     *
     * @return string
     */
    public function getPaypalTransactionId()
    {
        if ($this->getData('paypal_transaction_id')) {
            return $this->getData('paypal_transaction_id');
        }
        return $this->getTransactionId();
    }

    /**
     * Add method to request array
     *
     * @param string $methodName
     * @param array $request
     * @return array
     */
    protected function _addMethodToRequest($methodName, $request)
    {
        $request['TRXTYPE'] = $this->_mapPaypalMethodName($methodName);
        if ($this->_getPayflowActionName($methodName) !== null) {
            $request['ACTION'] = $this->_getPayflowActionName($methodName);
        }
        return $request;
    }

    /**
     * Return Payflow Edition
     *
     * @param string $methodName
     * @return string|null
     */
    protected function _getPayflowActionName($methodName)
    {
        switch ($methodName) {
            case \Magento\Paypal\Model\Api\Nvp::SET_EXPRESS_CHECKOUT:
                return self::EXPRESS_SET;
            case \Magento\Paypal\Model\Api\Nvp::GET_EXPRESS_CHECKOUT_DETAILS:
                return self::EXPRESS_GET;
            case \Magento\Paypal\Model\Api\Nvp::DO_EXPRESS_CHECKOUT_PAYMENT:
                return self::EXPRESS_DO_PAYMENT;
        }
        return null;
    }

    /**
     * Map paypal method names
     *
     * @param string| $methodName
     * @return string
     */
    protected function _mapPaypalMethodName($methodName)
    {
        switch ($methodName) {
            case \Magento\Paypal\Model\Api\Nvp::DO_EXPRESS_CHECKOUT_PAYMENT:
            case \Magento\Paypal\Model\Api\Nvp::GET_EXPRESS_CHECKOUT_DETAILS:
            case \Magento\Paypal\Model\Api\Nvp::SET_EXPRESS_CHECKOUT:
            case \Magento\Paypal\Model\Api\Nvp::DO_DIRECT_PAYMENT:
                return $this->_config->getValue('payment_action') ==
                    \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH ? self::TRXTYPE_AUTH_ONLY : self::TRXTYPE_SALE;
            case \Magento\Paypal\Model\Api\Nvp::DO_CAPTURE:
                return self::TRXTYPE_DELAYED_CAPTURE;
            case \Magento\Paypal\Model\Api\Nvp::DO_VOID:
                return self::TRXTYPE_DELAYED_VOID;
            case \Magento\Paypal\Model\Api\Nvp::REFUND_TRANSACTION:
                return self::TRXTYPE_CREDIT;
        }
    }

    /**
     * Catch success calls and collect warnings
     *
     * @param array $response
     * @return bool success flag
     */
    protected function _isCallSuccessful($response)
    {
        $this->_callWarnings = [];
        if ($response['RESULT'] == self::RESPONSE_CODE_APPROVED) {
            // collect warnings
            if (!empty($response['RESPMSG']) && strtoupper($response['RESPMSG']) != 'APPROVED') {
                $this->_callWarnings[] = $response['RESPMSG'];
            }
            return true;
        }
        return false;
    }

    /**
     * Handle logical errors
     *
     * @param array $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _handleCallErrors($response)
    {
        if ($response['RESULT'] != self::RESPONSE_CODE_APPROVED) {
            $message = $response['RESPMSG'];
            $e = new \Exception(sprintf('PayPal gateway errors: %s.', $message));
            $this->_logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The PayPal gateway rejected the request. %1', $message)
            );
        }
    }

    /**
     * Build query string without urlencoding from request
     *
     * @param array $request
     * @return string
     */
    protected function _buildQuery($request)
    {
        $result = '';
        foreach ($request as $k => $v) {
            $result .= '&' . $k . '=' . $v;
        }
        return trim($result, '&');
    }

    /**
     * Generate Request ID
     *
     * @return string
     */
    protected function getRequestId()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * GetTransactionDetails method does not exists in Payflow
     *
     * @return void
     */
    public function callGetTransactionDetails()
    {
    }

    /**
     * Get FMF results from response, if any
     *
     * @param array $from
     * @param array $collectedWarnings
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _importFraudFiltersResult(array $from, array $collectedWarnings)
    {
        if ($from['RESULT'] != self::RESPONSE_CODE_FRAUD) {
            return;
        }
        $this->setIsPaymentPending(true);
    }

    /**
     * Return each call request fields
     * (PayFlow edition doesn't support Unilateral payments)
     *
     * @param string $methodName Current method name
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareEachCallRequest($methodName)
    {
        return $this->_eachCallRequest;
    }

    /**
     * Overwrite parent logic, simply return input data
     * (PayFlow edition doesn't support Unilateral payments)
     *
     * @param array $requestFields Standard set of values
     * @return array
     */
    protected function _prepareExpressCheckoutCallRequest(&$requestFields)
    {
        return $requestFields;
    }

    /**
     * Additional response processing.
     *
     * Hack to cut off length from API type response params.
     *
     * @param  array $response
     * @return array
     */
    protected function _postProcessResponse($response)
    {
        foreach ($response as $key => $value) {
            $pos = strpos($key, '[');

            if ($pos === false) {
                continue;
            }

            unset($response[$key]);

            if ($pos !== 0) {
                $modifiedKey = substr($key, 0, $pos);
                $response[$modifiedKey] = $value;
            }
        }

        return $response;
    }

    /**
     * Checking negative line items
     *
     * @param array $request
     * @param int $i
     * @return null|true
     */
    protected function _exportLineItems(array &$request, $i = 0)
    {
        $requestBefore = $request;
        $result = parent::_exportLineItems($request, $i);
        if ($this->getIsLineItemsEnabled() && $this->_cart->hasNegativeItemAmount()) {
            $this->_lineItemTotalExportMap = [
                Cart::AMOUNT_TAX      => 'TAXAMT',
                Cart::AMOUNT_SHIPPING => 'FREIGHTAMT',
                'amount'              => 'PAYMENTREQUEST_0_ITEMAMT',
            ];
            $request = $requestBefore;
            $result = parent::_exportLineItems($request, $i);
            /** @var Nvp $paypalNvp */
            $paypalNvp = $this->nvpFactory->create();
            $this->_doCaptureResponse = $paypalNvp->_doCaptureResponse;
            $this->_refundTransactionResponse = $paypalNvp->_refundTransactionResponse;
            $this->_getTransactionDetailsResponse = $paypalNvp->_getTransactionDetailsResponse;
            $this->_paymentInformationResponse = $paypalNvp->_paymentInformationResponse;
            $this->_headers[] = 'PAYPAL-NVP: Y';
            $this->_setSpecificForNegativeLineItems();
        }
        return $result;
    }

    /**
     * Set specific data when negative line item case.
     *
     * @return void
     */
    protected function _setSpecificForNegativeLineItems()
    {
        /** @var Nvp $paypalNvp */
        $paypalNvp = $this->nvpFactory->create();
        $this->_setExpressCheckoutResponse = $paypalNvp->_setExpressCheckoutResponse;
        $index = array_search('PPREF', $this->_doExpressCheckoutPaymentResponse);
        if (false !== $index) {
            unset($this->_doExpressCheckoutPaymentResponse[$index]);
        }
        $this->_doExpressCheckoutPaymentResponse[] = 'PAYMENTINFO_0_TRANSACTIONID';
        $this->_requiredResponseParams[self::DO_EXPRESS_CHECKOUT_PAYMENT][] = 'PAYMENTINFO_0_TRANSACTIONID';
    }
}
