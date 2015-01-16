<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Model\Info;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Order payment information
 *
 * @method \Magento\Sales\Model\Resource\Order\Payment _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Payment getResource()
 * @method \Magento\Sales\Model\Order\Payment setParentId(int $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseShippingCaptured(float $value)
 * @method \Magento\Sales\Model\Order\Payment setShippingCaptured(float $value)
 * @method \Magento\Sales\Model\Order\Payment setAmountRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountPaid(float $value)
 * @method \Magento\Sales\Model\Order\Payment setAmountCanceled(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountAuthorized(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountPaidOnline(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountRefundedOnline(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseShippingAmount(float $value)
 * @method \Magento\Sales\Model\Order\Payment setShippingAmount(float $value)
 * @method \Magento\Sales\Model\Order\Payment setAmountPaid(float $value)
 * @method \Magento\Sales\Model\Order\Payment setAmountAuthorized(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountOrdered(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseShippingRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Payment setShippingRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Payment setAmountOrdered(float $value)
 * @method \Magento\Sales\Model\Order\Payment setBaseAmountCanceled(float $value)
 * @method \Magento\Sales\Model\Order\Payment setQuotePaymentId(int $value)
 * @method \Magento\Sales\Model\Order\Payment setAdditionalData(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcExpMonth(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcSsStartYear(string $value)
 * @method \Magento\Sales\Model\Order\Payment setEcheckBankName(string $value)
 * @method \Magento\Sales\Model\Order\Payment setMethod(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcDebugRequestBody(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcSecureVerify(string $value)
 * @method \Magento\Sales\Model\Order\Payment setProtectionEligibility(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcApproval(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcLast4(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcStatusDescription(string $value)
 * @method \Magento\Sales\Model\Order\Payment setEcheckType(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcDebugResponseSerialized(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcSsStartMonth(string $value)
 * @method \Magento\Sales\Model\Order\Payment setEcheckAccountType(string $value)
 * @method \Magento\Sales\Model\Order\Payment setLastTransId(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcCidStatus(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcOwner(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcType(string $value)
 * @method \Magento\Sales\Model\Order\Payment setPoNumber(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcExpYear(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcStatus(string $value)
 * @method \Magento\Sales\Model\Order\Payment setEcheckRoutingNumber(string $value)
 * @method \Magento\Sales\Model\Order\Payment setAccountStatus(string $value)
 * @method \Magento\Sales\Model\Order\Payment setAnetTransMethod(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcDebugResponseBody(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcSsIssue(string $value)
 * @method \Magento\Sales\Model\Order\Payment setEcheckAccountName(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcAvsStatus(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcNumberEnc(string $value)
 * @method \Magento\Sales\Model\Order\Payment setCcTransId(string $value)
 * @method \Magento\Sales\Model\Order\Payment setAddressStatus(string $value)
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
     * @var \Magento\Sales\Model\Order
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
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Service\OrderFactory $serviceOrderFactory
     * @param Payment\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Service\OrderFactory $serviceOrderFactory,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $transactionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
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
            $metadataService,
            $customAttributeBuilder,
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
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
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
                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER
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

        $orderState = \Magento\Sales\Model\Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $isCustomerNotified = false;

        // Do order payment validation on payment method level
        $methodInstance->validate();
        $action = $methodInstance->getConfigPaymentAction();

        if ($action) {
            if ($methodInstance->isInitializeNeeded()) {
                $stateObject = new \Magento\Framework\Object();
                // For method initialization we have to use original config value for payment action
                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
                $orderState = $stateObject->getState() ?: $orderState;
                $orderStatus = $stateObject->getStatus() ?: $orderStatus;
                $isCustomerNotified = $stateObject->getIsNotified();
            } else {
                $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $this->processAction($action, $order);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            }
        }

        $isCustomerNotified = $isCustomerNotified ?: $order->getCustomerNoteNotify();

        if (!in_array($orderStatus, $order->getConfig()->getStateStatuses($orderState))) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $this->updateOrder($order, $orderState, $orderStatus, $isCustomerNotified);

        $this->_eventManager->dispatch('sales_order_payment_place_end', ['payment' => $this]);

        return $this;
    }

    /**
     * Set appropriate state to order or add status to order history
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $orderState
     * @param string $orderStatus
     * @param bool $isCustomerNotified
     * @return void
     */
    protected function updateOrder(\Magento\Sales\Model\Order $order, $orderState, $orderStatus, $isCustomerNotified)
    {
        // add message if order was put into review during authorization or capture
        $message = $order->getCustomerNote();
        $originalOrderState = $order->getState();
        $originalOrderStatus = $order->getStatus();

        switch (true) {
            case ($message && ($originalOrderState == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)):
                $order->addStatusToHistory($originalOrderStatus, $message, $isCustomerNotified);
                break;
            case ($message):
            case ($originalOrderState && $message):
            case ($originalOrderState != $orderState):
            case ($originalOrderStatus != $orderStatus):
                $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
                break;
            default:
                break;
        }
    }

    /**
     * Perform actions based on passed action name
     *
     * @param string $action
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processAction($action, \Magento\Sales\Model\Order $order)
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
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function capture($invoice)
    {
        if (is_null($invoice)) {
            $invoice = $this->_invoice();
            $this->setCreatedInvoice($invoice);
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
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
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
            $this->getMethodInstance()->setStore(
                $order->getStoreId()
            )->fetchTransactionInfo(
                $this,
                $invoice->getTransactionId()
            );
        }
        $status = true;
        if (!$invoice->getIsPaid() && !$this->getIsTransactionPending()) {
            // attempt to capture: this can trigger "is_transaction_pending"
            $this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);

            $transaction = $this->_addTransaction(
                \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
                $invoice,
                true
            );

            if ($this->getIsTransactionPending()) {
                $message = __(
                    'An amount of %1 will be captured after being approved at the payment gateway.',
                    $this->_formatPrice($amountToCapture)
                );
                $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
                if ($this->getIsFraudDetected()) {
                    $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
                }
                $invoice->setIsPaid(false);
            } else {
                // normal online capture: invoice is marked as "paid"
                $message = __('Captured amount of %1 online', $this->_formatPrice($amountToCapture));
                $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $invoice->setIsPaid(true);
                $this->_updateTotals(['base_amount_paid_online' => $amountToCapture]);
            }
            $message = $this->_prependMessage($message);
            $message = $this->_appendTransactionToMessage($transaction, $message);

            $order->setState($state, $status, $message);
            $this->getMethodInstance()->processInvoice($invoice, $this);
            return $this;
        }
        throw new \Magento\Framework\Model\Exception(
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
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
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

        $status = true;
        if ($this->getIsTransactionPending()) {
            $message = __(
                'An amount of %1 will be captured after being approved at the payment gateway.',
                $this->_formatPrice($amount)
            );
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $message = __(
                    'Order is suspended as its capture amount %1 is suspected to be fraudulent.',
                    $this->_formatPrice($amount)
                );
                $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
            }
        } else {
            $message = __('Registered notification about captured amount of %1.', $this->_formatPrice($amount));
            $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
            if ($this->getIsFraudDetected()) {
                $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
                $message = __(
                    'Order is suspended as its capture amount %1 is suspected to be fraudulent.',
                    $this->_formatPrice($amount)
                );
                $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
            }
            // register capture for an existing invoice
            if ($invoice && Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(['base_amount_paid_online' => $amount]);
                $order->addRelatedObject($invoice);
            }
        }

        $transaction = $this->_addTransaction(
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
            $invoice,
            true
        );
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
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
     * @param \Magento\Framework\Object $document
     * @return bool
     */
    public function canVoid(\Magento\Framework\Object $document)
    {
        if (null === $this->_canVoidLookup) {
            $this->_canVoidLookup = (bool)$this->getMethodInstance()->canVoid($document);
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
     * Refund payment online or offline, depending on whether there is invoice set in the creditmemo instance
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param Creditmemo $creditmemo
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function refund($creditmemo)
    {
        $baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());
        $order = $this->getOrder();

        $this->_generateTransactionId(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND);

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
                    )->processBeforeRefund(
                        $invoice,
                        $this
                    )->refund(
                        $this,
                        $baseAmountToRefund
                    )->processCreditmemo(
                        $creditmemo,
                        $this
                    );
                } catch (\Magento\Framework\Model\Exception $e) {
                    if (!$captureTxn) {
                        $e->setMessage(
                            ' ' . __('If the invoice was created offline, try creating an offline credit memo.'),
                            true
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
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
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
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true, $message);

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
     */
    public function registerRefundNotification($amount)
    {
        $notificationAmount = $amount;
        $this->_generateTransactionId(
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
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
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
            $creditmemo
        );
        $message = $this->_prependMessage(
            __('Registered notification about refunded amount of %1.', $this->_formatPrice($amount))
        );
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true, $message);
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
        if (!$this->canVoid($this)) {
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
        return (bool)$this->getMethodInstance()->canReviewPayment($this);
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
        $this->registerPaymentReviewAction(self::REVIEW_ACTION_ACCEPT, true);
        return $this;
    }

    /**
     * Accept order with payment method instance
     *
     * @return $this
     */
    public function deny()
    {
        $this->registerPaymentReviewAction(self::REVIEW_ACTION_DENY, true);
        return $this;
    }

    /**
     * Perform the payment review action: either initiated by merchant or by a notification
     *
     * Sets order to processing state and optionally approves invoice or cancels the order
     *
     * @param string $action
     * @param bool $isOnline
     * @return $this
     * @throws \Exception
     */
    public function registerPaymentReviewAction($action, $isOnline)
    {
        $order = $this->getOrder();

        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();
        $invoice = $this->_getInvoiceForTransactionId($transactionId);

        // invoke the payment method to determine what to do with the transaction
        $result = null;
        $message = null;
        switch ($action) {
            case self::REVIEW_ACTION_ACCEPT:
                if ($isOnline) {
                    if ($this->getMethodInstance()->setStore($order->getStoreId())->acceptPayment($this)) {
                        $result = true;
                        $message = __('Approved the payment online.');
                    } else {
                        $result = -1;
                        $message = __('There is no need to approve this payment.');
                    }
                } else {
                    $result = (bool)$this->getNotificationResult() ? true : -1;
                    $message = __('Registered notification about approved payment.');
                }
                break;
            case self::REVIEW_ACTION_DENY:
                if ($isOnline) {
                    if ($this->getMethodInstance()->setStore($order->getStoreId())->denyPayment($this)) {
                        $result = false;
                        $message = __('Denied the payment online');
                    } else {
                        $result = -1;
                        $message = __('There is no need to deny this payment.');
                    }
                } else {
                    $result = (bool)$this->getNotificationResult() ? false : -1;
                    $message = __('Registered notification about denied payment.');
                }
                break;
            case self::REVIEW_ACTION_UPDATE:
                if ($isOnline) {
                    $this->getMethodInstance()->setStore(
                        $order->getStoreId()
                    )->fetchTransactionInfo(
                        $this,
                        $transactionId
                    );
                }
                if ($this->getIsTransactionApproved()) {
                    $result = true;
                    $message = __('Registered update about approved payment.');
                } elseif ($this->getIsTransactionDenied()) {
                    $result = false;
                    $message = __('Registered update about denied payment.');
                } else {
                    $result = -1;
                    $message = __('There is no update for the payment.');
                }
                break;
            default:
                throw new \Exception('Not implemented.');
        }
        $message = $this->_prependMessage($message);
        if ($transactionId) {
            $message = $this->_appendTransactionToMessage($transactionId, $message);
        }

        // process payment in case of positive or negative result, or add a comment
        if (-1 === $result) { // switch won't work with such $result!
            if ($order->getState() != \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
                $status = $this->getIsFraudDetected() ? \Magento\Sales\Model\Order::STATUS_FRAUD : false;
                $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW, $status, $message);
                if ($transactionId) {
                    $this->setLastTransId($transactionId);
                }
            } else {
                $order->addStatusHistoryComment($message);
            }
        } elseif (true === $result) {
            if ($invoice) {
                $invoice->pay();
                $this->_updateTotals(['base_amount_paid_online' => $invoice->getBaseGrandTotal()]);
                $order->addRelatedObject($invoice);
            }
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true, $message);
        } elseif (false === $result) {
            if ($invoice) {
                $invoice->cancel();
                $order->addRelatedObject($invoice);
            }
            $order->registerCancellation($message, false);
        }
        return $this;
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
        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $status = true;
        $this->getMethodInstance()->setStore($order->getStoreId())->order($this, $amount);

        if ($this->getSkipOrderProcessing()) {
            return $this;
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $message = __(
                'The order amount of %1 is pending approval on the payment gateway.',
                $this->_formatPrice($amount)
            );
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
            }
        } else {
            $message = __('Ordered amount of %1', $this->_formatPrice($amount));
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
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
        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $status = true;
        if ($isOnline) {
            // invoke authorization on gateway
            $this->getMethodInstance()->setStore($order->getStoreId())->authorize($this, $amount);
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
            $message = __(
                'We will authorize %1 after the payment is approved at the payment gateway.',
                $this->_formatPrice($amount)
            );
        } else {
            if ($this->getIsFraudDetected()) {
                $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
                $message = __(
                    'Order is suspended as its authorizing amount %1 is suspected to be fraudulent.',
                    $this->_formatPrice($amount, $this->getCurrencyCode())
                );
            } else {
                $message = __('Authorized amount of %1', $this->_formatPrice($amount));
            }
        }
        if ($this->getIsFraudDetected()) {
            $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);

        $order->setState($state, $status, $message);

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
     */
    protected function _void($isOnline, $amount = null, $gatewayCallback = 'void')
    {
        $order = $this->getOrder();
        $authTransaction = $this->getAuthorizationTransaction();
        $this->_generateTransactionId(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID, $authTransaction);
        $this->setShouldCloseParentTransaction(true);

        // attempt to void
        if ($isOnline) {
            $this->getMethodInstance()->setStore($order->getStoreId())->{$gatewayCallback}($this);
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
        $transaction = $this->_addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID, null, true);
        $message = $this->hasMessage() ? $this->getMessage() : __('Voided authorization.');
        $message = $this->_prependMessage($message);
        if ($amount) {
            $message .= ' ' . __('Amount: %1.', $this->_formatPrice($amount));
        }
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true, $message);
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
     * @return null|\Magento\Sales\Model\Order\Payment\Transaction
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
     * @return null|\Magento\Sales\Model\Order\Payment\Transaction
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
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transactionTo
     * @return $this
     */
    public function importTransactionInfo(\Magento\Sales\Model\Order\Payment\Transaction $transactionTo)
    {
        $data = $this->getMethodInstance()->setStore(
            $this->getOrder()->getStoreId()
        )->fetchTransactionInfo(
            $this,
            $transactionTo->getTxnId()
        );
        if ($data) {
            $transactionTo->setAdditionalInformation(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $data
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
     * @param \Magento\Sales\Model\Order\Payment\Transaction|null $transaction
     * @param string $message
     * @return string
     */
    protected function _appendTransactionToMessage($transaction, $message)
    {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getTxnId() : $transaction;
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
            if (is_string($preparedMessage)) {
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
     * @return \Magento\Sales\Model\Order\Payment\Transaction|false
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
     * @return \Magento\Sales\Model\Order\Payment\Transaction|false
     */
    public function lookupTransaction($txnId, $txnType = false)
    {
        return $this->_lookupTransaction($txnId, $txnType);
    }

    /**
     * Lookup an authorization transaction using parent transaction id, if set
     * @return \Magento\Sales\Model\Order\Payment\Transaction|false
     */
    public function getAuthorizationTransaction()
    {
        if ($this->getParentTransactionId()) {
            $txn = $this->_lookupTransaction($this->getParentTransactionId());
        } else {
            $txn = false;
        }

        if (!$txn) {
            $txn = $this->_lookupTransaction(false, \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
        }
        return $txn;
    }

    /**
     * Lookup the transaction by id
     * @param string $transactionId
     * @return \Magento\Sales\Model\Order\Payment\Transaction|false
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
     * @param bool|\Magento\Sales\Model\Order\Payment\Transaction $transactionBasedOn
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
}
