<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderStateResolverInterface
 *
 * @api
 */
interface OrderStateResolverInterface
{
    const IN_PROGRESS = 'order_in_progress';
    const FORCED_CREDITMEMO = 'forced_creditmemo';

    /**
     * @param OrderInterface $order
     * @param array $arguments
     * @return string
     */
    public function getStateForOrder(OrderInterface $order, array $arguments = []);
}
