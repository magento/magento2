<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Payment\Transaction;

use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Payment transactions collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection implements TransactionSearchResultInterface
{
    /**
     * Order ID filter
     *
     * @var int
     */
    protected $_orderId = null;

    /**
     * Columns of order info that should be selected
     *
     * @var string[]
     */
    protected $_addOrderInformation = [];

    /**
     * Columns of payment info that should be selected
     *
     * @var array
     */
    protected $_addPaymentInformation = [];

    /**
     * Order Store ids
     *
     * @var int[]
     */
    protected $_storeIds = [];

    /**
     * Payment ID filter
     *
     * @var int
     */
    protected $_paymentId = null;

    /**
     * Parent ID filter
     *
     * @var int
     */
    protected $_parentId = null;

    /**
     * Filter by transaction type
     *
     * @var string[]
     */
    protected $_txnTypes = null;

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * Initialize collection items factory class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Sales\Model\Order\Payment\Transaction',
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction'
        );
        $this->addFilterToMap('created_at', 'main_table.created_at');
        parent::_construct();
    }

    /**
     * Join order information
     *
     * @param string[] $keys
     * @return $this
     */
    public function addOrderInformation(array $keys)
    {
        $this->_addOrderInformation = array_merge($this->_addOrderInformation, $keys);
        return $this;
    }

    /**
     * Join payment information
     *
     * @param array $keys
     * @return $this
     */
    public function addPaymentInformation(array $keys)
    {
        $this->_addPaymentInformation = array_merge($this->_addPaymentInformation, $keys);
        return $this;
    }

    /**
     * Order ID filter setter
     *
     * @param int $orderId
     * @return $this
     */
    public function addOrderIdFilter($orderId)
    {
        $this->_orderId = (int)$orderId;
        return $this;
    }

    /**
     * Payment ID filter setter
     * Can take either the integer id or the payment instance
     *
     * @param \Magento\Sales\Model\Order\Payment|int $payment
     * @return $this
     */
    public function addPaymentIdFilter($payment)
    {
        $id = $payment;
        if (is_object($payment)) {
            $id = $payment->getId();
        }
        $this->_paymentId = (int)$id;
        return $this;
    }

    /**
     * Parent ID filter setter
     *
     * @param int $parentId
     * @return $this
     */
    public function addParentIdFilter($parentId)
    {
        $this->_parentId = (int)$parentId;
        return $this;
    }

    /**
     * Transaction type filter setter
     *
     * @param string[]|string $txnType
     * @return $this
     */
    public function addTxnTypeFilter($txnType)
    {
        if (!is_array($txnType)) {
            $txnType = [$txnType];
        }
        $this->_txnTypes = $txnType;
        return $this;
    }

    /**
     * Add filter by store ids
     *
     * @param int|int[] $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $storeIds = is_array($storeIds) ? $storeIds : [$storeIds];
        $this->_storeIds = array_merge($this->_storeIds, $storeIds);
        return $this;
    }

    /**
     * Render additional filters and joins
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->_paymentId) {
            $this->getSelect()->where('main_table.payment_id = ?', $this->_paymentId);
        }
        if ($this->_parentId) {
            $this->getSelect()->where('main_table.parent_id = ?', $this->_parentId);
        }
        if ($this->_txnTypes) {
            $this->getSelect()->where('main_table.txn_type IN(?)', $this->_txnTypes);
        }
        if ($this->_orderId) {
            $this->getSelect()->where('main_table.order_id = ?', $this->_orderId);
        }
        if ($this->_addPaymentInformation) {
            $this->getSelect()->joinInner(
                ['sop' => $this->getTable('sales_order_payment')],
                'main_table.payment_id = sop.entity_id',
                $this->_addPaymentInformation
            );
        }
        if ($this->_storeIds) {
            $this->getSelect()->where('so.store_id IN(?)', $this->_storeIds);
            $this->addOrderInformation(['store_id']);
        }
        if ($this->_addOrderInformation) {
            $this->getSelect()->joinInner(
                ['so' => $this->getTable('sales_order')],
                'main_table.order_id = so.entity_id',
                $this->_addOrderInformation
            );
        }
    }

    /**
     * Unserialize additional_information in each item
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}
