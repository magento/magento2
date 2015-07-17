<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Payment\Info;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Order payment information
 *
 * @method \Magento\Sales\Model\Resource\Order\Payment _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Payment getResource()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends Info implements OrderPaymentInterface
{
    /**
     * Actions for payment when it triggered review state
     *
     * @var string
     */
    const REVIEW_ACTION_ACCEPT = 'accept';

    const REVIEW_ACTION_DENY = 'deny';

    const REVIEW_ACTION_UPDATE = 'update';

    /**
     * Order model object
     *
     * @var Order
     */
    protected $_order;

    /**
     * Whether can void
     * @var string
     */
    protected $_canVoidLookup = null;

    /**
     * Transactions registry to spare resource calls
     * array(txn_id => sales/order_payment_transaction)
     *
     * @var array
     */
    protected $_transactionsLookup = [];

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_payment';

    /**
     * @var string
     */
    protected $_eventObject = 'payment';

    /**
     * Transaction additional information container
     *
     * @var array
     */
    protected $_transactionAdditionalInfo = [];

    /**
     * @var \Magento\Sales\Model\Service\Order
     */
    protected $_serviceOrderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Service\OrderFactory $serviceOrderFactory
     * @param Payment\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Service\OrderFactory $serviceOrderFactory,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $transactionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_serviceOrderFactory = $serviceOrderFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Payment');
    }

    /**
     * Declare order model object
     *
     * @codeCoverageIgnore
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @codeCoverageIgnore
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Sets transaction id for current payment
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->setData('transaction_id', $transactionId);
        return $this;
    }

    /**
     * Sets transaction close flag
     *
     * @param bool $isClosed
     * @return $this
     */
    public function setIsTransactionClosed($isClosed)
    {
        $this->setData('is_transaction_closed', (bool)$isClosed);
        return $this;
    }

    /**
     * Returns transaction parent
     *
     * @return string
     */
    public function getParentTransactionId()
    {
        return $this->getData('parent_transaction_id');
    }

    /**
     * Check order payment capture action availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if (!$this->getMethodInstance()->canCapture()) {
            return false;
        }
        // Check Authorization transaction state
        $authTransaction = $this->getAuthorizationTransaction();
        if ($authTransaction && $authTransaction->getIsClosed()) {
            $orderTransaction = $this->_lookupTransaction(
                null,
                Transaction::TYPE_ORDER
            );
            if (!$orderTransaction) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        return $this->getMethodInstance()->canRefund();
    }

    /**
     * @return bool
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->getMethodInstance()->canRefundPartialPerInvoice();
    }

    /**
     * @return bool
     */
    public function canCapturePartial()
    {
        return $this->getMethodInstance()->canCapturePartial();
    }

    /**
     * Authorize or authorize and capture payment on gateway, if applicable
     * This method is supposed to be called only when order is placed
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function place()
    {
        $this->_eventManager->dispatch('sales_order_payment_place_start', ['payment' => $this]);
        $order = $this->getOrder();

        $this->setAmountOrdered($order->getTotalDue());
        $this->setBaseAmountOrdered($order->getBaseTotalDue());
        $this->setShippingAmount($order->getShippingAmount());
        $this->setBaseShippingAmount($order->getBaseShippingAmount());

        $methodInstance = $this->getMethodInstance();
        $methodInstance->setStore($order->getStoreId());

        $orderState = Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $isCustomerNotified = $order->getCustomerNoteNotify();

        // Do order payment validation on payment method level
        $methodInstance->validate();
        $action = $methodInstance->getConfigPaymentAction();

        if ($action) {
            if ($methodInstance->isInitializeNeeded()) {
                $stateObject = new \Magento\Framework\Object();
                // For method initialization we have to use original config value for payment action
                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
                $orderState = $stateObject->getData('state') ?: $orderState;
                $orderStatus = $stateObject->getData('status') ?: $orderStatus;
                $isCustomerNotified = $stateObject->hasData('is_notified')
                    ? $stateObject->getData('is_notified')
                    : $isCustomerNotified;
            } else {
                $orderState = Order::STATE_PROCESSING;
                $this->processAction($action, $order);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            }
        } else {
            $order->setState($orderState)
                ->setStatus($orderStatus);
        }

        $isCustomerNotified = $isCustomerNotified ?: $order->getCustomerNoteNotify();

        if (!array_key_exists($orderStatus, $order->getConfig()->getStateStatuses($orderState))) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $this->updateOrder($order, $orderState, $orderStatus, $isCustomerNotified);

        $this->_eventManager->dispatch('sales_order_payment_place_end', ['payment' => $this]);

        return $this;
    }

    /**
     * Set appropriate state to order or add status to order history
     *
     * @param Order $order
     * @param string $orderState
     * @param string $orderStatus
     * @param bool $isCustomerNotified
     * @return void
     */
    protected function updateOrder(Order $order, $orderState, $orderStatus, $isCustomerNotified)
    {
        // add message if order was put into review during authorization or capture
        $message = $order->getCustomerNote();
        $originalOrderState = $order->getState();
        $originalOrderStatus = $order->getStatus();

        switch (true) {
            case ($message && ($originalOrderState == Order::STATE_PAYMENT_REVIEW)):
                $order->addStatusToHistory($originalOrderStatus, $message, $isCustomerNotified);
                break;
            case ($message):
            case ($originalOrderState && $message):
            case ($originalOrderState != $orderState):
            case ($originalOrderStatus != $orderStatus):
                $order->setState($orderState)
                    ->setStatus($orderStatus)
                    ->addStatusHistoryComment($message)
                    ->setIsCustomerNotified($isCustomerNotified);
                break;
            default:
                break;
        }
    }

    /**
     * Perform actions based on passed action name
     *
     * @param string $action
     * @param Order $order
     * @return void
     */
    protected function processAction($action, Order $order)
    {
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        switch ($action) {
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:
                $this->_order($baseTotalDue);
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE:
                $this->authorize(true, $baseTotalDue);
                // base amount will be set inside
                $this->setAmountAuthorized($totalDue);
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                $this->setAmountAuthorized($totalDue);
                $this->setBaseAmountAuthorized($baseTotalDue);
                $this->capture(null);
                break;
            default:
                break;
        }
    }

    /**
     * Capture the payment online
     * Requires an invoice. If there is no invoice specified, will automatically prepare an invoice for order
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with registerCaptureNotification()
     *
     * @param null|Invoice $invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function capture($invoice = null)
    {
        if (is_null($invoice)) {
            $invoice = $this->_invoice();
            $this->setCreatedInvoice($invoice);
            if ($this->getIsFraudDetected()) {
                $this->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            return $this;
        }
        $amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
        $order = $this->getOrder();

        // prepare parent transaction and its amount
        $paidWorkaround = 0;
        if (!$invoice->wasPayCalled()) {
            $paidWorkaround = (double)$amountToCapture;
        }
        $this->_isCaptureFinal($paidWorkaround);

        $this->_generateTransactionId(
            Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $this->_eventManager->dispatch(
            'sales_order_payment_capture',
            ['payment' => $this, 'invoice' => $invoice]
        );

        /**
         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
         */
        if ($invoice->getTransactionId()) {
            $method = $this->getMethodInstance();
            $method->setStore(
                $order->getStoreId()
            );
            $method->fetchTransactionInfo(
                $this,
                $invoice->getTransactionId()
            );
        }
        $status = false;
        if (!$invoice->getIsPaid()) {
            // attempt to capture: this can trigger "is_transaction_pending"
            $method = $this->getMethodInstance();
            $method->setStore(
                $order->getStoreId()
            );
            $method->capture($this, $amountToCapture);

            $transaction = $this->_addTransaction(
                Transaction::TYPE_CAPTURE,
                $invoice,
                true
            );

            if ($this->getIsTransactionPending()) {
                $message = __(
                    'An amount of %1 will be captured after being approved at the payment gateway.',
                    $this->_formatPrice($amountToCapture)
                );
                $state = Order::STATE_PAYMENT_REVIEW;
                if ($this->getIsFraudDetected()) {
                    $status = Order::STATUS_FRAUD;
                }
                $invoice->setIsPaid(false);
            } else {
                // normal online capture: invoice is marked as "paid"
                $message = __('Captured amount of %1 online', $this->_formatPrice($amountToCapture));
                $state = Order::STATE_PROCESSING;
                $invoice->setIsPaid(true);
                $this->_updateTotals(['base_amount_paid_online' => $amountToCapture]);
            }
            $message = $this->_prependMessage($message);
            $message = $this->_appendTransactionToMessage($transaction, $message);

            if (!$status) {
                $status = $order->getConfig()->getStateDefaultStatus($state);
            }

            $order->setState($state)
                ->setStatus($status)
                ->addStatusHistoryComment($message);

            $invoice->setTransactionId($this->getLastTransId());
            return $this;
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('The transaction "%1" cannot be captured yet.', $invoice->getTransactionId())
        );
    }

    /**
     * Process a capture notification from a payment gateway for specified amount
     * Creates an invoice automatically if the amount covers the order base grand total completely
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with capture()
     *
     * @param float $amount
     * @param bool $skipFraudDetection
     * @return $this
     */
    public function registerCaptureNotification($amount, $skipFraudDetection = false)
    {
        $this->_generateTransactionId(
            Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $order = $this->getOrder();
        $amount = (double)$amount;
        $invoice = $this->_getInvoiceForTransactionId($this->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($this->_isSameCurrency() && $this->_isCaptureFinal($amount)) {
                $invoice = $order->prepareInvoice()->register();
                $order->addRelatedObject($invoice);
                $this->setCreatedInvoice($invoice);
            } else {
                $this->setIsFraudDetected(!$skipFraudDetection);
                $this->_updateTotals(['base_amount_paid_online' => $amount]);
            }
        }

        if ($this->getIsTransactionPending()) {
            $message = __(
                'An amount of %1 will be captured after being approved at the payment gateway.',
                $this->_formatPrice($amount)
            );
            $state = Order::STATE_PAYMENT_REVIEW;
        } else {
            $message = __('Registered notification about captured amount of %1.', $this->_formatPrice($amount));
            $state = Order::STATE_PROCESSING;
            // register capture for an existing invoice
            if ($invoice && Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(['base_amount_paid_online' => $amount]);
                $order->addRelatedObject($invoice);
            }
        }
        if ($this->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = __(
                'Order is suspended as its capture amount %1 is suspected to be fraudulent.',
                $this->_formatPrice($amount)
            );
            $status = Order::STATUS_FRAUD;
        } else {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $transaction = $this->_addTransaction(
            Transaction::TYPE_CAPTURE,
            $invoice,
            true
        );
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);

        $order->setState($state)->setStatus($status)->addStatusHistoryComment($message);
        return $this;
    }

    /**
     * Process authorization notification
     *
     * @param float $amount
     * @return $this
     * @see self::_authorize()
     */
    public function registerAuthorizationNotification($amount)
    {
        return $this->_isTransactionExists() ? $this : $this->authorize(false, $amount);
    }

    /**
     * Register payment fact: update self totals from the invoice
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function pay($invoice)
    {
        $this->_updateTotals(
            [
                'amount_paid' => $invoice->getGrandTotal(),
                'base_amount_paid' => $invoice->getBaseGrandTotal(),
                'shipping_captured' => $invoice->getShippingAmount(),
                'base_shipping_captured' => $invoice->getBaseShippingAmount(),
            ]
        );
        $this->_eventManager->dispatch('sales_order_payment_pay', ['payment' => $this, 'invoice' => $invoice]);
        return $this;
    }

    /**
     * Cancel specified invoice: update self totals from it
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function cancelInvoice($invoice)
    {
        $this->_updateTotals(
            [
                'amount_paid' => -1 * $invoice->getGrandTotal(),
                'base_amount_paid' => -1 * $invoice->getBaseGrandTotal(),
                'shipping_captured' => -1 * $invoice->getShippingAmount(),
                'base_shipping_captured' => -1 * $invoice->getBaseShippingAmount(),
            ]
        );
        $this->_eventManager->dispatch(
            'sales_order_payment_cancel_invoice',
            ['payment' => $this, 'invoice' => $invoice]
        );
        return $this;
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @return Invoice
     */
    protected function _invoice()
    {
        $invoice = $this->getOrder()->prepareInvoice();

        $invoice->register();
        if ($this->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $this->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }

    /**
     * Check order payment void availability
     *
     * @return bool
     */
    public function canVoid()
    {
        if (null === $this->_canVoidLookup) {
            $this->_canVoidLookup = (bool)$this->getMethodInstance()->canVoid();
            if ($this->_canVoidLookup) {
                $authTransaction = $this->getAuthorizationTransaction();
                $this->_canVoidLookup = (bool)$authTransaction && !(int)$authTransaction->getIsClosed();
            }
        }
        return $this->_canVoidLookup;
    }

    /**
     * Void payment online
     *
     * @param \Magento\Framework\Object $document
     * @return $this
     * @see self::_void()
     */
    public function void(\Magento\Framework\Object $document)
    {
        $this->_void(true);
        $this->_eventManager->dispatch('sales_order_payment_void', ['payment' => $this, 'invoice' => $document]);
        return $this;
    }

    /**
     * Process void notification
     *
     * @param float $amount
     * @return $this
     * @see self::_void()
     */
    public function registerVoidNotification($amount = null)
    {
        if (!$this->hasMessage()) {
            $this->setMessage(__('Registered a Void notification.'));
        }
        return $this->_void(false, $amount);
    }

    /**
     * Sets creditmemo for current payment
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function setCreditmemo(Creditmemo $creditmemo)
    {
        $this->setData('creditmemo', $creditmemo);
        return $this;
    }

    /**
     * Returns Creditmemo assigned for this payment
     *
     * @return Creditmemo|null
     */
    public function getCreditmemo()
    {
        return $this->getData('creditmemo') instanceof Creditmemo
            ? $this->getData('creditmemo')
            : null;
    }

    /**
     * Refund payment online or offline, depending on whether there is invoice set in the creditmemo instance
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param Creditmemo $creditmemo
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function refund($creditmemo)
    {
        $baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());

        $this->_generateTransactionId(Transaction::TYPE_REFUND);

        // call refund from gateway if required
        $isOnline = false;
        $gateway = $this->getMethodInstance();
        $invoice = null;
        if ($gateway->canRefund() && $creditmemo->getDoTransaction()) {
            $this->setCreditmemo($creditmemo);
            $invoice = $creditmemo->getInvoice();
            if ($invoice) {
                $isOnline = true;
                $captureTxn = $this->_lookupTransaction($invoice->getTransactionId());
                if ($captureTxn) {
                    $this->setParentTransactionId($captureTxn->getTxnId());
                }
                $this->setShouldCloseParentTransaction(true);
                // TODO: implement multiple refunds per capture
                try {
                    $gateway->setStore(
                        $this->getOrder()->getStoreId()
                    );
                    $this->setRefundTransactionId($invoice->getTransactionId());
                    $gateway->refund(
                        $this,
                        $baseAmountToRefund
                    );

                    $creditmemo->setTransactionId($this->getLastTransId());
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if (!$captureTxn) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('If the invoice was created offline, try creating an offline credit memo.'),
                            $e
                        );
                    }
                    throw $e;
                }
            }
        }

        // update self totals from creditmemo
        $this->_updateTotals(
            [
                'amount_refunded' => $creditmemo->getGrandTotal(),
                'base_amount_refunded' => $baseAmountToRefund,
                'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
                'shipping_refunded' => $creditmemo->getShippingAmount(),
                'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
            ]
        );

        // update transactions and order state
        $transaction = $this->_addTransaction(
            Transaction::TYPE_REFUND,
            $creditmemo,
            $isOnline
        );
        if ($invoice) {
            $message = __('We refunded %1 online.', $this->_formatPrice($baseAmountToRefund));
        } else {
            $message = $this->hasMessage() ? $this->getMessage() : __(
                'We refunded %1 offline.',
                $this->_formatPrice($baseAmountToRefund)
            );
        }
        $message = $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $this->setOrderStateProcessing($message);
        $this->_eventManager->dispatch(
            'sales_order_payment_refund',
            ['payment' => $this, 'creditmemo' => $creditmemo]
        );
        return $this;
    }

    /**
     * Process payment refund notification
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     * TODO: potentially a full capture can be refunded. In this case if there was only one invoice for that transaction
     *       then we should create a creditmemo from invoice and also refund it offline
     * TODO: implement logic of chargebacks reimbursements (via negative amount)
     *
     * @param float $amount
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function registerRefundNotification($amount)
    {
        $notificationAmount = $amount;
        $this->_generateTransactionId(
            Transaction::TYPE_REFUND,
            $this->_lookupTransaction($this->getParentTransactionId())
        );
        if ($this->_isTransactionExists()) {
            return $this;
        }
        $order = $this->getOrder();
        $invoice = $this->_getInvoiceForTransactionId($this->getParentTransactionId());

        if ($invoice) {
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $amountRefundLeft = $baseGrandTotal - $invoice->getBaseTotalRefunded();
        } else {
            $baseGrandTotal = $order->getBaseGrandTotal();
            $amountRefundLeft = $baseGrandTotal - $order->getBaseTotalRefunded();
        }

        if ($amountRefundLeft < $amount) {
            $amount = $amountRefundLeft;
        }

        if ($amount != $baseGrandTotal) {
            $order->addStatusHistoryComment(
                __(
                    'IPN "Refunded". Refund issued by merchant. Registered notification about refunded amount of %1. Transaction ID: "%2". Credit Memo has not been created. Please create offline Credit Memo.',
                    $this->_formatPrice($notificationAmount),
                    $this->getTransactionId()
                ),
                false
            );
            return $this;
        }

        $serviceModel = $this->_serviceOrderFactory->create(['order' => $order]);
        if ($invoice) {
            if ($invoice->getBaseTotalRefunded() > 0) {
                $adjustment = ['adjustment_positive' => $amount];
            } else {
                $adjustment = ['adjustment_negative' => $baseGrandTotal - $amount];
            }
            $creditmemo = $serviceModel->prepareInvoiceCreditmemo($invoice, $adjustment);
            if ($creditmemo) {
                $totalRefunded = $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal();
                $this->setShouldCloseParentTransaction($invoice->getBaseGrandTotal() <= $totalRefunded);
            }
        } else {
            if ($order->getBaseTotalRefunded() > 0) {
                $adjustment = ['adjustment_positive' => $amount];
            } else {
                $adjustment = ['adjustment_negative' => $baseGrandTotal - $amount];
            }
            $creditmemo = $serviceModel->prepareCreditmemo($adjustment);
            if ($creditmemo) {
                $totalRefunded = $order->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal();
                $this->setShouldCloseParentTransaction($order->getBaseGrandTotal() <= $totalRefunded);
            }
        }

        $creditmemo->setPaymentRefundDisallowed(
            true
        )->setAutomaticallyCreated(
            true
        )->register()->addComment(
            __('The credit memo has been created automatically.')
        );
        $creditmemo->save();

        $this->_updateTotals(
            ['amount_refunded' => $creditmemo->getGrandTotal(), 'base_amount_refunded_online' => $amount]
        );

        $this->setCreatedCreditmemo($creditmemo);
        // update transactions and order state
        $transaction = $this->_addTransaction(
            Transaction::TYPE_REFUND,
            $creditmemo
        );
        $message = $this->_prependMessage(
            __('Registered notification about refunded amount of %1.', $this->_formatPrice($amount))
        );
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $this->setOrderStateProcessing($message);
        return $this;
    }

    /**
     * Cancel a creditmemo: substract its totals from the payment
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function cancelCreditmemo($creditmemo)
    {
        $this->_updateTotals(
            [
                'amount_refunded' => -1 * $creditmemo->getGrandTotal(),
                'base_amount_refunded' => -1 * $creditmemo->getBaseGrandTotal(),
                'shipping_refunded' => -1 * $creditmemo->getShippingAmount(),
                'base_shipping_refunded' => -1 * $creditmemo->getBaseShippingAmount(),
            ]
        );
        $this->_eventManager->dispatch(
            'sales_order_payment_cancel_creditmemo',
            ['payment' => $this, 'creditmemo' => $creditmemo]
        );
        return $this;
    }

    /**
     * Order cancellation hook for payment method instance
     * Adds void transaction if needed
     *
     * @return $this
     */
    public function cancel()
    {
        $isOnline = true;
        if (!$this->canVoid()) {
            $isOnline = false;
        }

        if (!$this->hasMessage()) {
            $this->setMessage($isOnline ? __('Canceled order online') : __('Canceled order offline'));
        }

        if ($isOnline) {
            $this->_void($isOnline, null, 'cancel');
        }

        $this->_eventManager->dispatch('sales_order_payment_cancel', ['payment' => $this]);

        return $this;
    }

    /**
     * Check order payment review availability
     *
     * @return bool
     */
    public function canReviewPayment()
    {
        return (bool)$this->getMethodInstance()->canReviewPayment();
    }

    /**
     * @return bool
     */
    public function canFetchTransactionInfo()
    {
        return (bool)$this->getMethodInstance()->canFetchTransactionInfo();
    }

    /**
     * Accept online a payment that is in review state
     *
     * @return $this
     */
    public function accept()
    {
        $transactionId = $this->getLastTransId();

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $this->getMethodInstance();
        $method->setStore($this->getOrder()->getStoreId());
        if ($method->acceptPayment($this)) {
            $invoice = $this->_getInvoiceForTransactionId($transactionId);
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('Approved the payment online.'))
            );
            $this->updateBaseAmountPaidOnlineTotal($invoice);
            $this->setOrderStateProcessing($message);
        } else {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('There is no need to approve this payment.'))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }
        return $this;
    }

    /**
     * Accept order with payment method instance
     *
     * @param bool $isOnline
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deny($isOnline = true)
    {
        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();

        if ($isOnline) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethodInstance();
            $method->setStore($this->getOrder()->getStoreId());

            $result = $method->denyPayment($this);
        } else {
            $result = (bool)$this->getNotificationResult();
        }

        if ($result) {
            $invoice = $this->_getInvoiceForTransactionId($transactionId);
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('Denied the payment online'))
            );
            $this->cancelInvoiceAndRegisterCancellation($invoice, $message);
        } else {
            $txt = $isOnline ?
                'There is no need to deny this payment.' : 'Registered notification about denied payment.';
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__($txt))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }
        return $this;
    }

    /**
     * Performs registered payment update.
     *
     * @param bool $isOnline
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update($isOnline = true)
    {
        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();
        $invoice = $this->_getInvoiceForTransactionId($transactionId);

        
        if ($isOnline) {
            $method = $this->getMethodInstance();
            $method->setStore($this->getOrder()->getStoreId());
            $method->fetchTransactionInfo($this, $transactionId);
        }

        if ($this->getIsTransactionApproved()) {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('Registered update about approved payment.'))
            );
            $this->updateBaseAmountPaidOnlineTotal($invoice);
            $this->setOrderStateProcessing($message);
        } elseif ($this->getIsTransactionDenied()) {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('Registered update about denied payment.'))
            );
            $this->cancelInvoiceAndRegisterCancellation($invoice, $message);
        } else {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->_prependMessage(__('There is no update for the payment.'))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }

        return $this;
    }

    /**
     * Triggers invoice pay and updates base_amount_paid_online total.
     *
     * @param \Magento\Sales\Model\Order\Invoice|false $invoice
     */
    protected function updateBaseAmountPaidOnlineTotal($invoice)
    {
        if ($invoice instanceof Invoice) {
            $invoice->pay();
            $this->_updateTotals(['base_amount_paid_online' => $invoice->getBaseGrandTotal()]);
            $this->getOrder()->addRelatedObject($invoice);
        }
    }

    /**
     * Sets order state to 'processing' with appropriate message
     *
     * @param \Magento\Framework\Phrase|string $message
     */
    protected function setOrderStateProcessing($message)
    {
        $this->getOrder()->setState(Order::STATE_PROCESSING)
            ->setStatus($this->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
            ->addStatusHistoryComment($message);
    }

    /**
     * Cancel invoice and register order cancellation
     *
     * @param Invoice|false $invoice
     * @param string $message
     */
    protected function cancelInvoiceAndRegisterCancellation($invoice, $message)
    {
        if ($invoice instanceof Invoice) {
            $invoice->cancel();
            $this->getOrder()->addRelatedObject($invoice);
        }
        $this->getOrder()->registerCancellation($message, false);
    }

    /**
     * Sets order state status to 'payment_review' with appropriate message
     *
     * @param string $message
     * @param int|null $transactionId
     */
    protected function setOrderStatePaymentReview($message, $transactionId)
    {
        if ($this->getOrder()->getState() != Order::STATE_PAYMENT_REVIEW) {
            $this->getOrder()->setState(Order::STATE_PAYMENT_REVIEW)
                ->addStatusHistoryComment($message);
            if ($this->getIsFraudDetected()) {
                $this->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            if ($transactionId) {
                $this->setLastTransId($transactionId);
            }
        } else {
            $this->getOrder()->addStatusHistoryComment($message);
        }
    }

    /**
     * Order payment either online
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param float $amount
     * @return $this
     */
    protected function _order($amount)
    {
        // update totals
        $amount = $this->_formatAmount($amount, true);

        // do ordering
        $order = $this->getOrder();

        $state = Order::STATE_PROCESSING;
        $status = false;
        $method = $this->getMethodInstance();
        $method->setStore($order->getStoreId());
        $method->order($this, $amount);

        if ($this->getSkipOrderProcessing()) {
            return $this;
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $message = __(
                'The order amount of %1 is pending approval on the payment gateway.',
                $this->_formatPrice($amount)
            );
            $state = Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $status = Order::STATUS_FRAUD;
            }
        } else {
            $message = __('Ordered amount of %1', $this->_formatPrice($amount));
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Transaction::TYPE_ORDER);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);

        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state)->setStatus($status)->addStatusHistoryComment($message);
        return $this;
    }

    /**
     * Authorize payment either online or offline (process auth notification)
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     *
     * @return $this
     */
    public function authorize($isOnline, $amount)
    {
        // check for authorization amount to be equal to grand total
        $this->setShouldCloseParentTransaction(false);
        $isSameCurrency = $this->_isSameCurrency();
        if (!$isSameCurrency || !$this->_isCaptureFinal($amount)) {
            $this->setIsFraudDetected(true);
        }

        // update totals
        $amount = $this->_formatAmount($amount, true);
        $this->setBaseAmountAuthorized($amount);

        // do authorization
        $order = $this->getOrder();
        $state = Order::STATE_PROCESSING;
        $status = false;
        if ($isOnline) {
            // invoke authorization on gateway
            $method = $this->getMethodInstance();
            $method->setStore($order->getStoreId());
            $method->authorize($this, $amount);
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = __(
                'We will authorize %1 after the payment is approved at the payment gateway.',
                $this->_formatPrice($amount)
            );
        } else {
            if ($this->getIsFraudDetected()) {
                $state = Order::STATE_PROCESSING;
                $message = __(
                    'Order is suspended as its authorizing amount %1 is suspected to be fraudulent.',
                    $this->_formatPrice($amount, $this->getCurrencyCode())
                );
            } else {
                $message = __('Authorized amount of %1', $this->_formatPrice($amount));
            }
        }
        if ($this->getIsFraudDetected()) {
            $status = Order::STATUS_FRAUD;
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Transaction::TYPE_AUTH);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);

        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state)->setStatus($status)->addStatusHistoryComment($message);

        return $this;
    }

    /**
     * Void payment either online or offline (process void notification)
     * NOTE: that in some cases authorization can be voided after a capture. In such case it makes sense to use
     *       the amount void amount, for informational purposes.
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     * @param string $gatewayCallback
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _void($isOnline, $amount = null, $gatewayCallback = 'void')
    {
        $order = $this->getOrder();
        $authTransaction = $this->getAuthorizationTransaction();
        $this->_generateTransactionId(Transaction::TYPE_VOID, $authTransaction);
        $this->setShouldCloseParentTransaction(true);

        // attempt to void
        if ($isOnline) {
            $method = $this->getMethodInstance();
            $method->setStore($order->getStoreId());
            $method->{$gatewayCallback}($this);
        }
        if ($this->_isTransactionExists()) {
            return $this;
        }

        // if the authorization was untouched, we may assume voided amount = order grand total
        // but only if the payment auth amount equals to order grand total
        if ($authTransaction &&
            $order->getBaseGrandTotal() == $this->getBaseAmountAuthorized() &&
            0 == $this->getBaseAmountCanceled()
        ) {
            if ($authTransaction->canVoidAuthorizationCompletely()) {
                $amount = (double)$order->getBaseGrandTotal();
            }
        }

        if ($amount) {
            $amount = $this->_formatAmount($amount);
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Transaction::TYPE_VOID, null, true);
        $message = $this->hasMessage() ? $this->getMessage() : __('Voided authorization.');
        $message = $this->_prependMessage($message);
        if ($amount) {
            $message .= ' ' . __('Amount: %1.', $this->_formatPrice($amount));
        }
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $this->setOrderStateProcessing($message);
        $order->setDataChanges(true);
        return $this;
    }

    //    /**
    //     * TODO: implement this
    //     * @param Invoice $invoice
    //     * @return $this
    //     */
    //    public function cancelCapture($invoice = null)
    //    {
    //    }

    /**
     * Create transaction,
     * prepare its insertion into hierarchy and add its information to payment and comments
     *
     * To add transactions and related information,
     * the following information should be set to payment before processing:
     * - transaction_id
     * - is_transaction_closed (optional) - whether transaction should be closed or open (closed by default)
     * - parent_transaction_id (optional)
     * - should_close_parent_transaction (optional) - whether to close parent transaction (closed by default)
     *
     * If the sales document is specified, it will be linked to the transaction as related for future usage.
     * Currently transaction ID is set into the sales object
     * This method writes the added transaction ID into last_trans_id field of the payment object
     *
     * To make sure transaction object won't cause trouble before saving, use $failsafe = true
     *
     * @param string $type
     * @param \Magento\Sales\Model\AbstractModel $salesDocument
     * @param bool $failsafe
     * @return null|Transaction
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _addTransaction($type, $salesDocument = null, $failsafe = false)
    {
        if ($this->getSkipTransactionCreation()) {
            $this->unsTransactionId();
            return null;
        }

        // look for set transaction ids
        $transactionId = $this->getTransactionId();
        if (null !== $transactionId) {
            // set transaction parameters
            $transaction = false;
            if ($this->getOrder()->getId()) {
                $transaction = $this->_lookupTransaction($transactionId);
            }
            if (!$transaction) {
                $transaction = $this->_transactionFactory->create()->setTxnId($transactionId);
            }
            $transaction->setOrderPaymentObject($this)->setTxnType($type)->isFailsafe($failsafe);

            if ($this->hasIsTransactionClosed()) {
                $transaction->setIsClosed((int)$this->getIsTransactionClosed());
            }

            //set transaction addition information
            if ($this->_transactionAdditionalInfo) {
                foreach ($this->_transactionAdditionalInfo as $key => $value) {
                    $transaction->setAdditionalInformation($key, $value);
                }
                $this->_transactionAdditionalInfo = [];
            }

            // link with sales entities
            $this->setLastTransId($transactionId);
            $this->setCreatedTransaction($transaction);
            $this->getOrder()->addRelatedObject($transaction);
            if ($salesDocument && $salesDocument instanceof \Magento\Sales\Model\AbstractModel) {
                $salesDocument->setTransactionId($transactionId);
            }

            // link with parent transaction
            $parentTransactionId = $this->getParentTransactionId();

            if ($parentTransactionId) {
                $transaction->setParentTxnId($parentTransactionId);
                if ($this->getShouldCloseParentTransaction()) {
                    $parentTransaction = $this->_lookupTransaction($parentTransactionId);
                    if ($parentTransaction) {
                        if (!$parentTransaction->getIsClosed()) {
                            $parentTransaction->isFailsafe($failsafe)->close(false);
                        }
                        $this->getOrder()->addRelatedObject($parentTransaction);
                    }
                }
            }
            return $transaction;
        }

        return null;
    }

    /**
     * Public access to _addTransaction method
     *
     * @param string $type
     * @param \Magento\Sales\Model\AbstractModel $salesDocument
     * @param bool $failsafe
     * @param bool|string $message
     * @return null|Transaction
     */
    public function addTransaction($type, $salesDocument = null, $failsafe = false, $message = false)
    {
        $transaction = $this->_addTransaction($type, $salesDocument, $failsafe);

        if ($message) {
            $order = $this->getOrder();
            $message = $this->_appendTransactionToMessage($transaction, $message);
            $order->addStatusHistoryComment($message);
        }

        return $transaction;
    }

    /**
     * Import details data of specified transaction
     *
     * @param Transaction $transactionTo
     * @return $this
     */
    public function importTransactionInfo(Transaction $transactionTo)
    {
        $method = $this->getMethodInstance();
        $method->setStore(
            $this->getOrder()->getStoreId()
        );
        $method->fetchTransactionInfo(
            $this,
            $transactionTo->getTxnId()
        );
        if ($method) {
            $transactionTo->setAdditionalInformation(
                Transaction::RAW_DETAILS,
                $method
            );
        }
        return $this;
    }

    /**
     * Totals updater utility method
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param array $data
     * @return void
     */
    protected function _updateTotals($data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $this->getDataUsingMethod($key);
                $this->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Check transaction existence by specified transaction id
     *
     * @param string $txnId
     * @return boolean
     */
    protected function _isTransactionExists($txnId = null)
    {
        if (null === $txnId) {
            $txnId = $this->getTransactionId();
        }
        return $txnId && $this->_lookupTransaction($txnId);
    }

    /**
     * Append transaction ID (if any) message to the specified message
     *
     * @param Transaction|null $transaction
     * @param string $message
     * @return string
     */
    protected function _appendTransactionToMessage($transaction, $message)
    {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getHtmlTxnId() : $transaction;
            $message .= ' ' . __('Transaction ID: "%1"', $txnId);
        }
        return $message;
    }

    /**
     * Prepend a "prepared_message" that may be set to the payment instance before, to the specified message
     * Prepends value to the specified string or to the comment of specified order status history item instance
     *
     * @param string|\Magento\Sales\Model\Order\Status\History $messagePrependTo
     * @return string|\Magento\Sales\Model\Order\Status\History
     */
    protected function _prependMessage($messagePrependTo)
    {
        $preparedMessage = $this->getPreparedMessage();
        if ($preparedMessage) {
            if (
                is_string($preparedMessage)
                || $preparedMessage instanceof \Magento\Framework\Phrase
            ) {
                return $preparedMessage . ' ' . $messagePrependTo;
            } elseif (is_object(
                    $preparedMessage
                ) && $preparedMessage instanceof \Magento\Sales\Model\Order\Status\History
            ) {
                $comment = $preparedMessage->getComment() . ' ' . $messagePrependTo;
                $preparedMessage->setComment($comment);
                return $comment;
            }
        }
        return $messagePrependTo;
    }

    /**
     * Round up and cast specified amount to float or string
     *
     * @param string|float $amount
     * @param bool $asFloat
     * @return string|float
     */
    protected function _formatAmount($amount, $asFloat = false)
    {
        $amount = $this->priceCurrency->round($amount);
        return !$asFloat ? (string)$amount : $amount;
    }

    /**
     * Format price with currency sign
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($amount)
    {
        return $this->getOrder()->getBaseCurrency()->formatTxt($amount);
    }

    /**
     * Find one transaction by ID or type
     *
     * @param string $txnId
     * @param bool|string $txnType
     * @return Transaction|false
     */
    protected function _lookupTransaction($txnId, $txnType = false)
    {
        if (!$txnId) {
            if ($txnType && $this->getId()) {
                $collection = $this->_transactionCollectionFactory->create()->setOrderFilter(
                    $this->getOrder()
                )->addPaymentIdFilter(
                    $this->getId()
                )->addTxnTypeFilter(
                    $txnType
                )->setOrder(
                    'created_at',
                    \Magento\Framework\Data\Collection::SORT_ORDER_DESC
                )->setOrder(
                    'transaction_id',
                    \Magento\Framework\Data\Collection::SORT_ORDER_DESC
                );
                foreach ($collection as $txn) {
                    $txn->setOrderPaymentObject($this);
                    $this->_transactionsLookup[$txn->getTxnId()] = $txn;
                    return $txn;
                }
            }
            return false;
        }
        if (isset($this->_transactionsLookup[$txnId])) {
            return $this->_transactionsLookup[$txnId];
        }
        $txn = $this->_transactionFactory->create()->setOrderPaymentObject($this)->loadByTxnId($txnId);
        if ($txn->getId()) {
            $this->_transactionsLookup[$txnId] = $txn;
        } else {
            $this->_transactionsLookup[$txnId] = false;
        }
        return $this->_transactionsLookup[$txnId];
    }

    /**
     * Find one transaction by ID or type
     *
     * @param string $txnId
     * @param bool|string $txnType
     * @return Transaction|false
     */
    public function lookupTransaction($txnId, $txnType = false)
    {
        return $this->_lookupTransaction($txnId, $txnType);
    }

    /**
     * Lookup an authorization transaction using parent transaction id, if set
     * @return Transaction|false
     */
    public function getAuthorizationTransaction()
    {
        if ($this->getParentTransactionId()) {
            $txn = $this->_lookupTransaction($this->getParentTransactionId());
        } else {
            $txn = false;
        }

        if (!$txn) {
            $txn = $this->_lookupTransaction(false, Transaction::TYPE_AUTH);
        }
        return $txn;
    }

    /**
     * Lookup the transaction by id
     * @param string $transactionId
     * @return Transaction|false
     */
    public function getTransaction($transactionId)
    {
        return $this->_lookupTransaction($transactionId);
    }

    /**
     * Update transaction ids for further processing
     * If no transactions were set before invoking, may generate an "offline" transaction id
     *
     * @param string $type
     * @param bool|Transaction $transactionBasedOn
     * @return void
     */
    protected function _generateTransactionId($type, $transactionBasedOn = false)
    {
        if (!$this->getParentTransactionId() && !$this->getTransactionId() && $transactionBasedOn) {
            $this->setParentTransactionId($transactionBasedOn->getTxnId());
        }
        // generate transaction id for an offline action or payment method that didn't set it
        if (($parentTxnId = $this->getParentTransactionId()) && !$this->getTransactionId()) {
            $this->setTransactionId("{$parentTxnId}-{$type}");
        }
    }

    /**
     * Decide whether authorization transaction may close (if the amount to capture will cover entire order)
     *
     * @param float $amountToCapture
     * @return bool
     */
    protected function _isCaptureFinal($amountToCapture)
    {
        $amountPaid = $this->_formatAmount($this->getBaseAmountPaid(), true);
        $amountToCapture = $this->_formatAmount($amountToCapture, true);
        $orderGrandTotal = $this->_formatAmount($this->getOrder()->getBaseGrandTotal(), true);
        if ($orderGrandTotal == $amountPaid + $amountToCapture) {
            if (false !== $this->getShouldCloseParentTransaction()) {
                $this->setShouldCloseParentTransaction(true);
            }
            return true;
        }
        return false;
    }

    /**
     * Check whether payment currency corresponds to order currency
     *
     * @return bool
     */
    protected function _isSameCurrency()
    {
        return !$this->getCurrencyCode() || $this->getCurrencyCode() == $this->getOrder()->getBaseCurrencyCode();
    }

    /**
     * Additional transaction info setter
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setTransactionAdditionalInfo($key, $value)
    {
        if (is_array($key)) {
            $this->_transactionAdditionalInfo = $key;
        } else {
            $this->_transactionAdditionalInfo[$key] = $value;
        }
    }

    /**
     * Additional transaction info getter
     *
     * @param string $key
     * @return mixed
     */
    public function getTransactionAdditionalInfo($key = null)
    {
        if (is_null($key)) {
            return $this->_transactionAdditionalInfo;
        }
        return isset($this->_transactionAdditionalInfo[$key]) ? $this->_transactionAdditionalInfo[$key] : null;
    }

    /**
     * Reset transaction additional info property
     *
     * @return $this
     */
    public function resetTransactionAdditionalInfo()
    {
        $this->_transactionAdditionalInfo = [];
        return $this;
    }

    /**
     * Return invoice model for transaction
     *
     * @param string $transactionId
     * @return Invoice|false
     */
    protected function _getInvoiceForTransactionId($transactionId)
    {
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId());
                // to make sure all data will properly load (maybe not required)
                return $invoice;
            }
        }
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_OPEN && $invoice->load(
                    $invoice->getId()
                )
            ) {
                $invoice->setTransactionId($transactionId);
                return $invoice;
            }
        }
        return false;
    }

    //@codeCoverageIgnoreStart
    /**
     * Returns account_status
     *
     * @return string
     */
    public function getAccountStatus()
    {
        return $this->getData(OrderPaymentInterface::ACCOUNT_STATUS);
    }

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(OrderPaymentInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns address_status
     *
     * @return string
     */
    public function getAddressStatus()
    {
        return $this->getData(OrderPaymentInterface::ADDRESS_STATUS);
    }

    /**
     * Returns amount_authorized
     *
     * @return float
     */
    public function getAmountAuthorized()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_AUTHORIZED);
    }

    /**
     * Returns amount_canceled
     *
     * @return float
     */
    public function getAmountCanceled()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_CANCELED);
    }

    /**
     * Returns amount_ordered
     *
     * @return float
     */
    public function getAmountOrdered()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_ORDERED);
    }

    /**
     * Returns amount_paid
     *
     * @return float
     */
    public function getAmountPaid()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_PAID);
    }

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_REFUNDED);
    }

    /**
     * Returns anet_trans_method
     *
     * @return string
     */
    public function getAnetTransMethod()
    {
        return $this->getData(OrderPaymentInterface::ANET_TRANS_METHOD);
    }

    /**
     * Returns base_amount_authorized
     *
     * @return float
     */
    public function getBaseAmountAuthorized()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_AUTHORIZED);
    }

    /**
     * Returns base_amount_canceled
     *
     * @return float
     */
    public function getBaseAmountCanceled()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_CANCELED);
    }

    /**
     * Returns base_amount_ordered
     *
     * @return float
     */
    public function getBaseAmountOrdered()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_ORDERED);
    }

    /**
     * Returns base_amount_paid
     *
     * @return float
     */
    public function getBaseAmountPaid()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_PAID);
    }

    /**
     * Returns base_amount_paid_online
     *
     * @return float
     */
    public function getBaseAmountPaidOnline()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_PAID_ONLINE);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_amount_refunded_online
     *
     * @return float
     */
    public function getBaseAmountRefundedOnline()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED_ONLINE);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_captured
     *
     * @return float
     */
    public function getBaseShippingCaptured()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_CAPTURED);
    }

    /**
     * Returns base_shipping_refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_REFUNDED);
    }

    /**
     * Returns cc_approval
     *
     * @return string
     */
    public function getCcApproval()
    {
        return $this->getData(OrderPaymentInterface::CC_APPROVAL);
    }

    /**
     * Returns cc_avs_status
     *
     * @return string
     */
    public function getCcAvsStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_AVS_STATUS);
    }

    /**
     * Returns cc_cid_status
     *
     * @return string
     */
    public function getCcCidStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_CID_STATUS);
    }

    /**
     * Returns cc_debug_request_body
     *
     * @return string
     */
    public function getCcDebugRequestBody()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_REQUEST_BODY);
    }

    /**
     * Returns cc_debug_response_body
     *
     * @return string
     */
    public function getCcDebugResponseBody()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_RESPONSE_BODY);
    }

    /**
     * Returns cc_debug_response_serialized
     *
     * @return string
     */
    public function getCcDebugResponseSerialized()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_RESPONSE_SERIALIZED);
    }

    /**
     * Returns cc_exp_month
     *
     * @return string
     */
    public function getCcExpMonth()
    {
        return $this->getData(OrderPaymentInterface::CC_EXP_MONTH);
    }

    /**
     * Returns cc_exp_year
     *
     * @return string
     */
    public function getCcExpYear()
    {
        return $this->getData(OrderPaymentInterface::CC_EXP_YEAR);
    }

    /**
     * Returns cc_last_4
     *
     * @return string
     */
    public function getCcLast4()
    {
        return $this->getData(OrderPaymentInterface::CC_LAST_4);
    }

    /**
     * Returns cc_number_enc
     *
     * @return string
     */
    public function getCcNumberEnc()
    {
        return $this->getData(OrderPaymentInterface::CC_NUMBER_ENC);
    }

    /**
     * Returns cc_owner
     *
     * @return string
     */
    public function getCcOwner()
    {
        return $this->getData(OrderPaymentInterface::CC_OWNER);
    }

    /**
     * Returns cc_secure_verify
     *
     * @return string
     */
    public function getCcSecureVerify()
    {
        return $this->getData(OrderPaymentInterface::CC_SECURE_VERIFY);
    }

    /**
     * Returns cc_ss_issue
     *
     * @return string
     */
    public function getCcSsIssue()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_ISSUE);
    }

    /**
     * Returns cc_ss_start_month
     *
     * @return string
     */
    public function getCcSsStartMonth()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_START_MONTH);
    }

    /**
     * Returns cc_ss_start_year
     *
     * @return string
     */
    public function getCcSsStartYear()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_START_YEAR);
    }

    /**
     * Returns cc_status
     *
     * @return string
     */
    public function getCcStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_STATUS);
    }

    /**
     * Returns cc_status_description
     *
     * @return string
     */
    public function getCcStatusDescription()
    {
        return $this->getData(OrderPaymentInterface::CC_STATUS_DESCRIPTION);
    }

    /**
     * Returns cc_trans_id
     *
     * @return string
     */
    public function getCcTransId()
    {
        return $this->getData(OrderPaymentInterface::CC_TRANS_ID);
    }

    /**
     * Returns cc_type
     *
     * @return string
     */
    public function getCcType()
    {
        return $this->getData(OrderPaymentInterface::CC_TYPE);
    }

    /**
     * Returns echeck_account_name
     *
     * @return string
     */
    public function getEcheckAccountName()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ACCOUNT_NAME);
    }

    /**
     * Returns echeck_account_type
     *
     * @return string
     */
    public function getEcheckAccountType()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ACCOUNT_TYPE);
    }

    /**
     * Returns echeck_bank_name
     *
     * @return string
     */
    public function getEcheckBankName()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_BANK_NAME);
    }

    /**
     * Returns echeck_routing_number
     *
     * @return string
     */
    public function getEcheckRoutingNumber()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ROUTING_NUMBER);
    }

    /**
     * Returns echeck_type
     *
     * @return string
     */
    public function getEcheckType()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_TYPE);
    }

    /**
     * Returns last_trans_id
     *
     * @return string
     */
    public function getLastTransId()
    {
        return $this->getData(OrderPaymentInterface::LAST_TRANS_ID);
    }

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(OrderPaymentInterface::METHOD);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(OrderPaymentInterface::PARENT_ID);
    }

    /**
     * Returns po_number
     *
     * @return string
     */
    public function getPoNumber()
    {
        return $this->getData(OrderPaymentInterface::PO_NUMBER);
    }

    /**
     * Returns protection_eligibility
     *
     * @return string
     */
    public function getProtectionEligibility()
    {
        return $this->getData(OrderPaymentInterface::PROTECTION_ELIGIBILITY);
    }

    /**
     * Returns quote_payment_id
     *
     * @return int
     */
    public function getQuotePaymentId()
    {
        return $this->getData(OrderPaymentInterface::QUOTE_PAYMENT_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_captured
     *
     * @return float
     */
    public function getShippingCaptured()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_CAPTURED);
    }

    /**
     * Returns shipping_refunded
     *
     * @return float
     */
    public function getShippingRefunded()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(OrderPaymentInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingCaptured($baseShippingCaptured)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_CAPTURED, $baseShippingCaptured);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCaptured($shippingCaptured)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_CAPTURED, $shippingCaptured);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountRefunded($amountRefunded)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_REFUNDED, $amountRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountPaid($baseAmountPaid)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_PAID, $baseAmountPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountCanceled($amountCanceled)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_CANCELED, $amountCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountAuthorized($baseAmountAuthorized)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_AUTHORIZED, $baseAmountAuthorized);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountPaidOnline($baseAmountPaidOnline)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_PAID_ONLINE, $baseAmountPaidOnline);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountRefundedOnline($baseAmountRefundedOnline)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED_ONLINE, $baseAmountRefundedOnline);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountPaid($amountPaid)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_PAID, $amountPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountAuthorized($amountAuthorized)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_AUTHORIZED, $amountAuthorized);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountOrdered($baseAmountOrdered)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_ORDERED, $baseAmountOrdered);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingRefunded($baseShippingRefunded)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_REFUNDED, $baseShippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingRefunded($shippingRefunded)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_REFUNDED, $shippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountRefunded($baseAmountRefunded)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED, $baseAmountRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountOrdered($amountOrdered)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_ORDERED, $amountOrdered);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountCanceled($baseAmountCanceled)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_CANCELED, $baseAmountCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuotePaymentId($id)
    {
        return $this->setData(OrderPaymentInterface::QUOTE_PAYMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(OrderPaymentInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcExpMonth($ccExpMonth)
    {
        return $this->setData(OrderPaymentInterface::CC_EXP_MONTH, $ccExpMonth);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcSsStartYear($ccSsStartYear)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_START_YEAR, $ccSsStartYear);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckBankName($echeckBankName)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_BANK_NAME, $echeckBankName);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        return $this->setData(OrderPaymentInterface::METHOD, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugRequestBody($ccDebugRequestBody)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_REQUEST_BODY, $ccDebugRequestBody);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcSecureVerify($ccSecureVerify)
    {
        return $this->setData(OrderPaymentInterface::CC_SECURE_VERIFY, $ccSecureVerify);
    }

    /**
     * {@inheritdoc}
     */
    public function setProtectionEligibility($protectionEligibility)
    {
        return $this->setData(OrderPaymentInterface::PROTECTION_ELIGIBILITY, $protectionEligibility);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcApproval($ccApproval)
    {
        return $this->setData(OrderPaymentInterface::CC_APPROVAL, $ccApproval);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcLast4($ccLast4)
    {
        return $this->setData(OrderPaymentInterface::CC_LAST_4, $ccLast4);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcStatusDescription($description)
    {
        return $this->setData(OrderPaymentInterface::CC_STATUS_DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckType($echeckType)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_TYPE, $echeckType);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugResponseSerialized($ccDebugResponseSerialized)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_RESPONSE_SERIALIZED, $ccDebugResponseSerialized);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcSsStartMonth($ccSsStartMonth)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_START_MONTH, $ccSsStartMonth);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckAccountType($echeckAccountType)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ACCOUNT_TYPE, $echeckAccountType);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastTransId($id)
    {
        return $this->setData(OrderPaymentInterface::LAST_TRANS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcCidStatus($ccCidStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_CID_STATUS, $ccCidStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcOwner($ccOwner)
    {
        return $this->setData(OrderPaymentInterface::CC_OWNER, $ccOwner);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcType($ccType)
    {
        return $this->setData(OrderPaymentInterface::CC_TYPE, $ccType);
    }

    /**
     * {@inheritdoc}
     */
    public function setPoNumber($poNumber)
    {
        return $this->setData(OrderPaymentInterface::PO_NUMBER, $poNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcExpYear($ccExpYear)
    {
        return $this->setData(OrderPaymentInterface::CC_EXP_YEAR, $ccExpYear);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcStatus($ccStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_STATUS, $ccStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckRoutingNumber($echeckRoutingNumber)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ROUTING_NUMBER, $echeckRoutingNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setAccountStatus($accountStatus)
    {
        return $this->setData(OrderPaymentInterface::ACCOUNT_STATUS, $accountStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setAnetTransMethod($anetTransMethod)
    {
        return $this->setData(OrderPaymentInterface::ANET_TRANS_METHOD, $anetTransMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugResponseBody($ccDebugResponseBody)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_RESPONSE_BODY, $ccDebugResponseBody);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcSsIssue($ccSsIssue)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_ISSUE, $ccSsIssue);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckAccountName($echeckAccountName)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ACCOUNT_NAME, $echeckAccountName);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcAvsStatus($ccAvsStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_AVS_STATUS, $ccAvsStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcNumberEnc($ccNumberEnc)
    {
        return $this->setData(OrderPaymentInterface::CC_NUMBER_ENC, $ccNumberEnc);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcTransId($id)
    {
        return $this->setData(OrderPaymentInterface::CC_TRANS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressStatus($addressStatus)
    {
        return $this->setData(OrderPaymentInterface::ADDRESS_STATUS, $addressStatus);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderPaymentExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Sets whether transaction is pending
     *
     * @param bool|int $flag
     * @return $this
     */
    public function setIsTransactionPending($flag)
    {
        $this->setData('is_transaction_pending', (bool)$flag);
        return $this;
    }

    /**
     * Whether transaction is pending
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsTransactionPending()
    {
        return (bool)$this->getData('is_transaction_pending');
    }

    /**
     * Sets whether fraud was detected
     *
     * @param bool|int $flag
     * @return $this
     */
    public function setIsFraudDetected($flag)
    {
        $this->setData('is_fraud_detected', (bool)$flag);
        return $this;
    }

    /**
     * Whether fraud was detected
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsFraudDetected()
    {
        return (bool)$this->getData('is_fraud_detected');
    }

    /**
     * Sets whether should close parent transaction
     *
     * @param int|bool $flag
     * @return $this
     */
    public function setShouldCloseParentTransaction($flag)
    {
        $this->setData('should_close_parent_transaction', (bool)$flag);
        return $this;
    }

    /**
     * Whether should close parent transaction
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShouldCloseParentTransaction()
    {
        return (bool)$this->getData('should_close_parent_transaction');
    }

    //@codeCoverageIgnoreEnd
}
