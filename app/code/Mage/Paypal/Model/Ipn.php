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
 * PayPal Instant Payment Notification processor model
 */
class Mage_Paypal_Model_Ipn
{
    /**
     * Default log filename
     *
     * @var string
     */
    const DEFAULT_LOG_FILE = 'paypal_unknown_ipn.log';

    /*
     * @param Mage_Sales_Model_Order
     */
    protected $_order = null;

    /*
     * Recurring profile instance
     *
     * @var Mage_Sales_Model_Recurring_Profile
     */
    protected $_recurringProfile = null;

    /**
     *
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * PayPal info instance
     *
     * @var Mage_Paypal_Model_Info
     */
    protected $_info = null;

    /**
     * IPN request data
     * @var array
     */
    protected $_request = array();

    /**
     * Collected debug information
     *
     * @var array
     */
    protected $_debugData = array();

    /**
     * IPN request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_request;
        }
        return isset($this->_request[$key]) ? $this->_request[$key] : null;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @param array $request
     * @param Zend_Http_Client_Adapter_Interface $httpAdapter
     * @throws Exception
     */
    public function processIpnRequest(array $request, Zend_Http_Client_Adapter_Interface $httpAdapter = null)
    {
        $this->_request   = $request;
        $this->_debugData = array('ipn' => $request);
        ksort($this->_debugData['ipn']);

        try {
            if (isset($this->_request['txn_type']) && 'recurring_payment' == $this->_request['txn_type']) {
                $this->_getRecurringProfile();
                if ($httpAdapter) {
                    $this->_postBack($httpAdapter);
                }
                $this->_processRecurringProfile();
            } else {
                $this->_getOrder();
                if ($httpAdapter) {
                    $this->_postBack($httpAdapter);
                }
                $this->_processOrder();
            }
        } catch (Exception $e) {
            $this->_debugData['exception'] = $e->getMessage();
            $this->_debug();
            throw $e;
        }
        $this->_debug();
    }

    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @param Zend_Http_Client_Adapter_Interface $httpAdapter
     */
    protected function _postBack(Zend_Http_Client_Adapter_Interface $httpAdapter)
    {
        $postbackQuery = http_build_query($this->_request) . '&cmd=_notify-validate';
        $postbackUrl = $this->_config->getPaypalUrl();
        $this->_debugData['postback_to'] = $postbackUrl;

        $httpAdapter->setConfig(array('verifypeer' => $this->_config->verifyPeer));
        $httpAdapter->write(Zend_Http_Client::POST, $postbackUrl, '1.1', array('Connection: close'), $postbackQuery);
        try {
            $postbackResult = $httpAdapter->read();
        } catch (Exception $e) {
            $this->_debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            throw $e;
        }

        $response = preg_split('/^\r?$/m', $postbackResult, 2);
        $response = trim($response[1]);
        if ($response != 'VERIFIED') {
            $this->_debugData['postback'] = $postbackQuery;
            $this->_debugData['postback_result'] = $postbackResult;
            throw new Exception('PayPal IPN postback failure. See ' . self::DEFAULT_LOG_FILE . ' for details.');
        }
    }

