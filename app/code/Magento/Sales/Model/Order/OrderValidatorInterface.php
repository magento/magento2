<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderValidatorInterface
 *
 * @api
 */
interface OrderValidatorInterface
{
    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function canInvoice(OrderInterface $order);
}
