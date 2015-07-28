<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;


/**
 * Manage payment transaction
 */
interface ManagerInterface
{
    /**
     * Create transaction,
     * prepare its insertion into hierarchy and add its information to payment and comments
     *
     * @param string $type
     * @param \Magento\Sales\Model\AbstractModel $salesDocument
     * @param bool $failSafe
     * @param bool|string $message
     * @return null|\Magento\Sales\Api\Data\TransactionInterface
     */
    public function addTransaction(
        \Magento\Sales\Api\Data\OrderPaymentInterface $payment,
        $type,
        $salesDocument = null,
        $failSafe = false,
        $message = false
    );
}