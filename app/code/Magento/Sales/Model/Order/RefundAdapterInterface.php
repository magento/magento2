<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Interface RefundAdapterInterface
 */
interface RefundAdapterInterface
{
    /**
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $isOnline
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function refund(
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $isOnline = false
    );
}
