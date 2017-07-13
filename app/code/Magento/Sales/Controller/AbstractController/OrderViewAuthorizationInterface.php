<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

interface OrderViewAuthorizationInterface
{
    /**
     * Check if order can be viewed by user
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canView(\Magento\Sales\Model\Order $order);
}
