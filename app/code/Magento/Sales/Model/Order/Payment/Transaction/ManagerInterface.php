<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Manage payment transaction
 */
interface ManagerInterface
{
    /**
     * Lookup an authorization transaction using parent transaction id, if set
     *
     * @param string $parentTransactionId
     * @param int $paymentId
     * @param int $orderId
     * @return false|Transaction
     */
    public function getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId);

    /**
     * Checks if transaction exists by txt id
     *
     * @param string $transactionId
     * @param int $paymentId
     * @param int $orderId
     * @return bool
     */
    public function isTransactionExists($transactionId, $paymentId, $orderId);

    /**
     * Update transaction ids for further processing
     * If no transactions were set before invoking, may generate an "offline" transaction id
     *
     * @param OrderPaymentInterface $payment
     * @param string $type
     * @param bool|Transaction $transactionBasedOn
     * @return string|null
     */
    public function generateTransactionId(OrderPaymentInterface $payment, $type, $transactionBasedOn = false);
}
