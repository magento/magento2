<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Payment transaction model
 * Tracks transaction history, allows to build transactions hierarchy
 * By default transactions are saved as closed.
 *
 * @method \Magento\Sales\Model\Resource\Order\Payment\Transaction _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Payment\Transaction getResource()
 * @method \Magento\Sales\Model\Order\Payment\Transaction setParentId(int $value)
 * @method \Magento\Sales\Model\Order\Payment\Transaction setOrderId(int $value)
 * @method \Magento\Sales\Model\Order\Payment\Transaction setPaymentId(int $value)
 * @method \Magento\Sales\Model\Order\Payment\Transaction setIsClosed(int $value)
 * @method \Magento\Sales\Model\Order\Payment\Transaction setCreatedAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Transaction extends AbstractExtensibleModel implements TransactionInterface
{
    /**#@+
     * Supported transaction types
     * @var string
     */
    const TYPE_PAYMENT = 'payment';

    const TYPE_ORDER = 'order';

    const TYPE_AUTH = 'authorization';

    const TYPE_CAPTURE = 'capture';

    const TYPE_VOID = 'void';

    const TYPE_REFUND = 'refund';

    /**#@-*/

    /**
     * Raw details key in additional info
     */
    const RAW_DETAILS = 'raw_details_info';

    /**
     * Payment instance. Required for most transaction writing and search operations
     *
     * @var \Magento\Sales\Model\Order\Payment
     */
    protected $_paymentObject = null;

    /**
     * Order instance
     *
     * @var \Magento\Sales\Model\Order\Payment
     */
    protected $_order = null;

    /**
     * Parent transaction instance
     * @var \Magento\Sales\Model\Order\Payment\Transaction
     */
    protected $_parentTransaction = null;

    /**
     * Child transactions, assoc array of transaction_id => instance
     *
     * @var array
     */
    protected $_children = null;

    /**
     * Child transactions, assoc array of txn_id => instance
     * Filled only in case when all child transactions have txn_id
     * Used for quicker search of child transactions using isset() as opposite to foreaching $_children
     *
     * @var array
     */
    protected $_identifiedChildren = null;

    /**
     * Whether to perform automatic actions on transactions, such as auto-closing and putting as a parent
     *
     * @var bool
     */
    protected $_transactionsAutoLinking = true;

    /**
     * Whether to throw exceptions on different operations
     *
     * @var bool
     */
    protected $_isFailsafe = false;

    /**
     * Whether transaction has children
     *
     * @var bool
     */
    protected $_hasChild = null;

    /**
     * Event object prefix
     *
     * @var string
     * @see \Magento\Core\Model\Absctract::$_eventPrefix
     */
    protected $_eventPrefix = 'sales_order_payment_transaction';

    /**
     * Event object prefix
     *
     * @var string
     * @see \Magento\Core\Model\Absctract::$_eventObject
     */
    protected $_eventObject = 'order_payment_transaction';

    /**
     * Order website id
     *
     * @var int
     */
    protected $_orderWebsiteId = null;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Sales\Model\Order\PaymentFactory $paymentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param TransactionFactory $transactionFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Sales\Model\Order\PaymentFactory $paymentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        TransactionFactory $transactionFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_paymentFactory = $paymentFactory;
        $this->_orderFactory = $orderFactory;
        $this->_dateFactory = $dateFactory;
        $this->_transactionFactory = $transactionFactory;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
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
        $this->_init('Magento\Sales\Model\Resource\Order\Payment\Transaction');
        parent::_construct();
    }

    /**
     * Payment instance setter
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    public function setOrderPaymentObject(\Magento\Sales\Model\Order\Payment $payment)
    {
        $this->_paymentObject = $payment;
        $this->setOrder($payment->getOrder());
        return $this;
    }

    /**
     * Transaction ID setter
     *
     * @param string $txnId
     * @return $this
     */
    public function setTxnId($txnId)
    {
        $this->_verifyTxnId($txnId);
        return $this->setData('txn_id', $txnId);
    }

    /**
     * Parent transaction ID setter
     * Can set the transaction id as well
     *
     * @param string $parentTxnId
     * @param string $txnId
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setParentTxnId($parentTxnId, $txnId = null)
    {
        $this->_verifyTxnId($parentTxnId);
        if (empty($txnId)) {
            if ('' == $this->getTxnId()) {
                throw new \Magento\Framework\Model\Exception(__('The parent transaction ID must have a transaction ID.'));
            }
        } else {
            $this->setTxnId($txnId);
        }
        return $this->setData('parent_txn_id', $parentTxnId);
    }

    /**
     * Transaction type setter
     *
     * @param string $txnType
     * @return $this
     */
    public function setTxnType($txnType)
    {
        $this->_verifyTxnType($txnType);
        return $this->setData('txn_type', $txnType);
    }

    /**
     * Parent transaction getter
     * May attempt to load it.
     *
     * @param bool $shouldLoad
     * @return bool|\Magento\Sales\Model\Order\Payment\Transaction
     */
    public function getParentTransaction($shouldLoad = true)
    {
        if (null === $this->_parentTransaction) {
            $this->_verifyThisTransactionExists();
            $this->_parentTransaction = false;
            $parentId = $this->getParentId();
            if ($parentId) {
                $this->_parentTransaction = $this->_transactionFactory->create();
                if ($shouldLoad) {
                    $this->_parentTransaction->setOrderPaymentObject($this->_paymentObject)->load($parentId);
                    if (!$this->_parentTransaction->getId()) {
                        $this->_parentTransaction = false;
                    } else {
                        $this->_parentTransaction->hasChildTransaction(true);
                    }
                }
            }
        }
        return $this->_parentTransaction;
    }

    /**
     * Child transaction(s) getter
     *
     * Will attempt to load them first
     * Can be filtered by types and/or transaction_id
     * Returns transaction object if transaction_id is specified, otherwise - array
     * TODO: $recursive is not implemented
     *
     * @param array|string $types
     * @param string $txnId
     * @param bool $recursive
     * @return Transaction[]
     */
    public function getChildTransactions($types = null, $txnId = null, $recursive = false)
    {
        $this->_loadChildren();

        // grab all transactions
        if (empty($types) && null === $txnId) {
            return $this->_children;
        } elseif ($types && !is_array($types)) {
            $types = [$types];
        }

        // get a specific transaction
        if ($txnId) {
            if (empty($this->_children)) {
                return null;
            }
            $transaction = null;
            if ($this->_identifiedChildren) {
                if (isset($this->_identifiedChildren[$txnId])) {
                    $transaction = $this->_identifiedChildren[$txnId];
                }
            } else {
                foreach ($this->_children as $child) {
                    if ($child->getTxnId() === $txnId) {
                        $transaction = $child;
                        break;
                    }
                }
            }
            // return transaction only if type matches
            if (!$transaction || $types && !in_array($transaction->getType(), $types, true)) {
                return null;
            }
            return $transaction;
        }

        // filter transactions by types
        $result = [];
        foreach ($this->_children as $child) {
            if (in_array($child->getType(), $types, true)) {
                $result[$child->getId()] = $child;
            }
        }
        return $result;
    }

    /**
     * Close an authorization transaction
     *
     * This method can be invoked from any child transaction of the transaction to be closed
     * Returns the authorization transaction on success. Otherwise false.
     * $dryRun = true prevents actual closing, it just allows to check whether this operation is possible
     *
     * @param bool $shouldSave
     * @param bool $dryRun
     * @return bool|\Magento\Sales\Model\Order\Payment\Transaction
     * @throws \Exception
     */
    public function closeAuthorization($shouldSave = true, $dryRun = false)
    {
        try {
            $this->_verifyThisTransactionExists();
        } catch (\Exception $e) {
            if ($dryRun) {
                return false;
            }
            throw $e;
        }
        $authTransaction = false;
        switch ($this->getTxnType()) {
            case self::TYPE_VOID:
                // break intentionally omitted
            case self::TYPE_CAPTURE:
                $authTransaction = $this->getParentTransaction();
                break;
            case self::TYPE_AUTH:
                $authTransaction = $this;
                break;
                // case self::TYPE_PAYMENT?
            default:
                break;
        }
        if ($authTransaction) {
            if (!$dryRun) {
                $authTransaction->close($shouldSave);
            }
        }
        return $authTransaction;
    }

    /**
     * Close a capture transaction
     * Logic is similar to closeAuthorization(), but for a capture transaction
     *
     * @param bool $shouldSave
     * @return bool|\Magento\Sales\Model\Order\Payment\Transaction
     * @see self::closeAuthorization()
     */
    public function closeCapture($shouldSave = true)
    {
        $this->_verifyThisTransactionExists();
        $captureTransaction = false;
        switch ($this->getTxnType()) {
            case self::TYPE_CAPTURE:
                $captureTransaction = $this;
                break;
            case self::TYPE_REFUND:
                $captureTransaction = $this->getParentTransaction();
                break;
            default:
                break;
        }
        if ($captureTransaction) {
            $captureTransaction->close($shouldSave);
        }
        return $captureTransaction;
    }

    /**
     * Check whether authorization in current hierarchy can be voided completely
     * Basically checks whether the authorization exists and it is not affected by a capture or void
     *
     * @return bool
     */
    public function canVoidAuthorizationCompletely()
    {
        try {
            $authTransaction = $this->closeAuthorization('', true);
            if ($authTransaction->hasChildTransaction() || $this->_children) {
                return false;
            }
            return true;
        } catch (\Magento\Framework\Model\Exception $e) {
            // jam all logical exceptions, fallback to false
        }
        return false;
    }

    /**
     * Getter/Setter of whether current transaction has a child transaction
     *
     * @param bool $whetherHasChild
     * @return $this|bool
     */
    public function hasChildTransaction($whetherHasChild = null)
    {
        if (null !== $whetherHasChild) {
            $this->_hasChild = (bool)$whetherHasChild;
            return $this;
        } elseif (null === $this->_hasChild) {
            if ($this->getChildTransactions()) {
                $this->_hasChild = true;
            } else {
                $this->_hasChild = false;
            }
        }
        return $this->_hasChild;
    }

    /**
     * Check object before loading by by specified transaction ID
     *
     * @param mixed $txnId
     * @return $this
     */
    protected function _beforeLoadByTxnId($txnId)
    {
        $this->_verifyPaymentObject();
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_load_by_txn_id_before',
            $this->_getEventData() + ['txn_id' => $txnId]
        );
        return $this;
    }

    /**
     * Load self by specified transaction ID. Requires the valid payment object to be set
     *
     * @param string $txnId
     * @return $this
     */
    public function loadByTxnId($txnId)
    {
        $this->_beforeLoadByTxnId($txnId);
        $this->getResource()->loadObjectByTxnId($this, $this->getOrderId(), $this->_paymentObject->getId(), $txnId);
        $this->_afterLoadByTxnId();
        return $this;
    }

    /**
     * Check object after loading by by specified transaction ID
     *
     * @return $this
     */
    protected function _afterLoadByTxnId()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_by_txn_id_after', $this->_getEventData());
        return $this;
    }

    /**
     * Additional information setter
     * Updates data inside the 'additional_information' array
     * Doesn't allow to set arrays
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setAdditionalInformation($key, $value)
    {
        if (is_object($value)) {
            throw new \Magento\Framework\Model\Exception(__('Payment transactions disallow storing objects.'));
        }
        $info = $this->_getData('additional_information');
        if (!$info) {
            $info = [];
        }
        $info[$key] = $value;
        return $this->setData('additional_information', $info);
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null)
    {
        $info = $this->_getData('additional_information');
        if (!$info) {
            $info = [];
        }
        if ($key) {
            return isset($info[$key]) ? $info[$key] : null;
        }
        return $info;
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($key) {
            $info = $this->_getData('additional_information');
            if (is_array($info)) {
                unset($info[$key]);
            }
        } else {
            $info = [];
        }
        return $this->setData('additional_information', $info);
    }

    /**
     * Close this transaction
     *
     * @param bool $shouldSave
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     * @throws \Exception
     */
    public function close($shouldSave = true)
    {
        if (!$this->_isFailsafe) {
            $this->_verifyThisTransactionExists();
        }
        if (1 == $this->getIsClosed() && $this->_isFailsafe) {
            throw new \Magento\Framework\Model\Exception(
                __('The transaction "%1" (%2) is already closed.', $this->getTxnId(), $this->getTxnType())
            );
        }
        $this->setIsClosed(1);
        if ($shouldSave) {
            $this->save();
        }
        if ($this->_transactionsAutoLinking && self::TYPE_AUTH === $this->getTxnType()) {
            try {
                $paymentTransaction = $this->getParentTransaction();
                if ($paymentTransaction) {
                    $paymentTransaction->close($shouldSave);
                }
            } catch (\Exception $e) {
                if (!$this->_isFailsafe) {
                    throw $e;
                }
            }
        }
        return $this;
    }

    /**
     * Order Payment instance getter
     * Will attempt to load by payment_id if it is set in data
     *
     * @param bool $shouldLoad
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getOrderPaymentObject($shouldLoad = true)
    {
        $this->_verifyThisTransactionExists();
        if (null === $this->_paymentObject && $shouldLoad) {
            $payment = $this->_paymentFactory->create()->load($this->getPaymentId());
            if ($payment->getId()) {
                $this->setOrderPaymentObject($payment);
            }
        }
        return $this->_paymentObject;
    }

    /**
     * Order ID getter
     * Attempts to get ID from set order payment object, if any, or from data by key 'order_id'
     *
     * @return int|null
     */
    public function getOrderId()
    {
        $orderId = $this->_getData('order_id');
        if ($orderId) {
            return $orderId;
        }
        if ($this->_paymentObject) {
            $orderId = $this->_paymentObject
                ->getOrder() ? $this
                ->_paymentObject
                ->getOrder()
                ->getId() : $this
                ->_paymentObject
                ->getParentId();
        }

        return $orderId;
    }

    /**
     * Retrieve order instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $this->setOrder();
        }

        return $this->_order;
    }

    /**
     * Set order instance for transaction depends on transaction behavior
     * If $order equals to true, method isn't loading new order instance.
     *
     * @param \Magento\Sales\Model\Order|null|boolean $order
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setOrder($order = null)
    {
        if (null === $order || $order === true) {
            if (null !== $this->_paymentObject && $this->_paymentObject->getOrder()) {
                $this->_order = $this->_paymentObject->getOrder();
            } elseif ($this->getOrderId() && $order === null) {
                $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
            } else {
                $this->_order = false;
            }
        } elseif (!$this->getId() || $this->getOrderId() == $order->getId()) {
            $this->_order = $order;
        } else {
            throw new \Magento\Framework\Model\Exception(__('Set order for existing transactions not allowed'));
        }

        return $this;
    }

    /**
     * Setter/Getter whether transaction is supposed to prevent exceptions on saving
     *
     * @param bool|null $setFailsafe
     * @return $this|bool
     */
    public function isFailsafe($setFailsafe = null)
    {
        if (null === $setFailsafe) {
            return $this->_isFailsafe;
        }
        $this->_isFailsafe = (bool)$setFailsafe;
        return $this;
    }

    /**
     * Verify data required for saving
     *
     * @return $this
     */
    public function beforeSave()
    {
        // set parent id
        $this->_verifyPaymentObject();
        if (!$this->getId()) {
            // We need to set order and payment ids only for new transactions
            if (null !== $this->_paymentObject) {
                $this->setPaymentId($this->_paymentObject->getId());
            }

            if (null !== $this->_order) {
                $this->setOrderId($this->_order->getId());
            }

            $this->setCreatedAt($this->_dateFactory->create()->gmtDate());
        }
        return parent::beforeSave();
    }

    /**
     * Load child transactions
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _loadChildren()
    {
        if (null !== $this->_children) {
            return;
        }

        // make sure minimum required data is set
        $this->_verifyThisTransactionExists();
        $payment = $this->_verifyPaymentObject(true);
        $paymentId = $payment ? $payment->getId() : $this->_getData('payment_id');
        if (!$paymentId) {
            throw new \Magento\Framework\Model\Exception(__('At minimum, you need to set a payment ID.'));
        }

        $this->setOrder(true);

        $orderFilter = $this->getOrder();
        // Try to get order instance for filter
        if (!$orderFilter) {
            $orderFilter = $this->getOrderId();
        }

        // prepare children collection
        $children = $this->getResourceCollection()->setOrderFilter(
            $orderFilter
        )->addPaymentIdFilter(
            $paymentId
        )->addParentIdFilter(
            $this->getId()
        );

        // set basic children array and attempt to map them per txn_id, if all of them have txn_id
        $this->_children = [];
        $this->_identifiedChildren = [];
        foreach ($children as $child) {
            if ($payment) {
                $child->setOrderPaymentObject($payment);
            }
            $this->_children[$child->getId()] = $child;
            if (false !== $this->_identifiedChildren) {
                $childTxnId = $child->getTxnId();
                if (!$childTxnId || '0' == $childTxnId) {
                    $this->_identifiedChildren = false;
                } else {
                    $this->_identifiedChildren[$child->getTxnId()] = $child;
                }
            }
        }
        if (false === $this->_identifiedChildren) {
            $this->_identifiedChildren = [];
        }
    }

    /**
     * Check whether this transaction is voided
     *
     * TODO: implement that there should be only one void per authorization
     * @return bool
     */
    protected function _isVoided()
    {
        $this->_verifyThisTransactionExists();
        return self::TYPE_AUTH === $this->getTxnType() && (bool)count($this->getChildTransactions(self::TYPE_VOID));
    }

    /**
     * Check whether this transaction is voided
     *
     * @return bool
     */
    public function isVoided()
    {
        return $this->_isVoided();
    }

    /**
     * Retrieve transaction types
     *
     * @return array
     */
    public function getTransactionTypes()
    {
        return [
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER => __('Order'),
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH => __('Authorization'),
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE => __('Capture'),
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID => __('Void'),
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND => __('Refund')
        ];
    }

    /**
     * Retrieve order website id
     *
     * @return int
     */
    public function getOrderWebsiteId()
    {
        if (is_null($this->_orderWebsiteId)) {
            $this->_orderWebsiteId = (int)$this->getResource()->getOrderWebsiteId($this->getOrderId());
        }
        return $this->_orderWebsiteId;
    }

    /**
     * Check whether specified or set transaction type is supported
     *
     * @param string $txnType
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _verifyTxnType($txnType = null)
    {
        if (null === $txnType) {
            $txnType = $this->getTxnType();
        }
        switch ($txnType) {
            case self::TYPE_PAYMENT:
            case self::TYPE_ORDER:
            case self::TYPE_AUTH:
            case self::TYPE_CAPTURE:
            case self::TYPE_VOID:
            case self::TYPE_REFUND:
                break;
            default:
                throw new \Magento\Framework\Model\Exception(__('We found an unsupported transaction type "%1".', $txnType));
        }
    }

    /**
     * Check whether the payment object is set and it has order object or there is an order_id is set
     * $dryRun allows to not throw exception
     *
     * @param bool $dryRun
     * @return \Magento\Sales\Model\Order\Payment|null|false
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _verifyPaymentObject($dryRun = false)
    {
        if (!$this->_paymentObject || !$this->getOrderId()) {
            if (!$dryRun) {
                throw new \Magento\Framework\Model\Exception(__('Please set a proper payment object.'));
            }
        }
        return $this->_paymentObject;
    }

    /**
     * Check whether specified transaction ID is valid
     *
     * @param string $txnId
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _verifyTxnId($txnId)
    {
        if (null !== $txnId && 0 == strlen($txnId)) {
            throw new \Magento\Framework\Model\Exception(__('The Transaction ID field cannot be empty.'));
        }
    }

    /**
     * Make sure this object is a valid transaction
     * TODO for more restriction we can check for data consistency
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _verifyThisTransactionExists()
    {
        if (!$this->getId()) {
            throw new \Magento\Framework\Model\Exception(__('You can\'t do this without a transaction object.'));
        }
        $this->_verifyTxnType();
    }

    /**
     * Returns transaction_id
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->getData(TransactionInterface::TRANSACTION_ID);
    }

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(TransactionInterface::METHOD);
    }

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData(TransactionInterface::INCREMENT_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(TransactionInterface::PARENT_ID);
    }

    /**
     * Returns payment_id
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->getData(TransactionInterface::PAYMENT_ID);
    }

    /**
     * Returns txn_id
     *
     * @return string
     */
    public function getTxnId()
    {
        return $this->getData(TransactionInterface::TXN_ID);
    }

    /**
     * Returns parent_txn_id
     *
     * @return string
     */
    public function getParentTxnId()
    {
        return $this->getData(TransactionInterface::PARENT_TXN_ID);
    }

    /**
     * Returns txn_type
     *
     * @return string
     */
    public function getTxnType()
    {
        return $this->getData(TransactionInterface::TXN_TYPE);
    }

    /**
     * Returns is_closed
     *
     * @return int
     */
    public function getIsClosed()
    {
        return $this->getData(TransactionInterface::IS_CLOSED);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(TransactionInterface::CREATED_AT);
    }
}
