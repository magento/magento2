<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Interface OrderItemValidatorInterface
 *
 * @api
 */
interface OrderItemValidatorInterface
{
    /**
     * @param OrderItemInterface $item
     * @return bool
     */
    public function canInvoice(OrderItemInterface $item);
}
