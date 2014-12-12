<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface TransactionInterface
 */
interface TransactionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TRANSACTION_ID = 'transaction_id';
    const PARENT_ID = 'parent_id';
    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const TXN_ID = 'txn_id';
    const PARENT_TXN_ID = 'parent_txn_id';
    const TXN_TYPE = 'txn_type';
    const IS_CLOSED = 'is_closed';
    const ADDITIONAL_INFORMATION = 'additional_information';
    const CREATED_AT = 'created_at';
    const METHOD = 'method';
    const INCREMENT_ID = 'increment_id';
    const CHILD_TRANSACTIONS = 'child_transactions';

    /**
     * Returns transaction_id
     *
     * @return int
     */
    public function getTransactionId();

    /**
     * Returns parent_id
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Returns payment_id
     *
     * @return int
     */
    public function getPaymentId();

    /**
     * Returns txn_id
     *
     * @return string
     */
    public function getTxnId();

    /**
     * Returns parent_txn_id
     *
     * @return string
     */
    public function getParentTxnId();

    /**
     * Returns txn_type
     *
     * @return string
     */
    public function getTxnType();

    /**
     * Returns is_closed
     *
     * @return int
     */
    public function getIsClosed();

    /**
     * Returns additional_information
     *
     * @return string[]|null
     */
    public function getAdditionalInformation();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns child_transactions
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface[]
     */
    public function getChildTransactions();
}
