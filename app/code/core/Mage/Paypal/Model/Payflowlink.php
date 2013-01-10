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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payflow Link payment gateway model
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Paypal_Model_Payflowlink extends Mage_Paypal_Model_Payflowpro
{
    /**
     * Payment method code
     */
    protected $_code = Mage_Paypal_Model_Config::METHOD_PAYFLOWLINK;

    protected $_formBlockType = 'Mage_Paypal_Block_Payflow_Link_Form';
    protected $_infoBlockType = 'Mage_Paypal_Block_Payflow_Link_Info';

    /**
     * Availability options
     */
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_isInitializeNeeded      = true;

    /**
     * Request & response model
     * @var Mage_Paypal_Model_Payflow_Request
     */
    protected $_response;

    /**
     * Gateway request URL
     * @var string
     */
    const TRANSACTION_PAYFLOW_URL = 'https://payflowlink.paypal.com/';

    /**
     * Error message
     * @var string
     */
    const RESPONSE_ERROR_MSG = 'Payment error. %s was not found.';

    /**
     * Key for storing secure hash in additional information of payment model
     *
     * @var string
     */
    protected $_secureSilentPostHashKey = 'secure_silent_post_hash';

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
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $storeId = Mage::app()->getStore($this->getStore())->getId();
        $config = Mage::getModel('Mage_Paypal_Model_Config')->setStoreId($storeId);
        if (Mage_Payment_Model_Method_Abstract::isAvailable($quote) && $config->isMethodAvailable($this->getCode())) {
            return true;
        }
        return false;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case Mage_Paypal_Model_Config::PAYMENT_ACTION_AUTH:
            case Mage_Paypal_Model_Config::PAYMENT_ACTION_SALE:
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

                $stateObject->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
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
     * @return Mage_Paypal_Model_Payflow_Request
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = Mage::getModel('Mage_Paypal_Model_Payflow_Request');
        }

        return $this->_response;
    }

    /**
     * Fill response with data.
     *
     * @param array $postData
     * @return Mage_Paypal_Model_Payflowlink
     */
    public function setResponseData(array $postData)
    {
        foreach ($postData as $key => $val) {
            $this->getResponse()->setData(strtolower($key), $val);
        }
        return $this;
    }

    /**
     * Operate with order using data from $_POST which came from Silent Post Url.
     *
     * @param array $responseData
     * @throws Mage_Core_Exception in case of validation error or order creation error
     */
    public function process($responseData)
    {
        $debugData = array(
            'response' => $responseData
        );
        $this->_debug($debugData);

        $this->setResponseData($responseData);

        if ($order = $this->_getOrderFromResponse()) {
            $this->_processOrder($order);
        }
    }

    /**
     * Operate with order using information from silent post
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _processOrder(Mage_Sales_Model_Order $order)
    {
        $response = $this->getResponse();
        $payment = $order->getPayment();
        $payment->setTransactionId($response->getPnref())
            ->setIsTransactionClosed(0);
        $canSendNewOrderEmail = true;

        if ($response->getResult() == self::RESPONSE_CODE_FRAUDSERVICE_FILTER ||
            $response->getResult() == self::RESPONSE_CODE_DECLINED_BY_FILTER
        ) {
            $canSendNewOrderEmail = false;
            $fraudMessage = $this->_getFraudMessage() ?
                $response->getFraudMessage() : $response->getRespmsg();
            $payment->setIsTransactionPending(true)
                ->setIsFraudDetected(true)
                ->setAdditionalInformation('paypal_fraud_filters', $fraudMessage);
        }

        if ($response->getAvsdata() && strstr(substr($response->getAvsdata(), 0, 2), 'N')) {
            $payment->setAdditionalInformation('paypal_avs_code', substr($response->getAvsdata(), 0, 2));
        }
        if ($response->getCvv2match() && $response->getCvv2match() != 'Y') {
            $payment->setAdditionalInformation('paypal_cvv2_match', $response->getCvv2match());
        }

        switch ($response->getType()){
            case self::TRXTYPE_AUTH_ONLY:
                $payment->registerAuthorizationNotification($payment->getBaseAmountAuthorized());
                break;
            case self::TRXTYPE_SALE:
                $payment->registerCaptureNotification($payment->getBaseAmountAuthorized());
                break;
        }
        $order->save();

        try {
            if ($canSendNewOrderEmail) {
                $order->sendNewOrderEmail();
            }
            Mage::getModel('Mage_Sales_Model_Quote')
                ->load($order->getQuoteId())
                ->setIsActive(false)
                ->save();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('Can not send new order email.'));
        }
    }

    /**
     * Get fraud message from response
     *
     * @return string|bool
     */
    protected function _getFraudMessage()
    {
        if ($this->getResponse()->getFpsPrexmldata()) {
            $xml = new SimpleXMLElement($this->getResponse()->getFpsPrexmldata());
            $this->getResponse()->setFraudMessage((string) $xml->rule->triggeredMessage);
            return $this->getResponse()->getFraudMessage();
        }

        return false;
    }

    /**
     * Check response from Payflow gateway.
     *
     * @return Mage_Sales_Model_Order in case of validation passed
     * @throws Mage_Core_Exception in other cases
     */
    protected function _getOrderFromResponse()
    {
        $response = $this->getResponse();

        $order = Mage::getModel('Mage_Sales_Model_Order')
                ->loadByIncrementId($response->getInvnum());

        if ($this->_getSecureSilentPostHash($order->getPayment()) != $response->getUser2()
            || $this->_code != $order->getPayment()->getMethodInstance()->getCode()
        ) {
            return false;
        }

        if ($response->getResult() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER
            && $response->getResult() != self::RESPONSE_CODE_DECLINED_BY_FILTER
            && $response->getResult() != self::RESPONSE_CODE_APPROVED
        ) {
            if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                $order->registerCancellation($response->getRespmsg())->save();
            }
            Mage::throwException($response->getRespmsg());
        }

        $amountCompared = ($response->getAmt() == $order->getPayment()->getBaseAmountAuthorized()) ? true : false;
        if (!$order->getId()
            || $order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
            || !$amountCompared
        ) {
            Mage::throwException($this->_formatStr(self::RESPONSE_ERROR_MSG, 'Order'));
        }

        $fetchData = $this->fetchTransactionInfo($order->getPayment(), $response->getPnref());
        if (!isset($fetchData['custref']) || $fetchData['custref'] != $order->getIncrementId()) {
            Mage::throwException($this->_formatStr(self::RESPONSE_ERROR_MSG, 'Transaction'));
        }

        return $order;
    }

    /**
     * Build request for getting token
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Varien_Object
     */
    protected function _buildTokenRequest(Mage_Sales_Model_Order_Payment $payment)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setCreatesecuretoken('Y')
            ->setSecuretokenid($this->_generateSecureTokenId())
            ->setTrxtype($this->_getTrxTokenType())
            ->setAmt($this->_formatStr('%.2F', $payment->getOrder()->getBaseTotalDue()))
            ->setCurrency($payment->getOrder()->getBaseCurrencyCode())
            ->setInvnum($payment->getOrder()->getIncrementId())
            ->setCustref($payment->getOrder()->getIncrementId())
            ->setPonum($payment->getOrder()->getId());
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
            $request->setFirstname($billing->getFirstname())
                ->setLastname($billing->getLastname())
                ->setStreet(implode(' ', $billing->getStreet()))
                ->setCity($billing->getCity())
                ->setState($billing->getRegionCode())
                ->setZip($billing->getPostcode())
                ->setCountry($billing->getCountry())
                ->setEmail($order->getCustomerEmail());
        }
        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->_applyCountryWorkarounds($shipping);
            $request->setShiptofirstname($shipping->getFirstname())
                ->setShiptolastname($shipping->getLastname())
                ->setShiptostreet(implode(' ', $shipping->getStreet()))
                ->setShiptocity($shipping->getCity())
                ->setShiptostate($shipping->getRegionCode())
                ->setShiptozip($shipping->getPostcode())
                ->setShiptocountry($shipping->getCountry());
        }
        //pass store Id to request
        $request->setUser1($order->getStoreId())
            ->setUser2($this->_getSecureSilentPostHash($payment));

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
            return (int) $response->getUser1();
        }

        return Mage::app()->getStore($this->getStore())->getId();
    }

    /**
      * Return request object with basic information for gateway request
      *
      * @param Varien_Object $payment
      * @return Mage_Paypal_Model_Payflow_Request
      */
    protected function _buildBasicRequest(Varien_Object $payment)
    {
        $request = Mage::getModel('Mage_Paypal_Model_Payflow_Request');
        $request
            ->setUser($this->getConfigData('user', $this->_getStoreId()))
            ->setVendor($this->getConfigData('vendor', $this->_getStoreId()))
            ->setPartner($this->getConfigData('partner', $this->_getStoreId()))
            ->setPwd($this->getConfigData('pwd', $this->_getStoreId()))
            ->setVerbosity($this->getConfigData('verbosity', $this->_getStoreId()))
            ->setTender(self::TENDER_CC);
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
            case Mage_Paypal_Model_Config::PAYMENT_ACTION_AUTH:
                return self::TRXTYPE_AUTH_ONLY;
            case Mage_Paypal_Model_Config::PAYMENT_ACTION_SALE:
                return self::TRXTYPE_SALE;
        }
    }

    /**
      * Return unique value for secure token id
      *
      * @return string
      */
    protected function _generateSecureTokenId()
    {
        return Mage::helper('Mage_Core_Helper_Data')->uniqHash();
    }

    /**
     * Format values
     *
     * @param mixed $format
     * @param mixed $string
     * @return mixed
     */
    protected function _formatStr($format, $string)
    {
        return sprintf($format, $string);
    }

    /**
      * If response is failed throw exception
      * Set token data in payment object
      *
      * @param Varien_Object $response
      * @param Mage_Sales_Model_Order_Payment $payment
      * @throws Mage_Core_Exception
      */
    protected function _processTokenErrors($response, $payment)
    {
        if (!$response->getSecuretoken() &&
            $response->getResult() != self::RESPONSE_CODE_APPROVED
            && $response->getResult() != self::RESPONSE_CODE_FRAUDSERVICE_FILTER) {
            Mage::throwException($response->getRespmsg());
        } else {
            $payment->setAdditionalInformation('secure_token_id', $response->getSecuretokenid())
                ->setAdditionalInformation('secure_token', $response->getSecuretoken());
        }
    }

    /**
     * Return secure hash value for silent post request
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return string
     */
    protected function _getSecureSilentPostHash($payment)
    {
        return $payment->getAdditionalInformation($this->_secureSilentPostHashKey);
    }

    /**
     * Generate end return new secure hash value
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return string
     */
    protected function _generateSecureSilentPostHash($payment)
    {
        $secureHash = md5(Mage::helper('Mage_Core_Helper_Data')->getRandomString(10));
        $payment->setAdditionalInformation($this->_secureSilentPostHashKey, $secureHash);
        return $secureHash;
    }

    /**
     * Add transaction with correct transaction Id
     *
     * @deprecated since 1.6.2.0
     * @param Varien_Object $payment
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
     * @param Varien_Object $payment
     * @param  $amount
     * @return Mage_Paypal_Model_Payflowlink
     */
    protected function _initialize(Varien_Object $payment, $amount)
    {
        return $this;
    }

    /**
     * Check whether order review has enough data to initialize
     *
     * @deprecated since 1.6.2.0
     * @param $token
     * @throws Mage_Core_Exception
     */
    public function prepareOrderReview($token = null)
    {
    }

    /**
     * Additional authorization logic for Account Verification
     *
     * @deprecated since 1.6.2.0
     * @param Varien_Object $payment
     * @param mixed $amount
     * @param Mage_Paypal_Model_Payment_Transaction $transaction
     * @param string $txnId
     * @return Mage_Paypal_Model_Payflowlink
     */
    protected function _authorize(Varien_Object $payment, $amount, $transaction, $txnId)
    {
        return $this;
    }

    /**
     * Operate with order or quote using information from silent post
     *
     * @deprecated since 1.6.2.0
     * @param Varien_Object $document
     */
    protected function _process(Varien_Object $document)
    {
    }

    /**
     * Check Transaction
     *
     * @deprecated since 1.6.2.0
     * @param Mage_Paypal_Model_Payment_Transaction $transaction
     * @param mixed $amount
     * @return Mage_Paypal_Model_Payflowlink
     */
    protected function _checkTransaction($transaction, $amount)
    {
        return $this;
    }

    /**
     * Check response from Payflow gateway.
     *
     * @deprecated since 1.6.2.0
     * @return Mage_Sales_Model_Abstract in case of validation passed
     * @throws Mage_Core_Exception in other cases
     */
    protected function _getDocumentFromResponse()
    {
        return null;
    }
}
