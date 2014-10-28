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

use Exception;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

/**
 * PayPal Instant Payment Notification processor model
 */
class Ipn extends \Magento\Paypal\Model\AbstractIpn implements IpnInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * PayPal info instance
     *
     * @var Info
     */
    protected $_paypalInfo;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Info $paypalInfo
     * @param OrderSender $orderSender
     * @param CreditmemoSender $creditmemoSender
     * @param array $data
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Info $paypalInfo,
        OrderSender $orderSender,
        CreditmemoSender $creditmemoSender,
        array $data = array()
    ) {
        parent::__construct($configFactory, $logAdapterFactory, $curlFactory, $data);
        $this->_orderFactory = $orderFactory;
        $this->_paypalInfo = $paypalInfo;
        $this->orderSender = $orderSender;
        $this->creditmemoSender = $creditmemoSender;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @return void
     * @throws Exception
     */
    public function processIpnRequest()
    {
        $this->_addDebugData('ipn', $this->getRequestData());

        try {
            $this->_getConfig();
            $this->_postBack();
            $this->_processOrder();
        } catch (Exception $e) {
            $this->_addDebugData('exception', $e->getMessage());
            $this->_debug();
            throw $e;
        }
        $this->_debug();
    }

    /**
     * Get config with the method code and store id and validate
     *
     * @return \Magento\Paypal\Model\Config
     * @throws Exception
     */
    protected function _getConfig()
    {
        $order = $this->_getOrder();
        $methodCode = $order->getPayment()->getMethod();
        $parameters = array('params' => array($methodCode, $order->getStoreId()));
        $this->_config = $this->_configFactory->create($parameters);
        if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable()) {
            throw new Exception(sprintf('Method "%s" is not available.', $methodCode));
        }
        /** @link https://cms.paypal.com/cgi-bin/marketingweb?cmd=_render-content&content_ID=developer/e_howto_admin_IPNIntro */
        // verify merchant email intended to receive notification
        $merchantEmail = $this->_config->getConfigValue('businessAccount');
        if (!$merchantEmail) {
            return $this->_config;
        }
        $receiver = $this->getRequestData('business') ?: $this->getRequestData('receiver_email');
        if (strtolower($merchantEmail) != strtolower($receiver)) {
            throw new Exception(
                sprintf('The requested %s and configured %s merchant emails do not match.', $receiver, $merchantEmail)
            );
        }

        return $this->_config;
    }

    /**
     * Load order
     *
     * @return \Magento\Sales\Model\Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        $incrementId = $this->getRequestData('invoice');
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        if (!$this->_order->getId()) {
            throw new Exception(sprintf('Wrong order ID: "%s".', $incrementId));
        }
        return $this->_order;
    }

    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _processOrder()
    {
        $this->_getConfig();
        try {
            // Handle payment_status
            $transactionType = $this->getRequestData('txn_type');
            switch ($transactionType) {
                // handle new case created
                case Info::TXN_TYPE_NEW_CASE:
                    $this->_registerDispute();
                    break;
                    // handle new adjustment is created
                case Info::TXN_TYPE_ADJUSTMENT:
                    $this->_registerAdjustment();
                    break;
                    //handle new transaction created
                default:
                    $this->_registerTransaction();
                    break;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process dispute notification
     *
     * @return void
     */
    protected function _registerDispute()
    {
        $reasonComment = $this->_paypalInfo->explainReasonCode($this->getRequestData('reason_code'));
        $caseType = $this->getRequestData('case_type');
        $caseTypeLabel = $this->_paypalInfo->getCaseTypeLabel($caseType);
        $caseId = $this->getRequestData('case_id');
        //Add IPN comment about registered dispute
        $message = __(
            'IPN "%1". Case type "%2". Case ID "%3" %4',
            ucfirst($caseType),
            $caseTypeLabel,
            $caseId,
            $reasonComment
        );
        $this->_order->addStatusHistoryComment($message)->setIsCustomerNotified(false)->save();
    }

    /**
     * Process adjustment notification
     *
     * @return void
     */
    protected function _registerAdjustment()
    {
        $reasonCode = $this->getRequestData('reason_code');
        $reasonComment = $this->_paypalInfo->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order->getBaseCurrency()->formatTxt($this->getRequestData('mc_gross'));
        // Add IPN comment about registered dispute
        $message = __(
            'IPN "%1". A dispute has been resolved and closed. %2 Transaction amount %3.',
            ucfirst($reasonCode),
            $notificationAmount,
            $reasonComment
        );
        $this->_order->addStatusHistoryComment($message)->setIsCustomerNotified(false)->save();
    }

    /**
     * Process regular IPN notifications
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     * @throws Exception
     */
    protected function _registerTransaction()
    {
        try {
            // Handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->getRequestData('payment_status'));
            switch ($paymentStatus) {
                // paid
                case Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerPaymentCapture(true);
                    break;
                    // the holded payment was denied on paypal side
                case Info::PAYMENTSTATUS_DENIED:
                    $this->_registerPaymentDenial();
                    break;
                    // customer attempted to pay via bank account, but failed
                case Info::PAYMENTSTATUS_FAILED:
                    // cancel order
                    $this->_registerPaymentFailure();
                    break;
                    // payment was obtained, but money were not captured yet
                case Info::PAYMENTSTATUS_PENDING:
                    $this->_registerPaymentPending();
                    break;
                case Info::PAYMENTSTATUS_PROCESSED:
                    $this->_registerMasspaymentsSuccess();
                    break;
                case Info::PAYMENTSTATUS_REVERSED:
                    //break is intentionally omitted
                case Info::PAYMENTSTATUS_UNREVERSED:
                    $this->_registerPaymentReversal();
                    break;
                case Info::PAYMENTSTATUS_REFUNDED:
                    $this->_registerPaymentRefund();
                    break;
                    // authorization expire/void
                case Info::PAYMENTSTATUS_EXPIRED:
                    // break is intentionally omitted
                case Info::PAYMENTSTATUS_VOIDED:
                    $this->_registerPaymentVoid();
                    break;
                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }

    /**
     * Process completed payment (either full or partial)
     *
     * @param bool $skipFraudDetection
     * @return void
     */
    protected function _registerPaymentCapture($skipFraudDetection = false)
    {
        if ($this->getRequestData('transaction_entity') == 'auth') {
            return;
        }
        $parentTransactionId = $this->getRequestData('parent_txn_id');
        $this->_importPaymentInformation();
        $payment = $this->_order->getPayment();
        $payment->setTransactionId(
            $this->getRequestData('txn_id')
        );
        $payment->setCurrencyCode(
            $this->getRequestData('mc_currency')
        );
        $payment->setPreparedMessage(
            $this->_createIpnComment('')
        );
        $payment->setParentTransactionId(
            $parentTransactionId
        );
        $payment->setShouldCloseParentTransaction(
            'Completed' === $this->getRequestData('auth_status')
        );
        $payment->setIsTransactionClosed(
            0
        );
        $payment->registerCaptureNotification(
            $this->getRequestData('mc_gross'),
            $skipFraudDetection && $parentTransactionId
        );
        $this->_order->save();

        // notify customer
        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->_order->getEmailSent()) {
            $this->orderSender->send($this->_order);
            $this->_order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(
                true
            )->save();
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
        $this->_order->getPayment()->setTransactionId(
            $this->getRequestData('txn_id')
        )->setNotificationResult(
            true
        )->setIsTransactionClosed(
            true
        )->registerPaymentReviewAction(
            \Magento\Sales\Model\Order\Payment::REVIEW_ACTION_DENY,
            false
        );
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
        $this->_order->registerCancellation($this->_createIpnComment(''))->save();
    }

    /**
     * Process payment pending notification
     *
     * @return void
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
        if (\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT == $this->_order->getState()
            && !$this->getRequestData('transaction_entity')
        ) {
            $this->_registerPaymentCapture();
            return;
        }

        $this->_importPaymentInformation();

        $this->_order->getPayment()->setPreparedMessage(
            $this->_createIpnComment($this->_paypalInfo->explainPendingReason($reason))
        )->setTransactionId(
            $this->getRequestData('txn_id')
        )->setIsTransactionClosed(
            0
        )->registerPaymentReviewAction(
            \Magento\Sales\Model\Order\Payment::REVIEW_ACTION_UPDATE,
            false
        );
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
            $payment->setPreparedMessage(
                $this->_createIpnComment('')
            )->setTransactionId(
                $this->getRequestData('txn_id')
            )->setParentTransactionId(
                $this->getRequestData('parent_txn_id')
            )->setCurrencyCode(
                $this->getRequestData('mc_currency')
            )->setIsTransactionClosed(
                0
            )->registerAuthorizationNotification(
                $this->getRequestData('mc_gross')
            );
        }
        if (!$this->_order->getEmailSent()) {
            $this->orderSender->send($this->_order);
        }
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
     * Process payment reversal and cancelled reversal notification
     *
     * @return void
     */
    protected function _registerPaymentReversal()
    {
        $reasonCode = $this->getRequestData('reason_code');
        $reasonComment = $this->_paypalInfo->explainReasonCode($reasonCode);
        $notificationAmount = $this->_order->getBaseCurrency()->formatTxt(
            $this->getRequestData('mc_gross') + $this->getRequestData('mc_fee')
        );
        $paymentStatus = $this->_filterPaymentStatus($this->getRequestData('payment_status'));
        $orderStatus = $paymentStatus ==
            Info::PAYMENTSTATUS_REVERSED ? Info::ORDER_STATUS_REVERSED : Info::ORDER_STATUS_CANCELED_REVERSAL;
        //Change order status to PayPal Reversed/PayPal Cancelled Reversal if it is possible.
        $message = __(
            'IPN "%1". %2 Transaction amount %3. Transaction ID: "%4"',
            $this->getRequestData('payment_status'),
            $reasonComment,
            $notificationAmount,
            $this->getRequestData('txn_id')
        );
        $this->_order->setStatus($orderStatus);
        $this->_order->save();
        $this->_order->addStatusHistoryComment($message, $orderStatus)->setIsCustomerNotified(false)->save();
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
        $isRefundFinal = !$this->_paypalInfo->isReversalDisputable($reason);
        $payment = $this->_order->getPayment()->setPreparedMessage(
            $this->_createIpnComment($this->_paypalInfo->explainReasonCode($reason))
        )->setTransactionId(
            $this->getRequestData('txn_id')
        )->setParentTransactionId(
            $this->getRequestData('parent_txn_id')
        )->setIsTransactionClosed(
            $isRefundFinal
        )->registerRefundNotification(
            -1 * $this->getRequestData('mc_gross')
        );
        $this->_order->save();

        // TODO: there is no way to close a capture right now

        $creditMemo = $payment->getCreatedCreditmemo();
        if ($creditMemo) {
            $this->creditmemoSender->send($creditMemo);
            $this->_order->addStatusHistoryComment(
                __('You notified customer about creditmemo #%1.', $creditMemo->getIncrementId())
            )->setIsCustomerNotified(
                true
            )->save();
        }
    }

    /**
     * Process voided authorization
     *
     * @return void
     */
    protected function _registerPaymentVoid()
    {
        $this->_importPaymentInformation();

        $parentTxnId = $this->getRequestData(
            'transaction_entity'
        ) == 'auth' ? $this->getRequestData(
            'txn_id'
        ) : $this->getRequestData(
            'parent_txn_id'
        );

        $this->_order->getPayment()->setPreparedMessage(
            $this->_createIpnComment('')
        )->setParentTransactionId(
            $parentTxnId
        )->registerVoidNotification();

        $this->_order->save();
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
            Info::PAYER_ID,
            'payer_email' => Info::PAYER_EMAIL,
            Info::PAYER_STATUS,
            Info::ADDRESS_STATUS,
            Info::PROTECTION_EL,
            Info::PAYMENT_STATUS,
            Info::PENDING_REASON
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
            $from[Info::FRAUD_FILTERS] = $fraudFilters;
        }

        $this->_paypalInfo->importToPayment($from, $payment);

        /**
         * Detect pending payment, frauds
         * TODO: implement logic in one place
         * @see \Magento\Paypal\Model\Pro::importPaymentInfo()
         */
        if ($this->_paypalInfo->isPaymentReviewRequired($payment)) {
            $payment->setIsTransactionPending(true);
            if ($fraudFilters) {
                $payment->setIsFraudDetected(true);
            }
        }
        if ($this->_paypalInfo->isPaymentSuccessful($payment)) {
            $payment->setIsTransactionApproved(true);
        } elseif ($this->_paypalInfo->isPaymentFailed($payment)) {
            $payment->setIsTransactionDenied(true);
        }

        return $was != $payment->getAdditionalInformation();
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
        $message = __('IPN "%1"', $this->getRequestData('payment_status'));
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }
}
