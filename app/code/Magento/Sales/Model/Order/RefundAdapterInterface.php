<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Encapsulates refund operation behind unified interface.
 * Can be used as extension point.
 *
 * @api
 * @since 2.2.0
 */
interface RefundAdapterInterface
{
    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param bool $isOnline
     * @return OrderInterface
     * @since 2.2.0
     */
    public function refund(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        $isOnline = false
    );
}
