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
 * @package     Mage_PaypalUk
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * NVP API wrappers model
 */
class Mage_PaypalUk_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    /**#@+
     * Transaction types declaration
     *
     * @var mixed
     */
    const TRXTYPE_AUTH_ONLY         = 'A';
    const TRXTYPE_SALE              = 'S';
    const TRXTYPE_CREDIT            = 'C';
    const TRXTYPE_DELAYED_CAPTURE   = 'D';
    const TRXTYPE_DELAYED_VOID      = 'V';
    /**#@-*/

    /**#@+
     * Tender definition
     *
     * @var mixed
     */
    const TENDER_CC                 = 'C';
    const TENDER_PAYPAL             = 'P';
    /**#@-*/

    /**#@+
     * Express Checkout Actions
     *
     * @var string
     */
    const EXPRESS_SET               = 'S';
    const EXPRESS_GET               = 'G';
    const EXPRESS_DO_PAYMENT        = 'D';
    /**#@-*/

    /**#@+
     * Response codes definition
     *
     * @var mixed
     */
    const RESPONSE_CODE_APPROVED = 0;
    const RESPONSE_CODE_FRAUD = 126;
    /**#@-*/

    /**#@+
     * Capture types (make authorization close or remain open)
     *
     * @var string
     */
    protected $_captureTypeComplete = 'Y';
    protected $_captureTypeNotcomplete = 'N';
    /**#@-*/

    /**
     * Global public interface map
     *
     * @var array
     */
    protected $_globalMap = array(
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
        'PPREF' => 'paypal_transaction_id', //We need to store paypal trx id for correct IPN working
        'PNREF' => 'transaction_id',
        'ORIGID' => 'authorization_id',
        'CAPTURECOMPLETE' => 'complete_type',
        'AMT' => 'amount',
        'AVSADDR' => 'address_verification',
        'AVSZIP' => 'postcode_verification',

        // payment/billing info
        'CURRENCY' => 'currency_code',
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
        'CARDSTART' => 'maestro_solo_issue_date', // MMYY, including leading zero
        'CARDISSUE' => 'maestro_solo_issue_number',
        'CVV2MATCH' => 'cvv2_check_result',
        // cardinal centinel
        'AUTHSTATUS3DS' => 'centinel_authstatus',
        'MPIVENDOR3DS' => 'centinel_mpivendor',
        'CAVV' => 'centinel_cavv',
        'ECI' => 'centinel_eci',
        'XID' => 'centinel_xid',
        'VPAS' => 'centinel_vpas_result',
        'ECISUBMITTED3DS' => 'centinel_eci_result',
    );

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array(
        'ACCT', 'EXPDATE', 'CVV2',
        'PARTNER', 'USER', 'VENDOR', 'PWD',
    );

    /**#@+
     * DoDirectPayment request/response map
     *
     * @var array
     */
    protected $_doDirectPaymentRequest = array(
        'ACCT', 'EXPDATE', 'CVV2', 'CURRENCY', 'EMAIL', 'TENDER', 'NOTIFYURL',
        'AMT', 'CUSTIP', 'INVNUM',
        'CARDISSUE', 'CARDSTART',
        'AUTHSTATUS3DS', 'MPIVENDOR3DS', 'CAVV', 'ECI', 'XID',//cardinal centinel params
        'TAXAMT', 'FREIGHTAMT'
    );
    protected $_doDirectPaymentResponse = array(
        'PNREF', 'PPREF', 'CORRELATIONID', 'CVV2MATCH', 'AVSADDR', 'AVSZIP', 'PENDINGREASON'
    );
    /**#@-*/

    /**#@+
     * DoCapture request/response map
     *
     * @var array
     */
    protected $_doCaptureRequest = array('ORIGID', 'CAPTURECOMPLETE', 'AMT', 'TENDER', 'NOTE', 'INVNUM');
    protected $_doCaptureResponse = array('PNREF', 'PPREF');
    /**#@-*/

    /**
     * DoVoid request map
     *
     * @var array
     */
    protected $_doVoidRequest = array('ORIGID', 'NOTE', 'TENDER');

    /**
     * Request map for each API call
     *
     * @var array
     */
    protected $_eachCallRequest = array('PARTNER', 'USER', 'VENDOR', 'PWD', 'BUTTONSOURCE');

    /**#@+
     * RefundTransaction request/response map
     *
     * @var array
     */
    protected $_refundTransactionRequest = array('ORIGID', 'TENDER');
    protected $_refundTransactionResponse = array('PNREF', 'PPREF');
    /**#@-*/

    /**#@+
     * SetExpressCheckout request/response map
     *
     * @var array
     */
    protected $_setExpressCheckoutRequest = array(
        'TENDER', 'AMT', 'CURRENCY', 'RETURNURL', 'CANCELURL', 'INVNUM',
        'PAGESTYLE', 'HDRIMG', 'HDRBORDERCOLOR', 'HDRBACKCOLOR', 'PAYFLOWCOLOR', 'LOCALECODE',
    );
    protected $_setExpressCheckoutResponse = array('REPMSG', 'TOKEN');
    /**#@-*/

    /**
     * GetExpressCheckoutDetails request/response map
     *
     * @var array
     */
    protected $_getExpressCheckoutDetailsRequest = array('TENDER', 'TOKEN');

    /**#@+
     * DoExpressCheckoutPayment request/response map
     *
     * @var array
     */
    protected $_doExpressCheckoutPaymentRequest = array(
        'TENDER', 'TOKEN', 'PAYERID', 'AMT', 'CURRENCY', 'CUSTIP', 'BUTTONSOURCE', 'NOTIFYURL',
    );
    protected $_doExpressCheckoutPaymentResponse = array(
        'PNREF', 'PPREF', 'REPMSG', 'AMT', 'PENDINGREASON',
        'CVV2MATCH', 'AVSADDR', 'AVSZIP', 'CORRELATIONID'
    );
    /**#@-*/

    /**#@+
     * GetTransactionDetailsRequest
     *
     * @var array
     */
    protected $_getTransactionDetailsRequest = array('ORIGID', 'TENDER');
    protected $_getTransactionDetailsResponse = array(
        'PAYERID', 'FIRSTNAME', 'LASTNAME', 'TRANSACTIONID',
        'PARENTTRANSACTIONID', 'CURRENCYCODE', 'AMT', 'PAYMENTSTATUS'
    );
    /**#@-*/

    /**
     * Map for shipping address import/export (extends billing address mapper)
     *
     * @var array
     */
    protected $_shippingAddressMap = array(
        'SHIPTOCOUNTRY' => 'country_id',
        'SHIPTOSTATE' => 'region',
        'SHIPTOCITY'    => 'city',
        'SHIPTOSTREET'  => 'street',
        'SHIPTOSTREET2' => 'street2',
        'SHIPTOZIP' => 'postcode',
        'SHIPTOPHONENUM' => 'telephone', // does not supported by PaypalUk
    );

    /**
     * Map for billing address import/export
     *
     * @var array
     */
    protected $_billingAddressMap = array(
        'BUSINESS' => 'company',
        'NOTETEXT' => 'customer_notes',
        'EMAIL' => 'email',
        'FIRSTNAME' => 'firstname',
        'LASTNAME' => 'lastname',
        'MIDDLENAME' => 'middlename',
        'SALUTATION' => 'prefix',
        'SUFFIX' => 'suffix',

        'COUNTRYCODE' => 'country_id', // iso-3166 two-character code
        'STATE'    => 'region',
        'CITY'     => 'city',
        'STREET'   => 'street',
        'STREET2'  => 'street2',
        'ZIP'      => 'postcode',
        'PHONENUM' => 'telephone',
    );

    /**
     * Map for billing address to do request to PayPalUk
     *
     * @var array
     */
    protected $_billingAddressMapRequest = array(
        'country_id' => 'COUNTRY',
    );

    /**#@+
     * Line items export mapping settings
     *
     * @var array
     */
    protected $_lineItemTotalExportMap = array(
        Mage_Paypal_Model_Cart::TOTAL_TAX      => 'TAXAMT',
        Mage_Paypal_Model_Cart::TOTAL_SHIPPING => 'FREIGHTAMT',
    );

    protected $_lineItemExportItemsFormat = array(
        'name'   => 'L_NAME%d',
        'qty'    => 'L_QTY%d',
        'amount' => 'L_COST%d',
    );
    /**#@-*/

    /**
     * Payment information response specifically to be collected after some requests
     *
     * @var array
     */
    protected $_paymentInformationResponse = array(
        'PAYERID', 'CORRELATIONID', 'ADDRESSID', 'ADDRESSSTATUS',
        'PAYMENTSTATUS', 'PENDINGREASON', 'PROTECTIONELIGIBILITY', 'EMAIL',
    );

    /**
     * Required fields in the response
     *
     * @var array
     */
    protected $_requiredResponseParams = array(
        self::DO_DIRECT_PAYMENT => array('RESULT', 'PNREF', 'PPREF')
    );

    /**
     * API endpoint getter
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return sprintf('https://%spayflowpro.paypal.com/transaction', $this->_config->sandboxFlag ? 'pilot-' : '');
    }

    /**
     * Return PaypalUk partner based on config data
     *
     * @return string
     */
    public function getPartner()
    {
        return $this->_getDataOrConfig('partner');
    }

    /**
     * Return PaypalUk user based on config data
     *
     * @return string
     */
    public function getUser()
    {
        return $this->_getDataOrConfig('user');
    }

    /**
     * Return PaypalUk password based on config data
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_getDataOrConfig('pwd');
    }

    /**
     * Return PaypalUk vendor based on config data
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->_getDataOrConfig('vendor');
    }

    /**
     * Return PaypalUk tender based on config data
     *
     * @return string
     */
    public function getTender()
    {
        if ($this->_config->getMethodCode() == Mage_Paypal_Model_Config::METHOD_WPP_PE_EXPRESS) {
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
        if (!is_null($this->_getPaypalUkActionName($methodName))) {
            $request['ACTION'] = $this->_getPaypalUkActionName($methodName);
        }
        return $request;
    }

    /**
     * Return Payflow Edition
     *
     * @param string
     * @return string | null
     */
    protected function _getPaypalUkActionName($methodName)
    {
        switch($methodName) {
            case Mage_Paypal_Model_Api_Nvp::SET_EXPRESS_CHECKOUT:
                return self::EXPRESS_SET;
            case Mage_Paypal_Model_Api_Nvp::GET_EXPRESS_CHECKOUT_DETAILS:
                return self::EXPRESS_GET;
            case Mage_Paypal_Model_Api_Nvp::DO_EXPRESS_CHECKOUT_PAYMENT:
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
        switch($methodName) {
            case Mage_Paypal_Model_Api_Nvp::DO_EXPRESS_CHECKOUT_PAYMENT:
            case Mage_Paypal_Model_Api_Nvp::GET_EXPRESS_CHECKOUT_DETAILS:
            case Mage_Paypal_Model_Api_Nvp::SET_EXPRESS_CHECKOUT:
            case Mage_Paypal_Model_Api_Nvp::DO_DIRECT_PAYMENT:
                return ($this->_config->payment_action == Mage_Paypal_Model_Config::PAYMENT_ACTION_AUTH)
                    ? self::TRXTYPE_AUTH_ONLY
                    : self::TRXTYPE_SALE;
            case Mage_Paypal_Model_Api_Nvp::DO_CAPTURE:
                return self::TRXTYPE_DELAYED_CAPTURE;
            case Mage_Paypal_Model_Api_Nvp::DO_VOID:
                return self::TRXTYPE_DELAYED_VOID;
            case Mage_Paypal_Model_Api_Nvp::REFUND_TRANSACTION:
                return self::TRXTYPE_CREDIT;
        }
    }

    /**
     * Catch success calls and collect warnings
     *
     * @param array
     * @return bool| success flag
     */
    protected function _isCallSuccessful($response)
    {
        $this->_callWarnings = array();
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
     * @param array
     */
    protected function _handleCallErrors($response)
    {
        if ($response['RESULT'] != self::RESPONSE_CODE_APPROVED) {
            $message = $response['RESPMSG'];
            $e = new Exception(sprintf('PayPal gateway errors: %s.', $message));
            Mage::logException($e);
            Mage::throwException(
                Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal gateway rejected the request. %s', $message)
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
        foreach ($request as $k=>$v) {
            $result .= '&'.$k.'='.$v;
        }
        return trim($result, '&');
    }

    /**
     * Generate Request ID
     *
     * @return
     */
    protected function getRequestId()
    {
        return Mage::helper('Mage_Core_Helper_Data')->uniqHash();
    }

    /**
     * "GetTransactionDetails" method does not exists in PaypalUK
     */
    function callGetTransactionDetails()
    {
    }

    /**
     * Get FMF results from response, if any
     *
     * @param array $from
     * @param array $collectedWarnings
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
     * @return array
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
     * Adopt specified request array to be compatible with Paypal
     * Puerto Rico should be as state of USA and not as a country
     *
     * @param array $request
     */
    protected function _applyCountryWorkarounds(&$request)
    {
        if (isset($request['SHIPTOCOUNTRY']) && $request['SHIPTOCOUNTRY'] == 'PR') {
            $request['SHIPTOCOUNTRY'] = 'US';
            $request['SHIPTOSTATE']   = 'PR';
        }
    }
}
