<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;


use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Interface BuilderInterface
 * Create transaction,
 * prepare its insertion into hierarchy and add its information to payment and comments
 */
interface BuilderInterface
{
    /**
     * Sets payment data for creating transaction
     *
     * @param OrderPaymentInterface $payment
     * @return $this
     */
    public function setPayment(OrderPaymentInterface $payment);

    /**
     * Sets order for creating transaction
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order);

    /**
     * Sets sales entity for creating transaction
     * If the sales document is specified, it will be linked to the transaction as related for future usage.
     * Currently transaction ID is set into the sales object
     * This method writes the added transaction ID into last_trans_id field of the payment object
     *
     * @param \Magento\Sales\Model\AbstractModel $document
     * @return $this
     */
    public function setSalesDocument(\Magento\Sales\Model\AbstractModel $document);

    /**
     * Sets failSafe flag
     * To make sure transaction object won't cause trouble before saving, use $failsafe = true
     *
     * @param bool $failSafe
     * @return $this
     */
    public function setFailSafe($failSafe);

    /**
     * Sets message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Sets transaction id
     * For getting rid of TransactionId in Payment model in future
     *
     * @param string|null $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Sets an array to additional information
     *
     * @param array $value
     * @return $this
     */
    public function setAdditionalInformation(array $value);

    /**
     * Add element to additional information
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addAdditionalInformation($key, $value);

    /**
     * Resets state
     *
     * @return $this
     */
    public function reset();

    /**
     * Build transaction.
     * Transaction type is required
     *
     * @param string $type
     * @return TransactionInterface
     */
    public function build($type);
}
