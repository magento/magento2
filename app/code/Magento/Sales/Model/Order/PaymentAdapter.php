<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

class PaymentAdapter implements PaymentAdapterInterface
{
    /**
     * @var CreditmemoRefundOperation
     */
    private $refundOperation;

    /**
     * PaymentAdapter constructor.
     * @param \Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation
     */
    public function __construct(\Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation)
    {
        $this->refundOperation = $refundOperation;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param bool $isOnline
     * @return OrderInterface
     */
    public function refund(CreditmemoInterface $creditmemo, OrderInterface $order, $isOnline = false)
    {
        return $this->refundOperation->execute($creditmemo, $order, $isOnline);
    }
}
