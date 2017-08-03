<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

/**
 * Interface \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
 *
 * @since 2.0.0
 */
interface OrderViewAuthorizationInterface
{
    /**
     * Check if order can be viewed by user
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     * @since 2.0.0
     */
    public function canView(\Magento\Sales\Model\Order $order);
}
