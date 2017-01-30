<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Class RefundAdapter
 */
class RefundAdapter implements RefundAdapterInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\RefundOperation
     */
    private $refundOperation;

    /**
     * PaymentAdapter constructor.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation
     */
    public function __construct(
        \Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation
    ) {
        $this->refundOperation = $refundOperation;
    }

    /**
     * {@inheritdoc}
     */
    public function refund(
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $isOnline = false
    ) {
        return $this->refundOperation->execute($creditmemo, $order, $isOnline);
    }
}
