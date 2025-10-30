<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo\RefundOperation;

/**
 * @inheritdoc
 */
class RefundAdapter implements RefundAdapterInterface
{
    /**
     * @var RefundOperation
     */
    private $refundOperation;

    /**
     * @param RefundOperation $refundOperation
     */
    public function __construct(
        RefundOperation $refundOperation
    ) {
        $this->refundOperation = $refundOperation;
    }

    /**
     * @inheritdoc
     */
    public function refund(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        $isOnline = false
    ) {
        return $this->refundOperation->execute($creditmemo, $order, $isOnline);
    }
}
