<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo\RefundOperation;

/**
 * @inheritdoc
 * @since 2.2.0
 */
class RefundAdapter implements RefundAdapterInterface
{
    /**
     * @var RefundOperation
     * @since 2.2.0
     */
    private $refundOperation;

    /**
     * @param RefundOperation $refundOperation
     * @since 2.2.0
     */
    public function __construct(
        RefundOperation $refundOperation
    ) {
        $this->refundOperation = $refundOperation;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function refund(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        $isOnline = false
    ) {
        return $this->refundOperation->execute($creditmemo, $order, $isOnline);
    }
}
