<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Interface InvoiceValidatorInterface
 */
class OrderItemValidator implements OrderItemValidatorInterface
{
    /**
     * @param OrderItemInterface $item
     * @return boolean
     */
    public function canInvoice(OrderItemInterface $item)
    {
        $item->getQtyToInvoice() > 0;
    }
}
