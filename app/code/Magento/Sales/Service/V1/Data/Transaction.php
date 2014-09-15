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
 
namespace Magento\Sales\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject as DataObject;

/**
 * @codeCoverageIgnore
 */
class Transaction extends DataObject
{
    /**#@+
     * Data object properties
     * @var string
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
    /**#@-*/

    /**
     * Returns transaction_id
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Returns payment_id
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->_get(self::PAYMENT_ID);
    }

    /**
     * Returns txn_id
     *
     * @return string
     */
    public function getTxnId()
    {
        return $this->_get(self::TXN_ID);
    }

    /**
     * Returns parent_txn_id
     *
     * @return string
     */
    public function getParentTxnId()
    {
        return $this->_get(self::PARENT_TXN_ID);
    }

    /**
     * Returns txn_type
     *
     * @return string
     */
    public function getTxnType()
    {
        return $this->_get(self::TXN_TYPE);
    }

    /**
     * Returns is_closed
     *
     * @return int
     */
    public function getIsClosed()
    {
        return $this->_get(self::IS_CLOSED);
    }

    /**
     * Returns additional_information
     *
     * @return \Magento\Sales\Service\V1\Data\Transaction\AdditionalInformation[]
     */
    public function getAdditionalInformation()
    {
        return $this->_get(self::ADDITIONAL_INFORMATION);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_get(self::METHOD);
    }

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->_get(self::INCREMENT_ID);
    }

    /**
     * Returns child_transactions
     *
     * @return \Magento\Sales\Service\V1\Data\Transaction[]
     */
    public function getChildTransactions()
    {
        return $this->_get(self::CHILD_TRANSACTIONS);
    }
}
