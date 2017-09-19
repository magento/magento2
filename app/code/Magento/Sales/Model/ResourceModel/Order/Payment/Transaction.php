<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Payment;

use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Sales\Model\Spi\TransactionResourceInterface;

/**
 * Sales transaction resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Transaction extends EntityAbstract implements TransactionResourceInterface
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     */
    protected $_serializableFields = ['additional_information' => [null, []]];

    /**
     * Initialize main table and the primary key field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_payment_transaction', 'transaction_id');
    }

    /**
     * Update transactions in database using provided transaction as parent for them
     * have to repeat the business logic to avoid accidental injection of wrong transactions
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @return void
     */
    public function injectAsParent(\Magento\Sales\Model\Order\Payment\Transaction $transaction)
    {
        $txnId = $transaction->getTxnId();
        if ($txnId &&
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT === $transaction->getTxnType() &&
            ($id = $transaction->getId())
        ) {
            $connection = $this->getConnection();

            // verify such transaction exists, determine payment and order id
            $verificationRow = $connection->fetchRow(
                $connection->select()->from(
                    $this->getMainTable(),
                    ['payment_id', 'order_id']
                )->where(
                    "{$this->getIdFieldName()} = ?",
                    (int)$id
                )
            );
            if (!$verificationRow) {
                return;
            }
            list($paymentId, $orderId) = array_values($verificationRow);

            // inject
            $where = [
                $connection->quoteIdentifier($this->getIdFieldName()) . '!=?' => $id,
                new \Zend_Db_Expr('parent_id IS NULL'),
                'payment_id = ?' => (int)$paymentId,
                'order_id = ?' => (int)$orderId,
                'parent_txn_id = ?' => $txnId,
            ];
            $connection->update($this->getMainTable(), ['parent_id' => $id], $where);
        }
    }

    /**
     * Load the transaction object by specified txn_id
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @return \Magento\Sales\Model\Order\Payment\Transaction
     */
    public function loadObjectByTxnId(
        \Magento\Sales\Model\Order\Payment\Transaction $transaction,
        $orderId,
        $paymentId,
        $txnId
    ) {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId);
        $data = $this->getConnection()->fetchRow($select);
        if (!$data) {
            return $transaction;
        }
        $transaction->setData($data);
        $this->unserializeFields($transaction);
        $this->_afterLoad($transaction);

        return $transaction;
    }

    /**
     * Retrieve order website id
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderWebsiteId($orderId)
    {
        $connection = $this->getConnection();
        $bind = [':entity_id' => $orderId];
        $select = $connection->select()->from(
            ['so' => $this->getTable('sales_order')],
            'cs.website_id'
        )->joinInner(
            ['cs' => $this->getTable('store')],
            'cs.store_id = so.store_id'
        )->where(
            'so.entity_id = :entity_id'
        );
        return $connection->fetchOne($select, $bind);
    }

    /**
     * Lookup for parent_id in already saved transactions of this payment by the order_id
     * Also serialize additional information, if any
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $transaction)
    {
        $parentTxnId = $transaction->getData('parent_txn_id');
        $txnId = $transaction->getData('txn_id');
        $orderId = $transaction->getData('order_id');
        $paymentId = $transaction->getData('payment_id');
        $idFieldName = $this->getIdFieldName();

        if ($parentTxnId) {
            if (!$txnId || !$orderId || !$paymentId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t have enough information to save the parent transaction ID.')
                );
            }
            $parentId = (int)$this->_lookupByTxnId($orderId, $paymentId, $parentTxnId, $idFieldName);
            if ($parentId) {
                $transaction->setData('parent_id', $parentId);
            }
        }

        // make sure unique key won't cause trouble
        if ($transaction->isFailsafe()) {
            $autoincrementId = (int)$this->_lookupByTxnId($orderId, $paymentId, $txnId, $idFieldName);
            if ($autoincrementId) {
                $transaction->setData($idFieldName, $autoincrementId)->isObjectNew(false);
            }
        }

        return parent::_beforeSave($transaction);
    }

    /**
     * Load cell/row by specified unique key parts
     *
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param mixed (array|string|object) $columns
     * @param bool $isRow
     * @param string $txnType
     * @return array|string
     */
    private function _lookupByTxnId($orderId, $paymentId, $txnId, $columns, $isRow = false, $txnType = null)
    {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns);
        if ($txnType) {
            $select->where('txn_type = ?', $txnType);
        }
        if ($isRow) {
            return $this->getConnection()->fetchRow($select);
        }
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get select object for loading transaction by the unique key of order_id, payment_id, txn_id
     *
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param string|array|\Zend_Db_Expr $columns
     * @return \Magento\Framework\DB\Select
     */
    private function _getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns = '*')
    {
        return $this->getConnection()->select()->from(
            $this->getMainTable(),
            $columns
        )->where(
            'order_id = ?',
            $orderId
        )->where(
            'payment_id = ?',
            $paymentId
        )->where(
            'txn_id = ?',
            $txnId
        );
    }
}
