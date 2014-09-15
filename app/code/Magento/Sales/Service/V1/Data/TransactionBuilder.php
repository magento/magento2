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

/**
 * Builder class for \Magento\Sales\Service\V1\Data\Transaction
 */
class TransactionBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::TRANSACTION_ID, (int)$transactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($parentId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::PARENT_ID, $parentId);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::ORDER_ID, (int)$orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentId($paymentId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::PAYMENT_ID, (int)$paymentId);
    }

    /**
     * {@inheritdoc}
     */
    public function setTxnId($txnId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::TXN_ID, (string)$txnId);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentTxnId($parentTxnId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::PARENT_TXN_ID, (string)$parentTxnId);
    }

    /**
     * {@inheritdoc}
     */
    public function setTxnType($txnType)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::TXN_TYPE, (string)$txnType);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsClosed($isClosed)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::IS_CLOSED, (int)$isClosed);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::ADDITIONAL_INFORMATION, $additionalInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::CREATED_AT, (string)$createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::METHOD, (string)$method);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::INCREMENT_ID, (string)$incrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function setChildTransactions($childTransactions)
    {
        $this->_set(\Magento\Sales\Service\V1\Data\Transaction::CHILD_TRANSACTIONS, $childTransactions);
    }
}
