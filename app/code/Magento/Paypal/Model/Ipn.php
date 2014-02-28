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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

/**
 * PayPal Instant Payment Notification processor model
 */
class Ipn
{
    /**
     * Default log filename
     */
    const DEFAULT_LOG_FILE = 'paypal_unknown_ipn.log';

    /**
     * Sales order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Recurring profile instance
     *
     * @var \Magento\RecurringProfile\Model\Profile
     */
    protected $_recurringProfile;

    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * PayPal info instance
     *
     * @var \Magento\Paypal\Model\Info
     */
    protected $_info;

    /**
     * IPN request data
     *
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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_responseHttp;

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var \Magento\RecurringProfile\Model\ProfileFactory
     */
    protected $_recurringProfileFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\App\ResponseInterface $responseHttp
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\RecurringProfile\Model\ProfileFactory $recurringProfileFactory
     * @param \Magento\Paypal\Model\Info $paypalInfo
     * @param \Magento\Logger\AdapterFactory $logAdapterFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\App\ResponseInterface $responseHttp,
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\RecurringProfile\Model\ProfileFactory $recurringProfileFactory,
        \Magento\Paypal\Model\Info $paypalInfo,
        \Magento\Logger\AdapterFactory $logAdapterFactory
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_responseHttp = $responseHttp;
        $this->_configFactory = $configFactory;
        $this->_recurringProfileFactory = $recurringProfileFactory;
        $this->_info = $paypalInfo;
        $this->_logAdapterFactory = $logAdapterFactory;
    }

    /**
     * IPN request data getter
     *
     * @param string|null $key
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
     * @param \Zend_Http_Client_Adapter_Interface|null $httpAdapter
     * @return void
     * @throws \Exception
     */
    public function processIpnRequest(array $request, \Zend_Http_Client_Adapter_Interface $httpAdapter = null)
    {
        $this->_request = $request;
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
        } catch (\Exception $e) {
            $this->_debugData['exception'] = $e->getMessage();
            $this->_debug();
            throw $e;
        }
        $this->_debug();
    }

    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @param \Zend_Http_Client_Adapter_Interface $httpAdapter
     * @return void
     * @throws \Exception
     */
    protected function _postBack(\Zend_Http_Client_Adapter_Interface $httpAdapter)
    {
        $postbackQuery = http_build_query($this->_request) . '&cmd=_notify-validate';
        $postbackUrl = $this->_config->getPaypalUrl();
        $this->_debugData['postback_to'] = $postbackUrl;

        $httpAdapter->setConfig(array('verifypeer' => $this->_config->verifyPeer));
        $httpAdapter->write(\Zend_Http_Client::POST, $postbackUrl, '1.1', array('Connection: close'), $postbackQuery);
        try {
            $postbackResult = $httpAdapter->read();
        } catch (\Exception $e) {
            $this->_debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            throw $e;
        }

        $response = preg_split('/^\r?$/m', $postbackResult, 2);
        $response = trim($response[1]);
        if ($response != 'VERIFIED') {
            $this->_debugData['postback'] = $postbackQuery;
            $this->_debugData['postback_result'] = $postbackResult;
            throw new \Exception('PayPal IPN postback failure. See ' . self::DEFAULT_LOG_FILE . ' for details.');
        }
    }

    /**
     * Load and validate order, instantiate proper configuration
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            // get proper order
            $id = $this->_request['invoice'];
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($id);
            if (!$this->_order->getId()) {
                $this->_debugData['exception'] = sprintf('Wrong order ID: "%s".', $id);
                $this->_debug();
                $this->_responseHttp->setHeader('HTTP/1.1', '503 Service Unavailable')->sendResponse();
                exit;
            }
            // re-initialize config with the method code and store id
            $method = $this->_order->getPayment()->getMethod();
            $parameters = array('params' => array($method, $this->_order->getStoreId()));
            $this->_config = $this->_configFactory->create($parameters);
            if (!$this->_config->isMethodActive($method) || !$this->_config->isMethodAvailable()) {
                throw new \Exception(sprintf('Method "%s" is not available.', $method));
            }

            $this->_verifyOrder();
        }
        return $this->_order;
    }

    /**
     * Load recurring profile
     *
     * @return \Magento\RecurringProfile\Model\Profile
     * @throws \Exception
     */
    protected function _getRecurringProfile()
    {
        if (empty($this->_recurringProfile)) {
            // get proper recurring profile
            $internalReferenceId = $this->_request['rp_invoice_id'];
            $this->_recurringProfile = $this->_recurringProfileFactory->create()
                ->loadByInternalReferenceId($internalReferenceId);
            if (!$this->_recurringProfile->getId()) {
                throw new \Exception(
                    sprintf('Wrong recurring profile INTERNAL_REFERENCE_ID: "%s".', $internalReferenceId)
                );
            }
            // re-initialize config with the method code and store id
            $methodCode = $this->_recurringProfile->getMethodCode();
            $parameters = array('params' => array($methodCode, $this->_recurringProfile->getStoreId()));
            $this->_config = $this->_configFactory->create($parameters);
            if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable()) {
                throw new \Exception(sprintf('Method "%s" is not available.', $methodCode));
            }
        }
        return $this->_recurringProfile;
    }

    /**
     * Validate incoming request data, as PayPal recommends
     *
     * @throws \Exception
     * @return void
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
                throw new \Exception(sprintf(
                    'The requested %s and configured %s merchant emails do not match.',
                    $receiverEmail,
                    $merchantEmail
                ));
            }
        }
    }

    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     *
     * @return void
     */
    protected function _processOrder()
    {
        $this->_order = null;
        $this->_getOrder();
        try {
            // Handle payment_status
            $transactionType = isset($this->_request['txn_type']) ? $this->_request['txn_type'] : null;
            switch ($transactionType) {
                // handle new case created
                case \Magento\Paypal\Model\Info::TXN_TYPE_NEW_CASE:
                    $this->_registerDispute();
                    break;

                // handle new adjustment is created
                case \Magento\Paypal\Model\Info::TXN_TYPE_ADJUSTMENT:
                    $this->_registerAdjustment();
                    break;

                //handle new transaction created
                default:
                    $this->_registerTransaction();
                    break;
            }
        } catch (\Magento\Core\Exception $e) {
            $comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process adjustment notification
     *
     * @return void
     */
    protected function _registerAdjustment()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order->getBaseCurrency()->formatTxt($this->_request['mc_gross']);
        /**
         *  Add IPN comment about registered dispute
         */
        $message = __(
            'IPN "%1". A dispute has been resolved and closed. %2 Transaction amount %3.',
            ucfirst($reasonCode),
            $notificationAmount,
            $reasonComment
        );
        $this->_order->addStatusHistoryComment($message)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process dispute notification
     *
     * @return void
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
        $message = __(
            'IPN "%1". Case type "%2". Case ID "%3" %4',
            ucfirst($caseType),
            $caseTypeLabel,
            $caseId,
            $reasonComment
        );
        $this->_order->addStatusHistoryComment($message)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process payment reversal and cancelled reversal notification
     *
     * @return void
     */
    protected function _registerPaymentReversal()
    {
        $reasonCode = isset($this->_request['reason_code']) ? $this->_request['reason_code'] : null;
        $reasonComment = $this->_info->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order
            ->getBaseCurrency()
            ->formatTxt($this->_request['mc_gross'] + $this->_request['mc_fee']);
        $paymentStatus = $this->_filterPaymentStatus(
            isset($this->_request['payment_status'])
                ? $this->_request['payment_status']
                : null
        );
        $orderStatus = ($paymentStatus == \Magento\Paypal\Model\Info::PAYMENTSTATUS_REVERSED)
            ? \Magento\Paypal\Model\Info::ORDER_STATUS_REVERSED
            : \Magento\Paypal\Model\Info::ORDER_STATUS_CANCELED_REVERSAL;
        /**
         * Change order status to PayPal Reversed/PayPal Cancelled Reversal if it is possible.
         */
        $message = __(
            'IPN "%1". %2 Transaction amount %3. Transaction ID: "%4"',
            $this->_request['payment_status'],
            $reasonComment,
            $notificationAmount,
            $this->_request['txn_id']
        );
        $this->_order->setStatus($orderStatus);
        $this->_order->save();
        $this->_order->addStatusHistoryComment($message, $orderStatus)
            ->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * Process regular IPN notifications
     *
     * @return void
     */
    protected function _registerTransaction()
    {
        try {
            // Handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);
            switch ($paymentStatus) {
                // paid
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerPaymentCapture();
                    break;

                // the holded payment was denied on paypal side
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_DENIED:
                    $this->_registerPaymentDenial();
                    break;

                // customer attempted to pay via bank account, but failed
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_FAILED:
                    // cancel order
                    $this->_registerPaymentFailure();
                    break;

                // payment was obtained, but money were not captured yet
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_PENDING:
                    $this->_registerPaymentPending();
                    break;

                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_PROCESSED:
                    $this->_registerMasspaymentsSuccess();
                    break;

                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_REVERSED: //break is intentionally omitted
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_UNREVERSED:
                    $this->_registerPaymentReversal();
                    break;

                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_REFUNDED:
                    $this->_registerPaymentRefund();
                    break;
                // authorization expire/void
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_EXPIRED: // break is intentionally omitted
                case \Magento\Paypal\Model\Info::PAYMENTSTATUS_VOIDED:
                    $this->_registerPaymentVoid();
                    break;

                default:
                    throw new \Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (\Magento\Core\Exception $e) {
            $comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process notification from recurring profile payments
     *
     * @return void
     */
    protected function _processRecurringProfile()
    {
        $this->_recurringProfile = null;
        $this->_getRecurringProfile();

        try {
            // handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);
            if ($paymentStatus != \Magento\Paypal\Model\Info::PAYMENTSTATUS_COMPLETED) {
                throw new \Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
            // Register recurring payment notification, create and process order
            $price = $this->getRequestData('mc_gross') - $this->getRequestData('tax')
                - $this->getRequestData('shipping');
            $productItemInfo = new \Magento\Object;
            $type = trim($this->getRequestData('period_type'));
            if ($type == 'Trial') {
                $productItemInfo->setPaymentType(\Magento\RecurringProfile\Model\PaymentTypeInterface::TRIAL);
            } elseif ($type == 'Regular') {
                $productItemInfo->setPaymentType(\Magento\RecurringProfile\Model\PaymentTypeInterface::REGULAR);
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
            $invoice = $payment->getCreatedInvoice();
            if ($invoice) {
                $message = __('You notified customer about invoice #%1.', $invoice->getIncrementId());
                $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                    ->setIsCustomerNotified(true)
                    ->save();
            }
        } catch (\Magento\Core\Exception $e) {
            //TODO: add to payment profile comments
            //$comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            //$comment->save();
            throw $e;
        }
    }

    /**
     * Process completed payment (either full or partial)
     *
     * @return void
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
            $this->_order->sendNewOrderEmail()
                ->addStatusHistoryComment(__('You notified customer about invoice #%1.', $invoice->getIncrementId()))
                ->setIsCustomerNotified(true)
                ->save();
        }
    }

    /**
     * Process denied payment notification
     *
     * @return void
     */
    protected function _registerPaymentDenial()
    {
        $this->_importPaymentInformation();
        $this->_order->getPayment()
            ->setTransactionId($this->getRequestData('txn_id'))
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true)
            ->registerPaymentReviewAction(\Magento\Sales\Model\Order\Payment::REVIEW_ACTION_DENY, false);
        $this->_order->save();
    }

    /**
     * Treat failed payment as order cancellation
     *
     * @return void
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
     *
     * @return void
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

        $creditMemo = $payment->getCreatedCreditmemo();
        if ($creditMemo) {
            $creditMemo->sendEmail();
            $this->_order->addStatusHistoryComment(
                __('You notified customer about creditmemo #%1.', $creditMemo->getIncrementId())
            )->setIsCustomerNotified(true)->save();
        }
    }

    /**
     * Process payment pending notification
     *
     * @return void
     * @throws \Exception
     */
    public function _registerPaymentPending()
    {
        $reason = $this->getRequestData('pending_reason');
        if ('authorization' === $reason) {
            $this->_registerPaymentAuthorization();
            return;
        }
        if ('order' === $reason) {
            throw new \Exception('The "order" authorizations are not implemented.');
        }

        // case when was placed using PayPal standard
        if (\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT == $this->_order->getState()) {
            $this->_registerPaymentCapture();
            return;
        }

        $this->_importPaymentInformation();

        $this->_order->getPayment()
            ->setPreparedMessage($this->_createIpnComment($this->_info->explainPendingReason($reason)))
            ->setTransactionId($this->getRequestData('txn_id'))
            ->setIsTransactionClosed(0)
            ->registerPaymentReviewAction(\Magento\Sales\Model\Order\Payment::REVIEW_ACTION_UPDATE, false);
        $this->_order->save();
    }

    /**
     * Register authorized payment
     *
     * @return void
     */
    protected function _registerPaymentAuthorization()
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $this->_order->getPayment();
        if ($this->_order->canFetchPaymentReviewUpdate()) {
            $payment->registerPaymentReviewAction(\Magento\Sales\Model\Order\Payment::REVIEW_ACTION_UPDATE, true);
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
     *
     * @return void
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
     * The status "Processed" is used when all Masspayments are successful
     *
     * @return void
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
     * @return string|\Magento\Sales\Model\Order\Status\History
     */
    protected function _createIpnComment($comment = '', $addToHistory = false)
    {
        $paymentStatus = $this->getRequestData('payment_status');
        $message = __('IPN "%1"', $paymentStatus);
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
     * @return bool
     */
    protected function _importPaymentInformation()
    {
        $payment = $this->_order->getPayment();
        $was = $payment->getAdditionalInformation();

        // collect basic information
        $from = array();
        foreach (array(
            \Magento\Paypal\Model\Info::PAYER_ID,
            'payer_email' => \Magento\Paypal\Model\Info::PAYER_EMAIL,
            \Magento\Paypal\Model\Info::PAYER_STATUS,
            \Magento\Paypal\Model\Info::ADDRESS_STATUS,
            \Magento\Paypal\Model\Info::PROTECTION_EL,
            \Magento\Paypal\Model\Info::PAYMENT_STATUS,
            \Magento\Paypal\Model\Info::PENDING_REASON,
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
            $from[\Magento\Paypal\Model\Info::FRAUD_FILTERS] = $fraudFilters;
        }

        $this->_info->importToPayment($from, $payment);

        /**
         * Detect pending payment, frauds
         * TODO: implement logic in one place
         * @see \Magento\Paypal\Model\Pro::importPaymentInfo()
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
            case 'Completed': return \Magento\Paypal\Model\Info::PAYMENTSTATUS_COMPLETED;
            case 'Denied':    return \Magento\Paypal\Model\Info::PAYMENTSTATUS_DENIED;
            case 'Expired':   return \Magento\Paypal\Model\Info::PAYMENTSTATUS_EXPIRED;
            case 'Failed':    return \Magento\Paypal\Model\Info::PAYMENTSTATUS_FAILED;
            case 'Pending':   return \Magento\Paypal\Model\Info::PAYMENTSTATUS_PENDING;
            case 'Refunded':  return \Magento\Paypal\Model\Info::PAYMENTSTATUS_REFUNDED;
            case 'Reversed':  return \Magento\Paypal\Model\Info::PAYMENTSTATUS_REVERSED;
            case 'Canceled_Reversal': return \Magento\Paypal\Model\Info::PAYMENTSTATUS_UNREVERSED;
            case 'Processed': return \Magento\Paypal\Model\Info::PAYMENTSTATUS_PROCESSED;
            case 'Voided':    return \Magento\Paypal\Model\Info::PAYMENTSTATUS_VOIDED;
        }
        return '';
        // documented in NVP, but not documented in IPN:
        //Magento_Paypal_Model_Info::PAYMENTSTATUS_NONE
        //Magento_Paypal_Model_Info::PAYMENTSTATUS_INPROGRESS
        //Magento_Paypal_Model_Info::PAYMENTSTATUS_REFUNDEDPART
    }

    /**
     * Log debug data to file
     *
     * @return void
     */
    protected function _debug()
    {
        if ($this->_config && $this->_config->debug) {
            $file = $this->_config->getMethodCode()
                ? "payment_{$this->_config->getMethodCode()}.log"
                : self::DEFAULT_LOG_FILE;
            $this->_logAdapterFactory->create(array('fileName' => $file))->log($this->_debugData);
        }
    }
}
