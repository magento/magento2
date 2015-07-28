<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Manage payment transaction
 */
interface ManagerInterface
{
    /**
     * Lookup an authorization transaction using parent transaction id, if set
     *
     * @param $parentTransactionId
     * @param $paymentId
     * @param $orderId
     * @return false|Transaction
     */
    public function getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId);
}