    /**
     * Load and validate order, instantiate proper configuration
     *
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            // get proper order
            $id = $this->_request['invoice'];
            $this->_order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($id);
            if (!$this->_order->getId()) {
                $this->_debugData['exception'] = sprintf('Wrong order ID: "%s".', $id);
                $this->_debug();
                Mage::app()->getResponse()
                    ->setHeader('HTTP/1.1','503 Service Unavailable')
                    ->sendResponse();
                exit;
            }
            // re-initialize config with the method code and store id
            $method = $this->_order->getPayment()->getMethod();
            $parameters = array('params' => array($method, $this->_order->getStoreId()));
            $this->_config = Mage::getModel('Mage_Paypal_Model_Config', $parameters);
            if (!$this->_config->isMethodActive($method) || !$this->_config->isMethodAvailable()) {
                throw new Exception(sprintf('Method "%s" is not available.', $method));
            }

            $this->_verifyOrder();
        }
        return $this->_order;
    }

    /**
     * Load recurring profile
     *
     * @return Mage_Sales_Model_Recurring_Profile
     * @throws Exception
     */
    protected function _getRecurringProfile()
    {
        if (empty($this->_recurringProfile)) {
            // get proper recurring profile
            $internalReferenceId = $this->_request['rp_invoice_id'];
            $this->_recurringProfile = Mage::getModel('Mage_Sales_Model_Recurring_Profile')
                ->loadByInternalReferenceId($internalReferenceId);
            if (!$this->_recurringProfile->getId()) {
                throw new Exception(
                    sprintf('Wrong recurring profile INTERNAL_REFERENCE_ID: "%s".', $internalReferenceId)
                );
            }
            // re-initialize config with the method code and store id
            $methodCode = $this->_recurringProfile->getMethodCode();
            $parameters = array('params' => array($methodCode, $this->_recurringProfile->getStoreId()));
            $this->_config = Mage::getModel('Mage_Paypal_Model_Config', $parameters);
            if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable()) {
                throw new Exception(sprintf('Method "%s" is not available.', $methodCode));
            }
        }
        return $this->_recurringProfile;
    }

    /**
     * Validate incoming request data, as PayPal recommends
     *
     * @throws Exception
     * @link https://cms.paypal.com/cgi-bin/marketingweb?cmd=_render-content&content_ID=developer/e_howto_admin_IPNIntro
     */
    protected function _verifyOrder()
    {
        // verify merchant email intended to receive notification
        $merchantEmail = $this->_config->businessAccount;
        if ($merchantEmail) {
            $receiverEmail = $this->getRequestData('business');
            if (!$receiverEmail) {
                $receiverEmail = $this->getRequestData('receiver_email');
            }
            if (strtolower($merchantEmail) != strtolower($receiverEmail)) {
                throw new Exception(
                    sprintf(
                        'The requested %s and configured %s merchant emails do not match.', $receiverEmail, $merchantEmail
                    )
                );
            }
        }
    }

    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     */
    protected function _processOrder()
    {
        $this->_order = null;
        $this->_getOrder();
        $this->_info = Mage::getSingleton('Mage_Paypal_Model_Info');
        try {
            // Handle payment_status
            $transactionType = isset($this->_request['txn_type']) ? $this->_request['txn_type'] : null;
            switch ($transactionType) {
                // handle new case created
                case Mage_Paypal_Model_Info::TXN_TYPE_NEW_CASE:
                    $this->_registerDispute();
                    break;

                // handle new adjustment is created
                case Mage_Paypal_Model_Info::TXN_TYPE_ADJUSTMENT:
                    $this->_registerAdjustment();
                    break;

                //handle new transaction created
                default:
                    $this->_registerTransaction();
            }
        } catch (Mage_Core_Exception $e) {
            $comment = $this->_createIpnComment(Mage::helper('Mage_Paypal_Helper_Data')->__('Note: %s', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process adjustment notification
     */
    protected function _registerAdjustment()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order->getBaseCurrency()->formatTxt($this->_request['mc_gross']);
        /**
         *  Add IPN comment about registered dispute
         */
        $message = Mage::helper('Mage_Paypal_Helper_Data')->__('IPN "%s". A dispute has been resolved and closed. %s Transaction amount %s.', ucfirst($reasonCode), $notificationAmount, $reasonComment);
        $this->_order->addStatusHistoryComment($message)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process dispute notification
     */
    protected function _registerDispute()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $caseType = isset($this->_request['case_type']) ? $this->_request['case_type'] : null;
        $caseTypeLabel = $this->_info->getCaseTypeLabel($caseType);
        $caseId = isset($this->_request['case_id']) ? $this->_request['case_id'] : null;
        /**
         *  Add IPN comment about registered dispute
         */
        $message = Mage::helper('Mage_Paypal_Helper_Data')->__('IPN "%s". Case type "%s". Case ID "%s" %s', ucfirst($caseType), $caseTypeLabel, $caseId, $reasonComment);
        $this->_order->addStatusHistoryComment($message)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process payment reversal and cancelled reversal notification
     */
    protected function _registerPaymentReversal()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order
            ->getBaseCurrency()
            ->formatTxt($this->_request['mc_gross'] + $this->_request['mc_fee']);
        $paymentStatus = $this->_filterPaymentStatus(isset($this->_request['payment_status'])
                ? $this->_request['payment_status']
                : null
        );
        $orderStatus = ($paymentStatus == Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED)
            ? Mage_Paypal_Model_Info::ORDER_STATUS_REVERSED
            : Mage_Paypal_Model_Info::ORDER_STATUS_CANCELED_REVERSAL;
        /**
         * Change order status to PayPal Reversed/PayPal Cancelled Reversal if it is possible.
         */
        $message = Mage::helper('Mage_Paypal_Helper_Data')->__('IPN "%s". %s Transaction amount %s. Transaction ID: "%s"', $this->_request['payment_status'], $reasonComment, $notificationAmount, $this->_request['txn_id']);
        $this->_order->setStatus($orderStatus);
        $this->_order->save();
        $this->_order->addStatusHistoryComment($message, $orderStatus)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process regular IPN notifications
     */
    protected function _registerTransaction()
    {
        try {
            // Handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);
            switch ($paymentStatus) {
                // paid
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerPaymentCapture();
                    break;

                // the holded payment was denied on paypal side
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_DENIED:
                    $this->_registerPaymentDenial();
                    break;

                // customer attempted to pay via bank account, but failed
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_FAILED:
                    // cancel order
                    $this->_registerPaymentFailure();
                    break;

                // payment was obtained, but money were not captured yet
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING:
                    $this->_registerPaymentPending();
                    break;

                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PROCESSED:
                    $this->_registerMasspaymentsSuccess();
                    break;

                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED:// break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_UNREVERSED:
                    $this->_registerPaymentReversal();
                    break;

                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED:
                    $this->_registerPaymentRefund();
                    break;
                // authorization expire/void
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_EXPIRED: // break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_VOIDED:
                    $this->_registerPaymentVoid();
                    break;

                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (Mage_Core_Exception $e) {
            $comment = $this->_createIpnComment(Mage::helper('Mage_Paypal_Helper_Data')->__('Note: %s', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process notification from recurring profile payments
     */
    protected function _processRecurringProfile()
    {
        $this->_recurringProfile = null;
        $this->_getRecurringProfile();

        try {
            // handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);

            switch ($paymentStatus) {
                // paid
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerRecurringProfilePaymentCapture();
                    break;

                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (Mage_Core_Exception $e) {
// TODO: add to payment profile comments
//            $comment = $this->_createIpnComment(Mage::helper('Mage_Paypal_Helper_Data')->__('Note: %s', $e->getMessage()), true);
//            $comment->save();
            throw $e;
        }
    }

    /**
     * Register recurring payment notification, create and process order
     */
    protected function _registerRecurringProfilePaymentCapture()
    {
        $price = $this->getRequestData('mc_gross') - $this->getRequestData('tax') -  $this->getRequestData('shipping');
        $productItemInfo = new Varien_Object;
        $type = trim($this->getRequestData('period_type'));
        if ($type == 'Trial') {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
        } elseif ($type == 'Regular') {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
        }
        $productItemInfo->setTaxAmount($this->getRequestData('tax'));
        $productItemInfo->setShippingAmount($this->getRequestData('shipping'));
        $productItemInfo->setPrice($price);

        $order = $this->_recurringProfile->createOrder($productItemInfo);

        $payment = $order->getPayment();
        $payment->setTransactionId($this->getRequestData('txn_id'))
            ->setCurrencyCode($this->getRequestData('mc_currency'))
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setIsTransactionClosed(0);
        $order->save();
        $this->_recurringProfile->addOrderRelation($order->getId());
        $payment->registerCaptureNotification($this->getRequestData('mc_gross'));
        $order->save();

        // notify customer
        if ($invoice = $payment->getCreatedInvoice()) {
            $message = Mage::helper('Mage_Paypal_Helper_Data')->__('You notified customer about invoice #%s.', $invoice->getIncrementId());
            $comment = $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    /**
     * Process completed payment (either full or partial)
     */
    protected function _registerPaymentCapture()
    {
        if ($this->getRequestData('transaction_entity') == 'auth') {
            return;
        }
        $this->_importPaymentInformation();
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($this->getRequestData('txn_id'))
            ->setCurrencyCode($this->getRequestData('mc_currency'))
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setParentTransactionId($this->getRequestData('parent_txn_id'))
            ->setShouldCloseParentTransaction('Completed' === $this->getRequestData('auth_status'))
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification($this->getRequestData('mc_gross'));
        $this->_order->save();

        // notify customer
        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->_order->getEmailSent()) {
            $this->_order->sendNewOrderEmail()->addStatusHistoryComment(
                Mage::helper('Mage_Paypal_Helper_Data')->__('You notified customer about invoice #%s.', $invoice->getIncrementId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }

    /**
     * Process denied payment notification
     */
    protected function _registerPaymentDenial()
    {
        $this->_importPaymentInformation();
        $this->_order->getPayment()
            ->setTransactionId($this->getRequestData('txn_id'))
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true)
            ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_DENY, false);
        $this->_order->save();
    }

    /**
     * Treat failed payment as order cancellation
     */
    protected function _registerPaymentFailure()
    {
        $this->_importPaymentInformation();
        $this->_order
            ->registerCancellation($this->_createIpnComment(''), false)
            ->save();
    }

    /**
     * Process a refund
     */
    protected function _registerPaymentRefund()
    {
        $this->_importPaymentInformation();
        $reason = $this->getRequestData('reason_code');
        $isRefundFinal = !$this->_info->isReversalDisputable($reason);
        $payment = $this->_order->getPayment()
            ->setPreparedMessage($this->_createIpnComment($this->_info->explainReasonCode($reason)))
            ->setTransactionId($this->getRequestData('txn_id'))
            ->setParentTransactionId($this->getRequestData('parent_txn_id'))
            ->setIsTransactionClosed($isRefundFinal)
            ->registerRefundNotification(-1 * $this->getRequestData('mc_gross'));
        $this->_order->save();

        // TODO: there is no way to close a capture right now

        if ($creditmemo = $payment->getCreatedCreditmemo()) {
            $creditmemo->sendEmail();
            $comment = $this->_order->addStatusHistoryComment(
                    Mage::helper('Mage_Paypal_Helper_Data')->__('You notified customer about creditmemo #%s.', $creditmemo->getIncrementId())
                )
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    /**
     * Process payment pending notification
     *
     * @throws Exception
     */
    public function _registerPaymentPending()
    {
        $reason = $this->getRequestData('pending_reason');
        if ('authorization' === $reason) {
            $this->_registerPaymentAuthorization();
            return;
        }
        if ('order' === $reason) {
            throw new Exception('The "order" authorizations are not implemented.');
        }

        // case when was placed using PayPal standard
        if (Mage_Sales_Model_Order::STATE_PENDING_PAYMENT == $this->_order->getState()) {
            $this->_registerPaymentCapture();
            return;
        }

        $this->_importPaymentInformation();

        $this->_order->getPayment()
            ->setPreparedMessage($this->_createIpnComment($this->_info->explainPendingReason($reason)))
            ->setTransactionId($this->getRequestData('txn_id'))
            ->setIsTransactionClosed(0)
            ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, false);
        $this->_order->save();
    }

    /**
     * Register authorized payment
     */
    protected function _registerPaymentAuthorization()
    {
        /** @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $this->_order->getPayment();
        if ($this->_order->canFetchPaymentReviewUpdate()) {
            $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, true);
        } else {
            $this->_importPaymentInformation();
            $payment
                ->setPreparedMessage($this->_createIpnComment(''))
                ->setTransactionId($this->getRequestData('txn_id'))
                ->setParentTransactionId($this->getRequestData('parent_txn_id'))
                ->setIsTransactionClosed(0)
                ->registerAuthorizationNotification($this->getRequestData('mc_gross'));
        }
        if (!$this->_order->getEmailSent()) {
            $this->_order->sendNewOrderEmail();
        }
        $this->_order->save();
    }

    /**
     * Process voided authorization
     */
    protected function _registerPaymentVoid()
    {
        $this->_importPaymentInformation();

        $parentTxnId = $this->getRequestData('transaction_entity') == 'auth'
            ? $this->getRequestData('txn_id') : $this->getRequestData('parent_txn_id');

        $this->_order->getPayment()
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setParentTransactionId($parentTxnId)
            ->registerVoidNotification();

        $this->_order->save();
    }

    /**
     * TODO
     * The status "Processed" is used when all Masspayments are successful
     */
    protected function _registerMasspaymentsSuccess()
    {
        $comment = $this->_createIpnComment('', true);
        $comment->save();
    }

    /**
     * Generate an "IPN" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param string $comment
     * @param bool $addToHistory
     * @return string|Mage_Sales_Model_Order_Status_History
     */
    protected function _createIpnComment($comment = '', $addToHistory = false)
    {
        $paymentStatus = $this->getRequestData('payment_status');
        $message = Mage::helper('Mage_Paypal_Helper_Data')->__('IPN "%s"', $paymentStatus);
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }

    /**
     * Map payment information from IPN to payment object
     * Returns true if there were changes in information
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    protected function _importPaymentInformation()
    {
        $payment = $this->_order->getPayment();
        $was = $payment->getAdditionalInformation();

        // collect basic information
        $from = array();
        foreach (array(
            Mage_Paypal_Model_Info::PAYER_ID,
            'payer_email' => Mage_Paypal_Model_Info::PAYER_EMAIL,
            Mage_Paypal_Model_Info::PAYER_STATUS,
            Mage_Paypal_Model_Info::ADDRESS_STATUS,
            Mage_Paypal_Model_Info::PROTECTION_EL,
            Mage_Paypal_Model_Info::PAYMENT_STATUS,
            Mage_Paypal_Model_Info::PENDING_REASON,
        ) as $privateKey => $publicKey) {
            if (is_int($privateKey)) {
                $privateKey = $publicKey;
            }
            $value = $this->getRequestData($privateKey);
            if ($value) {
                $from[$publicKey] = $value;
            }
        }
        if (isset($from['payment_status'])) {
            $from['payment_status'] = $this->_filterPaymentStatus($this->getRequestData('payment_status'));
        }

        // collect fraud filters
        $fraudFilters = array();
        for ($i = 1; $value = $this->getRequestData("fraud_management_pending_filters_{$i}"); $i++) {
            $fraudFilters[] = $value;
        }
        if ($fraudFilters) {
            $from[Mage_Paypal_Model_Info::FRAUD_FILTERS] = $fraudFilters;
        }

        $this->_info->importToPayment($from, $payment);

        /**
         * Detect pending payment, frauds
         * TODO: implement logic in one place
         * @see Mage_Paypal_Model_Pro::importPaymentInfo()
         */
        if ($this->_info->isPaymentReviewRequired($payment)) {
            $payment->setIsTransactionPending(true);
            if ($fraudFilters) {
                $payment->setIsFraudDetected(true);
            }
        }
        if ($this->_info->isPaymentSuccessful($payment)) {
            $payment->setIsTransactionApproved(true);
        } elseif ($this->_info->isPaymentFailed($payment)) {
            $payment->setIsTransactionDenied(true);
        }

        return $was != $payment->getAdditionalInformation();
    }

    /**
     * Filter payment status from NVP into paypal/info format
     *
     * @param string $ipnPaymentStatus
     * @return string
     */
    protected function _filterPaymentStatus($ipnPaymentStatus)
    {
        switch ($ipnPaymentStatus) {
            case 'Created': // break is intentionally omitted
            case 'Completed': return Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED;
            case 'Denied':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_DENIED;
            case 'Expired':   return Mage_Paypal_Model_Info::PAYMENTSTATUS_EXPIRED;
            case 'Failed':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_FAILED;
            case 'Pending':   return Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING;
            case 'Refunded':  return Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED;
            case 'Reversed':  return Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED;
            case 'Canceled_Reversal': return Mage_Paypal_Model_Info::PAYMENTSTATUS_UNREVERSED;
            case 'Processed': return Mage_Paypal_Model_Info::PAYMENTSTATUS_PROCESSED;
            case 'Voided':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_VOIDED;
        }
        return '';
// documented in NVP, but not documented in IPN:
//Mage_Paypal_Model_Info::PAYMENTSTATUS_NONE
//Mage_Paypal_Model_Info::PAYMENTSTATUS_INPROGRESS
//Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDEDPART
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    protected function _debug()
    {
        if ($this->_config && $this->_config->debug) {
            $file = $this->_config->getMethodCode() ? "payment_{$this->_config->getMethodCode()}.log"
                : self::DEFAULT_LOG_FILE;
            Mage::getModel('Mage_Core_Model_Log_Adapter', array('fileName' => $file))->log($this->_debugData);
        }
    }
}
